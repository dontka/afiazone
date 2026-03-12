<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\KycService;

class KycController extends BaseController
{
    private KycService $kycService;

    public function __construct()
    {
        parent::__construct();
        $this->kycService = new KycService();
    }

    public function show(): void
    {
        $this->requireAuth();
        $submission = $this->kycService->getSubmission($this->authUserId());
        $this->jsonResponse([
            'submission' => $submission?->toArray() ?? null,
        ]);
    }

    public function submit(): void
    {
        $this->requireAuth();
        $submission = $this->kycService->submit($this->authUserId(), $this->getData());
        $this->jsonResponse(['submission' => $submission->toArray()], 201);
    }

    public function approve(int $id): void
    {
        $this->authorize('manage_kyc');
        $this->kycService->approve($id, $this->authUserId(), (string) ($this->getData('notes') ?? ''));
        $this->jsonResponse(['message' => 'KYC approved']);
    }

    public function reject(int $id): void
    {
        $this->authorize('manage_kyc');
        $this->kycService->reject($id, $this->authUserId(), (string) ($this->getData('reason') ?? ''));
        $this->jsonResponse(['message' => 'KYC rejected']);
    }
}
