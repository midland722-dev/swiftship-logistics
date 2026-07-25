<?php
/**
 * Payment Adapter — charge / refund / webhook verification for payment gateways.
 *
 * Bridges gateway lifecycle events into the local payments / invoices / receipts
 * tables. Provider-specific signature verification + payload parsing is done in
 * subclasses (StripeAdapter, PayPalAdapter).
 */

require_once __DIR__ . '/IntegrationClient.php';

class PaymentAdapter extends IntegrationClient {
    /**
     * Create a payment intent / charge for an invoice.
     * Returns gateway-specific data (e.g. client_secret, gateway_payment_id).
     */
    public function createIntent(array $invoice) {
        $resp = $this->request('POST', 'charges', $this->buildChargePayload($invoice));
        return $resp['body'] ?? null;
    }

    /** Build a provider-agnostic charge payload (override per provider). */
    protected function buildChargePayload(array $invoice) {
        return [
            'amount'      => (int)round((float)($invoice['total_amount'] ?? 0) * 100),
            'currency'    => strtolower($invoice['currency'] ?? 'usd'),
            'description' => 'Invoice ' . ($invoice['invoice_number'] ?? $invoice['id'] ?? ''),
            'reference'   => $invoice['id'] ?? null,
        ];
    }

    /**
     * Verify an inbound webhook request and return normalized events.
     * Must be implemented by provider subclasses.
     * @return array list of normalized events: ['type','gateway_payment_id','status','amount','currency','invoice_id','raw']
     */
    public function verifyWebhook($payloadRaw, $headers) {
        return [];
    }

    /**
     * Apply a normalized gateway event to local records (idempotent).
     */
    public function applyEvent(array $event) {
        $gid = $event['gateway_payment_id'] ?? null;
        if (!$gid) {
            return false;
        }
        try {
            // Idempotency: skip if we already processed this gateway payment id.
            $chk = $this->db->prepare("SELECT id FROM payment_intents WHERE gateway_payment_id = :g LIMIT 1");
            $chk->execute([':g' => $gid]);
            if (!$chk->fetchColumn()) {
                $this->db->prepare("
                    INSERT INTO payment_intents (gateway, gateway_payment_id, invoice_id, status, amount, currency, metadata, created_at)
                    VALUES (:gw, :g, :inv, :st, :amt, :cur, :meta, NOW())
                ")->execute([
                    ':gw' => $this->integration['provider'], ':g' => $gid,
                    ':inv' => $event['invoice_id'] ?? null, ':st' => $event['status'] ?? 'pending',
                    ':amt' => $event['amount'] ?? null, ':cur' => $event['currency'] ?? null,
                    ':meta' => json_encode($event['raw'] ?? [], JSON_UNESCAPED_SLASHES),
                ]);
            } else {
                $this->db->prepare("
                    UPDATE payment_intents SET status = :st, updated_at = NOW()
                    WHERE gateway_payment_id = :g
                ")->execute([':st' => $event['status'] ?? 'pending', ':g' => $gid]);
            }

            // Reflect into payments table when completed.
            if (in_array($event['status'], ['succeeded', 'paid', 'completed'], true)) {
                $existing = $this->db->prepare("SELECT id FROM payments WHERE transaction_id = :t LIMIT 1");
                $existing->execute([':t' => $gid]);
                if (!$existing->fetchColumn()) {
                    $this->db->prepare("
                        INSERT INTO payments (reference_number, invoice_id, shipment_id, customer_id, amount, currency, payment_method, status, transaction_id, paid_at, created_at)
                        SELECT :ref, i.id, i.shipment_id, i.customer_id, :amt, :cur, :pm, 'completed', :t, NOW(), NOW()
                        FROM invoices i WHERE i.id = :inv
                    ")->execute([
                        ':ref' => 'PAY-' . substr(md5($gid), 0, 12),
                        ':amt' => $event['amount'] ?? 0, ':cur' => $event['currency'] ?? 'USD',
                        ':pm' => $this->integration['provider'], ':t' => $gid, ':inv' => $event['invoice_id'] ?? 0,
                    ]);
                }
            }
            return true;
        } catch (Exception $e) {
            $this->markFailure('applyEvent: ' . $e->getMessage());
            return false;
        }
    }
}
