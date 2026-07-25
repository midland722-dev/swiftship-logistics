<?php
/**
 * Stripe payment adapter.
 * - charges via POST /v1/charges (api_key auth)
 * - webhook verification using the Stripe-Signature HMAC (signing secret in api_secret_encrypted)
 */
require_once __DIR__ . '/PaymentAdapter.php';

class StripePaymentAdapter extends PaymentAdapter {
    protected function buildChargePayload(array $invoice) {
        return [
            'amount'   => (int)round((float)($invoice['total_amount'] ?? 0) * 100),
            'currency' => strtolower($invoice['currency'] ?? 'usd'),
            'description' => 'Invoice ' . ($invoice['invoice_number'] ?? $invoice['id'] ?? ''),
            'metadata' => ['invoice_id' => $invoice['id'] ?? null],
        ];
    }

    public function verifyWebhook($payloadRaw, $headers) {
        $secret = $this->apiSecret();
        $sigHeader = $headers['stripe-signature'] ?? $headers['Stripe-Signature'] ?? '';
        if (!$secret || !$sigHeader) {
            $this->markFailure('Stripe: missing signature');
            return [];
        }
        // Format: t=<ts>,v1=<hmac>
        $pairs = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$k, $v] = array_map('trim', explode('=', $part, 2) + [1 => '']);
            $pairs[$k] = $v;
        }
        $expected = hash_hmac('sha256', $pairs['t'] . '.' . $payloadRaw, $secret);
        if (!hash_equals($expected, $pairs['v1'] ?? '')) {
            $this->markFailure('Stripe: signature mismatch');
            return [];
        }

        $data = json_decode($payloadRaw, true);
        $type = $data['type'] ?? '';
        $obj  = $data['data']['object'] ?? [];
        $statusMap = [
            'payment_intent.succeeded' => 'succeeded',
            'charge.succeeded'         => 'succeeded',
            'payment_intent.payment_failed' => 'failed',
            'charge.refunded'           => 'refunded',
        ];
        if (!isset($statusMap[$type])) {
            return [];
        }
        return [[
            'type'               => $type,
            'gateway_payment_id' => $obj['id'] ?? null,
            'status'             => $statusMap[$type],
            'amount'             => isset($obj['amount']) ? $obj['amount'] / 100 : null,
            'currency'           => $obj['currency'] ?? null,
            'invoice_id'         => $obj['metadata']['invoice_id'] ?? null,
            'raw'                => $data,
        ]];
    }
}
