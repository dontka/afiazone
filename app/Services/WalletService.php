<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletReservation;
use App\Models\BaseModel;
use App\Repositories\WalletRepository;

class WalletService extends BaseService
{
    private WalletRepository $walletRepo;

    public function __construct()
    {
        parent::__construct();
        $this->walletRepo = new WalletRepository();
    }

    public function getWallet(int $userId): Wallet
    {
        return $this->walletRepo->getOrCreate($userId);
    }

    public function getBalance(int $userId): float
    {
        return $this->walletRepo->getBalance($userId);
    }

    public function getAvailableBalance(int $userId): float
    {
        return $this->walletRepo->getAvailableBalance($userId);
    }

    /**
     * Credit wallet (top-up, refund, etc.)
     */
    public function credit(
        int $userId,
        float $amount,
        string $description = '',
        string $paymentMethod = 'wallet',
        ?string $externalRef = null
    ): WalletTransaction {
        if ($amount <= 0) {
            $this->throwIfErrors(['amount' => 'Amount must be positive']);
        }

        $wallet = $this->getWallet($userId);

        return BaseModel::transaction(function () use ($wallet, $amount, $description, $paymentMethod, $externalRef) {
            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            $tx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => $paymentMethod,
                'external_reference' => $externalRef,
                'description' => $description,
                'status' => 'completed',
            ]);

            $wallet->update([
                'balance' => $balanceAfter,
                'available_balance' => $balanceAfter - (float) $wallet->reserved_balance,
                'total_received' => (float) $wallet->total_received + $amount,
            ]);

            $this->log('Wallet credited', [
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'tx_id' => $tx->id,
            ]);

            return $tx;
        });
    }

    /**
     * Debit wallet (purchase, fee, etc.)
     */
    public function debit(
        int $userId,
        float $amount,
        string $description = '',
        ?string $externalRef = null
    ): WalletTransaction {
        if ($amount <= 0) {
            $this->throwIfErrors(['amount' => 'Amount must be positive']);
        }

        $wallet = $this->getWallet($userId);

        if ((float) $wallet->available_balance < $amount) {
            $this->throwIfErrors(['balance' => 'Insufficient funds']);
        }

        return BaseModel::transaction(function () use ($wallet, $amount, $description, $externalRef) {
            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            $tx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => 'wallet',
                'external_reference' => $externalRef,
                'description' => $description,
                'status' => 'completed',
            ]);

            $wallet->update([
                'balance' => $balanceAfter,
                'available_balance' => $balanceAfter - (float) $wallet->reserved_balance,
                'total_spent' => (float) $wallet->total_spent + $amount,
            ]);

            $this->log('Wallet debited', [
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'tx_id' => $tx->id,
            ]);

            return $tx;
        });
    }

    /**
     * Reserve funds for a pending order
     */
    public function reserveFunds(int $userId, int $orderId, float $amount): WalletReservation
    {
        $wallet = $this->getWallet($userId);

        if ((float) $wallet->available_balance < $amount) {
            $this->throwIfErrors(['balance' => 'Insufficient funds to reserve']);
        }

        return BaseModel::transaction(function () use ($wallet, $orderId, $amount) {
            $reservation = WalletReservation::create([
                'wallet_id' => $wallet->id,
                'order_id' => $orderId,
                'amount' => $amount,
                'reason' => 'order_payment',
                'status' => 'reserved',
            ]);

            $wallet->update([
                'reserved_balance' => (float) $wallet->reserved_balance + $amount,
                'available_balance' => (float) $wallet->available_balance - $amount,
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'reserve',
                'amount' => $amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance,
                'description' => "Funds reserved for order #{$orderId}",
                'status' => 'completed',
            ]);

            $this->log('Funds reserved', ['wallet_id' => $wallet->id, 'amount' => $amount, 'order_id' => $orderId]);
            return $reservation;
        });
    }

    /**
     * Release previously reserved funds
     */
    public function releaseFunds(int $reservationId): bool
    {
        $reservation = WalletReservation::find($reservationId);
        if (!$reservation || $reservation->status !== 'reserved') {
            return false;
        }

        $wallet = Wallet::find($reservation->wallet_id);
        if (!$wallet) {
            return false;
        }

        return (bool) BaseModel::transaction(function () use ($wallet, $reservation) {
            $reservation->update([
                'status' => 'released',
                'released_at' => date('Y-m-d H:i:s'),
            ]);

            $wallet->update([
                'reserved_balance' => max(0, (float) $wallet->reserved_balance - (float) $reservation->amount),
                'available_balance' => (float) $wallet->available_balance + (float) $reservation->amount,
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'release',
                'amount' => $reservation->amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance,
                'description' => 'Funds released from reservation #' . $reservation->id,
                'status' => 'completed',
            ]);

            $this->log('Funds released', ['reservation_id' => $reservation->id]);
            return true;
        });
    }

    public function getTransactions(int $userId, int $page = 1, int $perPage = 50): array
    {
        $wallet = $this->getWallet($userId);
        return WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $perPage);
    }
}
