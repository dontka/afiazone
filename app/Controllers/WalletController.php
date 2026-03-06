<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\WalletService;

class WalletController extends BaseController
{
    private WalletService $walletService;

    public function __construct()
    {
        parent::__construct();
        $this->walletService = new WalletService();
    }

    public function show(): void
    {
        $this->requireAuth();
        $wallet = $this->walletService->getWallet($this->authUserId());
        $this->jsonResponse(['wallet' => $wallet->toArray()]);
    }

    public function topup(): void
    {
        $this->requireAuth();
        $amount = (float) $this->getData('amount');
        $method = (string) ($this->getData('payment_method') ?? 'card');

        $tx = $this->walletService->credit(
            $this->authUserId(),
            $amount,
            'Wallet top-up',
            $method
        );

        $this->jsonResponse(['transaction' => $tx->toArray()], 201);
    }

    public function transactions(): void
    {
        $this->requireAuth();
        $page = (int) ($this->getData('page') ?? 1);
        $result = $this->walletService->getTransactions($this->authUserId(), $page);
        $this->jsonResponse($result);
    }

    public function transfer(): void
    {
        $this->requireAuth();
        $amount = (float) $this->getData('amount');
        $recipientId = (int) $this->getData('recipient_id');

        // Debit sender
        $this->walletService->debit(
            $this->authUserId(),
            $amount,
            "Transfer to user #{$recipientId}"
        );

        // Credit recipient
        $this->walletService->credit(
            $recipientId,
            $amount,
            "Transfer from user #{$this->authUserId()}"
        );

        $this->jsonResponse(['message' => 'Transfer successful'], 201);
    }
}
