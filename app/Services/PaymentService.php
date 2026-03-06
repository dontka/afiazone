<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\BaseModel;

class PaymentService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Initiate a payment for an order.
     */
    public function initiate(int $orderId, float $amount, string $method, array $meta = []): PaymentTransaction
    {
        $errors = $this->validate(
            ['method' => $method, 'amount' => $amount],
            ['method' => 'required|in:cash_on_delivery,wallet,card,mobile_money', 'amount' => 'required|numeric']
        );
        $this->throwIfErrors($errors);

        $tx = PaymentTransaction::create([
            'order_id' => $orderId,
            'transaction_ref' => $this->generateRef(),
            'payment_method' => $method,
            'amount' => $amount,
            'currency' => $meta['currency'] ?? 'XOF',
            'status' => $method === 'cash_on_delivery' ? 'pending_collection' : 'pending',
            'gateway_response' => null,
        ]);

        $this->log('Payment initiated', ['tx_id' => $tx->id, 'order_id' => $orderId, 'method' => $method]);
        return $tx;
    }

    /**
     * Mark a payment as completed (callback from gateway or manual).
     */
    public function confirm(int $transactionId, array $gatewayData = []): bool
    {
        $tx = PaymentTransaction::find($transactionId);
        if (!$tx || $tx->status === 'completed') {
            return false;
        }

        $tx->update([
            'status' => 'completed',
            'gateway_response' => json_encode($gatewayData),
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        $this->log('Payment confirmed', ['tx_id' => $transactionId]);
        return true;
    }

    /**
     * Mark a payment as failed.
     */
    public function fail(int $transactionId, string $reason = ''): bool
    {
        $tx = PaymentTransaction::find($transactionId);
        if (!$tx) {
            return false;
        }

        $tx->update([
            'status' => 'failed',
            'gateway_response' => json_encode(['error' => $reason]),
        ]);

        $this->log('Payment failed', ['tx_id' => $transactionId, 'reason' => $reason]);
        return true;
    }

    /**
     * Process a refund.
     */
    public function refund(int $transactionId, float $amount, string $reason = ''): ?PaymentTransaction
    {
        $original = PaymentTransaction::find($transactionId);
        if (!$original || $original->status !== 'completed') {
            return null;
        }

        $refundTx = PaymentTransaction::create([
            'order_id' => $original->order_id,
            'transaction_ref' => $this->generateRef(),
            'payment_method' => $original->payment_method,
            'amount' => -$amount,
            'currency' => $original->currency ?? 'XOF',
            'status' => 'refunded',
            'gateway_response' => json_encode(['original_tx' => $original->id, 'reason' => $reason]),
        ]);

        $this->log('Payment refunded', [
            'original_tx_id' => $transactionId,
            'refund_tx_id' => $refundTx->id,
            'amount' => $amount,
        ]);

        return $refundTx;
    }

    private function generateRef(): string
    {
        return 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
    }
}
