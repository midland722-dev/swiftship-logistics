<?php
/**
 * Notification Adapter — send SMS / Email via a provider (Twilio / SendGrid / SMTP).
 *
 * Writes every send attempt to communication_logs (and communication_logs_enhanced
 * when available) for audit + retry. Respects the integration's channel type
 * (sms vs email) derived from integration_type.
 */

require_once __DIR__ . '/IntegrationClient.php';

class NotificationAdapter extends IntegrationClient {
    /**
     * Send a message.
     * @param string $to        email or phone number
     * @param string $subject   (email) or null (sms)
     * @param string $body      message text/html
     * @param array  $opts      ['template_key','priority','recipient_type','recipient_id']
     * @return bool success
     */
    public function send($to, $subject, $body, array $opts = []) {
        $channel = $this->integration['integration_type'] === 'sms' ? 'sms' : 'email';
        $logId = $this->logCommunication($to, $subject, $body, $channel, $opts);

        try {
            $payload = $this->buildPayload($to, $subject, $body);
            $resp = $this->request('POST', $this->sendPath(), $payload, $this->sendQuery());
            $ok = ($resp['status'] ?? 0) >= 200 && ($resp['status'] ?? 0) < 300;
            $this->updateCommunication($logId, $ok ? 'sent' : 'failed', $resp['raw'] ?? null,
                $resp['body']['sid'] ?? $resp['body']['id'] ?? null);
            return $ok;
        } catch (Exception $e) {
            $this->updateCommunication($logId, 'failed', $e->getMessage(), null);
            return false;
        }
    }

    protected function sendPath()  { return 'messages'; }
    protected function sendQuery() { return []; }

    /** Build provider payload (override per provider). */
    protected function buildPayload($to, $subject, $body) {
        return ['to' => $to, 'subject' => $subject, 'body' => $body];
    }

    private function logCommunication($to, $subject, $body, $channel, $opts) {
        try {
            // Prefer the enhanced table; fall back to the base table.
            $enhanced = true;
            try { $this->db->query("SELECT 1 FROM communication_logs_enhanced LIMIT 1"); }
            catch (Exception $e) { $enhanced = false; }

            if ($enhanced) {
                $this->db->prepare("
                    INSERT INTO communication_logs_enhanced
                        (type, provider, recipient_type, recipient_id, recipient_address, subject, body, template_key, priority, status, created_at)
                    VALUES (:t, :p, :rt, :ri, :ra, :s, :b, :tk, :pr, 'queued', NOW())
                ")->execute([
                    ':t' => $channel, ':p' => $this->integration['provider'],
                    ':rt' => $opts['recipient_type'] ?? 'customer', ':ri' => $opts['recipient_id'] ?? null,
                    ':ra' => $to, ':s' => $subject, ':b' => $body,
                    ':tk' => $opts['template_key'] ?? null, ':pr' => $opts['priority'] ?? 'normal',
                ]);
            } else {
                $this->db->prepare("
                    INSERT INTO communication_logs
                        (type, template_key, recipient_type, recipient_id, recipient_address, subject, body, provider, status, created_at)
                    VALUES (:t, :tk, :rt, :ri, :ra, :s, :b, :p, 'queued', NOW())
                ")->execute([
                    ':t' => $channel, ':tk' => $opts['template_key'] ?? null,
                    ':rt' => $opts['recipient_type'] ?? 'customer', ':ri' => $opts['recipient_id'] ?? null,
                    ':ra' => $to, ':s' => $subject, ':b' => $body, ':p' => $this->integration['provider'],
                ]);
            }
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            return null;
        }
    }

    private function updateCommunication($logId, $status, $resp, $providerMsgId) {
        if ($logId === null) { return; }
        try {
            $this->db->prepare("
                UPDATE communication_logs_enhanced
                SET status = :s, provider_message_id = :pm, sent_at = NOW()
                WHERE id = :id
            ")->execute([':s' => $status, ':pm' => $providerMsgId, ':id' => $logId]);
        } catch (Exception $e) {
            try {
                $this->db->prepare("
                    UPDATE communication_logs
                    SET status = :s, provider_message_id = :pm, sent_at = NOW()
                    WHERE id = :id
                ")->execute([':s' => $status, ':pm' => $providerMsgId, ':id' => $logId]);
            } catch (Exception $e2) { /* ignore */ }
        }
    }
}
