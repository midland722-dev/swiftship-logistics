<?php
/**
 * SendGrid email adapter. Bearer auth, JSON mail/send payload.
 */
require_once __DIR__ . '/NotificationAdapter.php';

class SendGridNotificationAdapter extends NotificationAdapter {
    protected function sendPath() { return 'mail/send'; }

    protected function buildPayload($to, $subject, $body) {
        return [
            'personalizations' => [['to' => [['email' => $to]]]],
            'from'    => ['email' => ($this->integration['webhook_url'] ?: 'noreply@ascl-logistics.com')],
            'subject' => $subject,
            'content' => [['type' => 'text/html', 'value' => $body]],
        ];
    }

    protected function authHeaders() {
        $h = parent::authHeaders();
        // SendGrid uses Bearer with the API key.
        return array_values(array_filter($h, fn($l) => stripos($l, 'X-API-Key:') !== 0)) ;
    }
}
