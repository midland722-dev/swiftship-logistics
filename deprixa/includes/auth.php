<?php
require_once __DIR__ . '/../../includes/db.php';

class LoginSecurity {
    private $db;
    private $max_attempts = 5;
    private $lockout_minutes = 15;
    private $cache_prefix = 'login_attempt_';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    private function getCacheKey($identifier) {
        return $this->cache_prefix . md5($identifier);
    }
    
    private function getIpAddress() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    public function isLockedOut($identifier) {
        $ipAddress = $this->getIpAddress();
        $key = $this->getCacheKey($identifier . '|' . $ipAddress);
        $data = $this->getCacheData($key);
        
        if ($data && $data['attempts'] >= $this->max_attempts) {
            if (time() - $data['last_attempt'] < ($this->lockout_minutes * 60)) {
                $remaining = ceil(($this->lockout_minutes * 60) - (time() - $data['last_attempt']));
                return ['locked' => true, 'remaining' => $remaining];
            } else {
                $this->clearAttempts($identifier);
            }
        }
        
        return ['locked' => false];
    }
    
    public function recordFailedAttempt($identifier) {
        $ipAddress = $this->getIpAddress();
        $key = $this->getCacheKey($identifier . '|' . $ipAddress);
        $data = $this->getCacheData($key);
        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        $data['last_attempt'] = time();
        $this->setCacheData($key, $data);
        
        $this->logFailedAttempt($identifier);
        
        return $this->max_attempts - $data['attempts'];
    }
    
    public function clearAttempts($identifier) {
        $ipAddress = $this->getIpAddress();
        $key = $this->getCacheKey($identifier . '|' . $ipAddress);
        $this->setCacheData($key, ['attempts' => 0, 'last_attempt' => 0]);
    }
    
    private function getCacheData($key) {
        $cache_file = sys_get_temp_dir() . '/' . $key . '.json';
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            return $data ?? ['attempts' => 0, 'last_attempt' => 0];
        }
        return ['attempts' => 0, 'last_attempt' => 0];
    }
    
    private function setCacheData($key, $data) {
        $cache_file = sys_get_temp_dir() . '/' . $key . '.json';
        file_put_contents($cache_file, json_encode($data), LOCK_EX);
    }
    
    private function logFailedAttempt($identifier) {
        try {
            $ip = $this->getIpAddress();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $timestamp = date('Y-m-d H:i:s');
            
            if ($this->db) {
                $stmt = $this->db->prepare("
                    INSERT INTO activity_logs (user_id, action, entity_type, description, ip_address, user_agent, created_at)
                    VALUES (NULL, 'login_failed', 'auth', :description, :ip, :ua, NOW())
                ");
                $stmt->execute([
                    ':description' => "Failed login attempt for: $identifier from IP: $ip",
                    ':ip' => $ip,
                    ':ua' => substr($userAgent, 0, 255)
                ]);
            }
        } catch (Exception $e) {
            error_log("Failed to log login attempt: " . $e->getMessage());
        }
    }
    
    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $_SESSION['csrf_token_time'] = time();
        return $_SESSION['csrf_token'];
    }
    
    public function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        if (($_SESSION['csrf_token_time'] ?? 0) < time() - 3600) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public function regenerateSession() {
        session_regenerate_id(true);
    }
    
    public function getRemainingAttempts($identifier) {
        $lockout = $this->isLockedOut($identifier);
        if ($lockout['locked']) {
            return 0;
        }
        $ipAddress = $this->getIpAddress();
        $key = $this->getCacheKey($identifier . '|' . $ipAddress);
        $data = $this->getCacheData($key);
        return max(0, $this->max_attempts - $data['attempts']);
    }
}
