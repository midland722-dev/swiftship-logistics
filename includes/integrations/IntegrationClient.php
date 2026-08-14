<?php
/**
 * Integration Client — shared HTTP transport for every provider integration.
 *
 * Loads an `api_integrations` row and transparently handles:
 *   - credential resolution (basic / bearer / oauth / api_key auth)
 *   - per-integration rate limiting (rate_limit_per_minute)
 *   - bounded retries with backoff (retry_count / retry_delay_seconds)
 *   - structured logging to api_integration_logs
 *   - failure bookkeeping (consecutive_failures / last_error / last_sync_at)
 *
 * It is intentionally provider-agnostic; the per-provider logic lives in the
 * adapter classes under includes/integrations/adapters/.
 */

require_once __DIR__ . '/../db.php';

class IntegrationClient {
    /** @var PDO */
    public $db;
    /** @var array the api_integrations row */
    public $integration;
    private $key;

    public function __construct(PDO $db, array $integration) {
        $this->db = $db;
        $this->integration = $integration;
    }

    /* ---------------------------------------------------------------- *
     *  Loading
     * ---------------------------------------------------------------- */

    public static function load(PDO $db, $id) {
        $stmt = $db->prepare("SELECT * FROM api_integrations WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new self($db, $row) : null;
    }

    public static function loadByType(PDO $db, $type, $provider = null) {
        $sql = "SELECT * FROM api_integrations WHERE integration_type = :t AND is_active = 1";
        $params = [':t' => $type];
        if ($provider !== null) {
            $sql .= " AND provider = :p";
            $params[':p'] = $provider;
        }
        $sql .= " ORDER BY id ASC LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new self($db, $row) : null;
    }

    public static function allActive(PDO $db, $type = null) {
        $sql = "SELECT * FROM api_integrations WHERE is_active = 1";
        $params = [];
        if ($type !== null) {
            $sql .= " AND integration_type = :t";
            $params[':t'] = $type;
        }
        $sql .= " ORDER BY provider ASC, id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return array_map(fn($r) => new self($db, $r), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ---------------------------------------------------------------- *
     *  Secret handling (openssl, key from env with fallback)
     * ---------------------------------------------------------------- */

    private function secretKey() {
        if ($this->key === null) {
            $env = getenv('SHP_INTEGRATION_KEY');
            if (!$env) {
                $msg = 'SHP_INTEGRATION_KEY is not set. Integration secrets are encrypted with a deterministic fallback key. Set the env var in production.';
                trigger_error($msg, E_USER_WARNING);
                $env = hash('sha256', ($GLOBALS['db_user'] ?? 'shp') . ':' . ($GLOBALS['db_name'] ?? 'shipping_db') . ':ascl-integration');
            }
            $this->key = $env;
        }
        return $this->key;
    }

    public function encryptSecret($value) {
        if ($value === null || $value === '') {
            return $value;
        }
        $ivlen = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $enc = openssl_encrypt((string)$value, 'AES-256-CBC', $this->secretKey(), 0, $iv);
        return 'enc::' . base64_encode($iv) . '::' . $enc;
    }

    public function decryptSecret($value) {
        if ($value === null || $value === '' || strpos($value, 'enc::') !== 0) {
            return $value;
        }
        $parts = explode('::', $value, 3);
        if (count($parts) !== 3) {
            return $value;
        }
        $iv = base64_decode($parts[1]);
        $dec = openssl_decrypt($parts[2], 'AES-256-CBC', $this->secretKey(), 0, $iv);
        return $dec === false ? '' : $dec;
    }

    public function apiKey()    { return $this->decryptSecret($this->integration['api_key_encrypted'] ?? ''); }
    public function apiSecret() { return $this->decryptSecret($this->integration['api_secret_encrypted'] ?? ''); }

    /* ---------------------------------------------------------------- *
     *  Rate limiting (per integration, fixed window in temp dir)
     * ---------------------------------------------------------------- */

    private function rateLimitAllowed() {
        $max = (int)($this->integration['rate_limit_per_minute'] ?? 60);
        if ($max <= 0) { $max = 60; }
        $file = sys_get_temp_dir() . '/shp_intg_rl_' . md5('intg_' . $this->integration['id']) . '.json';
        $now = time();
        $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
        $data = array_filter($data, fn($ts) => ($now - $ts) < 60);
        if (count($data) >= $max) {
            return false;
        }
        $data[] = $now;
        file_put_contents($file, json_encode($data), LOCK_EX);
        return true;
    }

    /* ---------------------------------------------------------------- *
     *  Request
     * ---------------------------------------------------------------- */

    /**
     * Perform an HTTP request to the integration endpoint.
     *
     * @param string $method GET/POST/PUT/DELETE
     * @param string $path   appended to endpoint_url (or full URL if it starts with http)
     * @param mixed  $body   array (json/form) or raw string
     * @param array  $query  query string params
     * @return array ['status'=>int,'headers'=>[],'body'=>mixed]
     */
    public function request($method, $path = '', $body = null, $query = []) {
        $base = rtrim($this->integration['endpoint_url'], '/');
        $url = (stripos($path, 'http') === 0) ? $path : ($base . '/' . ltrim($path, '/'));
        if (!empty($query)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        $format = $this->integration['request_format'] ?? 'json';
        $headers = $this->authHeaders();
        $content = null;
        if ($body !== null) {
            if (is_array($body)) {
                if ($format === 'form_data') {
                    $content = http_build_query($body);
                    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                } else {
                    $content = json_encode($body);
                    $headers[] = 'Content-Type: application/json';
                }
            } else {
                $content = (string)$body;
            }
        }

        $timeout = (int)($this->integration['timeout_seconds'] ?? 30);
        $retry = (int)($this->integration['retry_count'] ?? 3);
        $delay = (int)($this->integration['retry_delay_seconds'] ?? 5);

        $attempt = 0;
        $lastErr = null;
        $resp = null;
        while ($attempt <= $retry) {
            if ($attempt > 0) {
                sleep($delay);
            }
            if (!$this->rateLimitAllowed()) {
                $lastErr = 'Rate limit exceeded for integration #' . $this->integration['id'];
                $attempt++;
                if ($attempt <= $retry) { continue; }
                break;
            }
            $start = microtime(true);
            $logId = $this->logStart($method, $url, $headers, $body);
            try {
                $ctx = stream_context_create([
                    'http' => [
                        'method'          => strtoupper($method),
                        'header'          => implode("\r\n", $headers),
                        'content'         => $content,
                        'timeout'         => $timeout,
                        'ignore_errors'   => true,
                        'follow_location' => true,
                    ],
                ]);
                $suppress = function () {};
                set_error_handler($suppress);
                $raw = file_get_contents($url, false, $ctx);
                restore_error_handler();
                $meta = $http_response_header ?? [];
                $status = $this->parseStatus($meta);
                $respHeaders = $this->parseHeaders($meta);
                $resp = [
                    'status'  => $status,
                    'headers' => $respHeaders,
                    'body'    => $this->decodeBody($raw, $respHeaders),
                    'raw'     => $raw,
                ];
                $this->logEnd($logId, $status, $respHeaders, $raw, null, $start);
                if ($status >= 200 && $status < 300) {
                    $this->markSuccess();
                    return $resp;
                }
                // Retry only on transient failures.
                if ($status >= 500 || $status === 408 || $status === 429 || $status === 0) {
                    $lastErr = "HTTP $status";
                    $attempt++;
                    continue;
                }
                // 4xx other than retryable -> do not retry.
                $this->markFailure("HTTP $status: " . substr((is_string($raw) ? $raw : ''), 0, 500));
                return $resp;
            } catch (Exception $e) {
                $this->logEnd($logId, 0, [], null, $e->getMessage(), $start);
                $lastErr = $e->getMessage();
                $attempt++;
                if ($attempt <= $retry) { continue; }
            }
        }
        $this->markFailure($lastErr ?? 'Request failed');
        // Return last response (or a synthetic error) so callers can decide.
        return $resp ?? ['status' => 0, 'headers' => [], 'body' => null, 'raw' => null, 'error' => $lastErr];
    }

    private function authHeaders() {
        $type = $this->integration['auth_type'] ?? 'api_key';
        $key = $this->apiKey();
        $secret = $this->apiSecret();
        $h = [];
        switch ($type) {
            case 'basic':
                $h[] = 'Authorization: Basic ' . base64_encode($key . ':' . $secret);
                break;
            case 'bearer':
                $h[] = 'Authorization: Bearer ' . $key;
                break;
            case 'oauth':
                // Simple token; providers needing a token endpoint override in subclass.
                if ($secret) {
                    $h[] = 'Authorization: Bearer ' . $secret;
                } elseif ($key) {
                    $h[] = 'Authorization: Bearer ' . $key;
                }
                break;
            case 'api_key':
            default:
                // Default: send as header X-API-Key; subclasses may refine.
                if ($key) {
                    $h[] = 'X-API-Key: ' . $key;
                }
                break;
        }
        return $h;
    }

    private function parseStatus(array $meta) {
        foreach ($meta as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#i', $line, $m)) {
                return (int)$m[1];
            }
        }
        return 0;
    }

    private function parseHeaders(array $meta) {
        $out = [];
        foreach ($meta as $line) {
            if (preg_match('#^([^:]+):\s*(.*)$#', $line, $m)) {
                $out[strtolower(trim($m[1]))] = trim($m[2]);
            }
        }
        return $out;
    }

    private function decodeBody($raw, $headers) {
        if (!is_string($raw) || $raw === '') {
            return $raw;
        }
        $ct = $headers['content-type'] ?? '';
        if (stripos($ct, 'json') !== false) {
            $dec = json_decode($raw, true);
            return $dec === null ? $raw : $dec;
        }
        if (stripos($ct, 'xml') !== false) {
            return simplexml_load_string($raw);
        }
        return $raw;
    }

    /* ---------------------------------------------------------------- *
     *  Logging + bookkeeping
     * ---------------------------------------------------------------- */

    private function logStart($method, $url, $headers, $body) {
        try {
            $safeHeaders = $this->scrubHeaders($headers);
            $safeBody = $this->scrubBody($body);
            $db = $this->db->prepare("
                INSERT INTO api_integration_logs
                    (integration_id, endpoint_hit, http_method, request_headers, request_body, started_at)
                VALUES (:i, :u, :m, :h, :b, NOW())
            ");
            $db->execute([
                ':i' => $this->integration['id'], ':u' => $url, ':m' => strtoupper($method),
                ':h' => json_encode($safeHeaders), ':b' => is_scalar($safeBody) ? (string)$safeBody : json_encode($safeBody),
            ]);
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            return null;
        }
    }

    private function logEnd($logId, $code, $respHeaders, $raw, $error, $start) {
        if ($logId === null) { return; }
        try {
            $dur = max(0, (int)round((microtime(true) - $start) * 1000));
            $safeHeaders = $this->scrubHeaders($respHeaders);
            $this->db->prepare("
                UPDATE api_integration_logs
                SET response_code = :c, response_headers = :h, response_body = :b, error_message = :e, duration_ms = :d, completed_at = NOW()
                WHERE id = :id
            ")->execute([
                ':c' => $code, ':h' => json_encode($safeHeaders),
                ':b' => is_string($raw) ? substr($raw, 0, 20000) : json_encode($raw),
                ':e' => $error ? substr($error, 0, 1000) : null, ':d' => $dur, ':id' => $logId,
            ]);
        } catch (Exception $e) { /* best-effort */ }
    }

    private function scrubHeaders($headers) {
        $secretKeys = ['authorization', 'x-api-key', 'x-auth-token', 'cookie'];
        $out = [];
        foreach ((array)$headers as $k => $v) {
            $lk = is_int($k) ? strtolower(strtok(is_string($v) ? $v : '', ':')) : strtolower($k);
            $out[$k] = in_array($lk, $secretKeys, true) ? '[redacted]' : $v;
        }
        return $out;
    }

    private function scrubBody($body) {
        if (!is_array($body)) {
            return $body;
        }
        $secretKeys = ['api_key', 'api_secret', 'password', 'secret', 'token', 'client_secret', 'access_token'];
        foreach ($secretKeys as $sk) {
            if (isset($body[$sk])) {
                $body[$sk] = '[redacted]';
            }
        }
        return $body;
    }

    public function markSuccess() {
        try {
            $this->db->prepare("
                UPDATE api_integrations
                SET last_sync_at = NOW(), last_error = NULL, consecutive_failures = 0
                WHERE id = :id
            ")->execute([':id' => $this->integration['id']]);
        } catch (Exception $e) { /* ignore */ }
    }

    public function markFailure($msg) {
        try {
            $this->db->prepare("
                UPDATE api_integrations
                SET consecutive_failures = consecutive_failures + 1, last_error = :e, last_sync_at = NOW()
                WHERE id = :id
            ")->execute([':id' => $this->integration['id'], ':e' => substr((string)$msg, 0, 1000)]);
        } catch (Exception $e) { /* ignore */ }
    }
}
