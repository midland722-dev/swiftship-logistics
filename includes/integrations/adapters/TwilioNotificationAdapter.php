<?php
/**
 * Twilio SMS adapter. Uses Basic auth (Account SID : Auth Token) and
 * form-encoded Messages.json endpoint.
 */
require_once __DIR__ . '/NotificationAdapter.php';

class TwilioNotificationAdapter extends NotificationAdapter {
    protected function sendPath() { return '2010-04-01/Accounts/' . rawurlencode($this->apiKey()) . '/Messages.json'; }
    protected function sendQuery() { return []; }

    protected function buildPayload($to, $subject, $body) {
        return [
            'To'   => $to,
            'From' => $this->integration['webhook_url'] ?: $this->apiSecret(),
            'Body' => $body,
        ];
    }

    protected function authHeaders() {
        // Twilio requires Basic auth with SID:Token.
        $h = parent::authHeaders();
        // Remove the generic X-API-Key added by parent for api_key auth.
        $h = array_filter($h, fn($l) => stripos($l, 'X-API-Key:') !== 0);
        $h[] = 'Authorization: Basic ' . base64_encode($this->apiKey() . ':' . $this->apiSecret());
        return array_values($h);
    }
}
