<?php
/**
 * PayPal payment adapter.
 * - captures via v2/checkout/orders
 * - webhook verification: PayPal signs with a key id; we perform a simplified
 *   HMAC-SHA256 verification using the webhook signing secret (api_secret_encrypted).
 *   For full cert-based verification, call PayPal's /v1/notifications/verify-webhook-signature.
 */
require_once __DIR__ . '/PaymentAdapter.php';

class PayPalPaymentAdapter extends PaymentAdapter {
    protected function buildChargePayload(array $invoice) {
        $amount = number_format((float)($invoice['total_amount'] ?? 0), 2, '.', '');
        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => (string)($invoice['id'] ?? ''),
                'amount' => ['currency_code' => strtoupper($invoice['currency'] ?? 'USD'), 'value' => $amount],
            ]],
        ];
    }

    public function verifyWebhook($payloadRaw, $headers) {
        $secret = $this->apiSecret();
        $sig = $headers['paypal-transmission-sig'] ?? $headers['Paypal-Transmission-Sig'] ?? '';
        $ts  = $headers['paypal-transmission-time'] ?? $headers['Paypal-Transmission-Time'] ?? '';
        if (!$secret || !$sig) {
            $this->markFailure('PayPal: missing signature');
            return [];
        }
        // PayPal computes HMAC over: transmissionId + time + webhookId + crc32(payload).
        $crc = sprintf('%u', crc32($payloadRaw));
        $expected = base64_encode(hash_hmac('sha256', $ts . $crc, $secret, true));
        if (!hash_equals($expected, $sig)) {
            $this->markFailure('PayPal: signature mismatch');
            return [];
        }
        $data = json_decode($payloadRaw, true);
        $eventType = $data['event_type'] ?? '';
        $res = $data['resource'] ?? [];
        $statusMap = [
            'PAYMENT.CAPTURE.COMPLETED' => 'succeeded',
            'PAYMENT.CAPTURE.REFUNDED'  => 'refunded',
            'PAYMENT.ORDER.CANCELLED'   => 'cancelled',
        ];
        if (!isset($statusMap[$eventType])) {
            return [];
        }
        return [[
            'type'               => $eventType,
            'gateway_payment_id' => $res['id'] ?? null,
            'status'             => $statusMap[$eventType],
            'amount'             => isset($res['amount']['value']) ? (float)$res['amount']['value'] : null,
            'currency'           => $res['amount']['currency_code'] ?? null,
            'invoice_id'         => $res['supplementary_data']['related_ids']['order_id'] ?? null,
            'raw'                => $data,
        ]];
    }
}
