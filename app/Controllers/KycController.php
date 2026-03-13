<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\KycService;
use App\Services\KycDocumentUploadService;
use App\Exceptions\ValidationException;

class KycController extends BaseController
{
    private KycService $kycService;
    private KycDocumentUploadService $documentService;

    public function __construct()
    {
        parent::__construct();
        $this->kycService = new KycService();
        $this->documentService = new KycDocumentUploadService();
    }

    /**
     * GET /api/kyc
     * Get current user's KYC submission
     */
    public function show(): void
    {
        $this->requireAuth();
        $submission = $this->kycService->getSubmission($this->authUserId());
        $this->jsonResponse([
            'submission' => $submission?->toArray() ?? null,
        ]);
    }

    /**
     * POST /api/kyc
     * Submit KYC application
     */
    public function submit(): void
    {
        $this->requireAuth();

        try {
            $submission = $this->kycService->submit($this->authUserId(), $this->getData());
            $this->jsonResponse(['submission' => $submission->toArray()], 201, 'KYC submitted');
        } catch (ValidationException $e) {
            $this->errorResponse($e->getMessage(), 422, $e->getErrors());
        } catch (\Throwable $e) {
            $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/kyc/documents
     * Upload KYC document
     */
    public function uploadDocument(): void
    {
        $this->requireAuth();

        // Get or create KYC submission
        $submission = $this->kycService->getSubmission($this->authUserId());
        if (!$submission) {
            $this->errorResponse('No KYC submission found. Please submit KYC first.', 400);
            return;
        }

        $documentType = (string) ($this->getData('document_type') ?? '');

        if (empty($_FILES['document'])) {
            $this->errorResponse('Document file is required', 400);
            return;
        }

        try {
            $document = $this->documentService->uploadDocument(
                (int) $submission->id,
                $documentType,
                $_FILES['document']
            );

            $this->jsonResponse(
                ['document' => $document],
                201,
                'Document uploaded successfully'
            );
        } catch (ValidationException $e) {
            $this->errorResponse($e->getMessage(), 422, $e->getErrors());
        } catch (\Throwable $e) {
            $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/admin/kyc/pending
     * Get pending KYC submissions (Admin only)
     */
    public function pending(): void
    {
        $this->authorize('manage_kyc');

        $page = (int) ($this->getData('page') ?? 1);
        $perPage = (int) ($this->getData('per_page') ?? 20);

        $result = $this->kycService->getPendingSubmissions($page, $perPage);

        $this->jsonResponse($result);
    }

    /**
     * POST /api/admin/kyc/{id}/approve
     * Approve KYC submission (Admin only)
     */
    public function approve(int $id): void
    {
        $this->authorize('manage_kyc');

        try {
            $this->kycService->approve(
                $id,
                $this->authUserId(),
                (string) ($this->getData('notes') ?? '')
            );

            $this->jsonResponse(['message' => 'KYC approved successfully']);
        } catch (\Throwable $e) {
            $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/admin/kyc/{id}/reject
     * Reject KYC submission (Admin only)
     */
    public function reject(int $id): void
    {
        $this->authorize('manage_kyc');

        $reason = (string) ($this->getData('reason') ?? '');
        if (empty($reason)) {
            $this->errorResponse('Rejection reason is required', 400);
            return;
        }

        try {
            $this->kycService->reject($id, $this->authUserId(), $reason);
            $this->jsonResponse(['message' => 'KYC rejected']);
        } catch (\Throwable $e) {
            $this->errorResponse($e->getMessage(), 400);
        }
    }
}
