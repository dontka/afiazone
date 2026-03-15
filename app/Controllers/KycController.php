<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\KycSubmission;
use App\Models\KycDocument;
use App\Services\KycService;
use App\Services\KycDocumentUploadService;
use Exception;

class KycController extends BaseController
{
    private KycService $kycService;
    private KycDocumentUploadService $documentUploadService;

    public function __construct()
    {
        parent::__construct();
        $this->kycService = new KycService();
        $this->documentUploadService = new KycDocumentUploadService();
    }

    /**
     * Afficher l'état de ma soumission KYC
     * GET /api/kyc
     */
    public function show(): void
    {
        try {
            $this->requireAuth();
            
            $userId = $this->getCurrentUserId();
            $submission = KycSubmission::where('user_id', $userId)->first();
            
            if (!$submission) {
                $this->success(null, 'No KYC submission found');
                return;
            }
            
            $submission->load('documents');
            
            $this->success([
                'id' => $submission->id,
                'user_id' => $submission->user_id,
                'status' => $submission->status,
                'submission_date' => $submission->submission_date,
                'review_date' => $submission->review_date,
                'rejection_reason' => $submission->rejection_reason,
                'internal_notes' => $submission->internal_notes,
                'documents' => $submission->documents->map(fn($doc) => [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'file_url' => $doc->file_url,
                    'verification_status' => $doc->verification_status,
                    'uploaded_at' => $doc->uploaded_at,
                ])->toArray(),
            ]);
        } catch (Exception $e) {
            $this->error('Failed to fetch KYC submission: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Soumettre une nouvelle demande KYC
     * POST /api/kyc
     */
    public function submit(): void
    {
        try {
            $this->requireAuth();
            
            $userId = $this->getCurrentUserId();
            
            // Vérifier qu'aucune soumission n'existe
            $existing = KycSubmission::where('user_id', $userId)->first();
            if ($existing) {
                $this->error('KYC submission already exists. Current status: ' . $existing->status, 409);
                return;
            }
            
            $data = $this->getJsonBody([
                'identity_type' => 'required|string',
                'notes' => 'nullable|string',
            ]);
            
            if ($data === null) {
                return;
            }
            
            $submission = new KycSubmission();
            $submission->user_id = $userId;
            $submission->status = 'pending';
            $submission->submission_date = date('Y-m-d H:i:s');
            $submission->save();
            
            $this->success([
                'id' => $submission->id,
                'user_id' => $submission->user_id,
                'status' => $submission->status,
                'submission_date' => $submission->submission_date,
            ], 'KYC submission created successfully', 201);
        } catch (Exception $e) {
            $this->error('Failed to submit KYC: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Uploader un document KYC
     * POST /api/kyc/documents
     */
    public function uploadDocument(): void
    {
        try {
            $this->requireAuth();
            
            $userId = $this->getCurrentUserId();
            $submission = KycSubmission::where('user_id', $userId)->first();
            
            if (!$submission) {
                $this->error('No KYC submission found. Please submit KYC first', 400);
                return;
            }
            
            if (!isset($_FILES['document'])) {
                $this->error('Document file is required', 400);
                return;
            }
            
            $file = $_FILES['document'];
            $documentType = $this->getQueryParam('type') ?? $this->getJsonBody(['type' => 'required'])['type'] ?? '';
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->error('File upload error: ' . $file['error'], 400);
                return;
            }
            
            $document = $this->documentUploadService->upload(
                $submission->id,
                $documentType,
                $file['tmp_name']
            );
            
            $this->success([
                'id' => $document->id,
                'document_type' => $document->document_type,
                'file_url' => $document->file_url,
                'verification_status' => $document->verification_status,
                'uploaded_at' => $document->uploaded_at,
            ], 'Document uploaded successfully', 201);
        } catch (Exception $e) {
            $this->error('Failed to upload document: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Valider la complétude des documents
     * POST /api/kyc/validate
     */
    public function validateDocuments(): void
    {
        try {
            $this->requireAuth();
            
            $userId = $this->getCurrentUserId();
            $submission = KycSubmission::where('user_id', $userId)->first();
            
            if (!$submission) {
                $this->error('No KYC submission found', 404);
                return;
            }
            
            $hasAllRequired = $this->documentUploadService->hasAllRequiredDocuments($submission->id);
            
            $this->success([
                'submission_id' => $submission->id,
                'is_complete' => $hasAllRequired,
                'message' => $hasAllRequired ? 'All required documents submitted' : 'Missing required documents',
            ]);
        } catch (Exception $e) {
            $this->error('Failed to validate documents: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Lister les soumissions KYC (admin seulement)
     * GET /api/admin/kyc
     */
    public function adminList(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin', 'moderator']);
            
            $page = (int)($this->getQueryParam('page') ?? 1);
            $perPage = (int)($this->getQueryParam('per_page') ?? 15);
            $status = $this->getQueryParam('status');
            
            $query = KycSubmission::query();
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $total = $query->count();
            $submissions = $query
                ->orderByDesc('submission_date')
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get();
            
            $submissions->load('user', 'documents');
            
            $data = [];
            foreach ($submissions as $submission) {
                $data[] = [
                    'id' => $submission->id,
                    'user_id' => $submission->user_id,
                    'user_email' => $submission->user?->email,
                    'status' => $submission->status,
                    'submission_date' => $submission->submission_date,
                    'review_date' => $submission->review_date,
                    'document_count' => $submission->documents->count(),
                ];
            }
            
            $this->success([
                'submissions' => $data,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'pages' => ceil($total / $perPage),
                ],
            ]);
        } catch (Exception $e) {
            $this->error('Failed to list KYC submissions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Lister les soumissions KYC en attente
     * GET /api/admin/kyc/pending
     */
    public function pending(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin', 'moderator']);
            
            $page = (int)($this->getQueryParam('page') ?? 1);
            $perPage = (int)($this->getQueryParam('per_page') ?? 15);
            
            $total = KycSubmission::where('status', 'pending')->count();
            $submissions = KycSubmission::where('status', 'pending')
                ->orderBy('submission_date')
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get();
            
            $submissions->load('user', 'documents');
            
            $data = [];
            foreach ($submissions as $submission) {
                $data[] = [
                    'id' => $submission->id,
                    'user_id' => $submission->user_id,
                    'user_email' => $submission->user?->email,
                    'submission_date' => $submission->submission_date,
                    'document_count' => $submission->documents->count(),
                ];
            }
            
            $this->success([
                'pending_submissions' => $data,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'pages' => ceil($total / $perPage),
                ],
            ]);
        } catch (Exception $e) {
            $this->error('Failed to list pending submissions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher le détail d'une soumission KYC
     * GET /api/admin/kyc/{id}
     */
    public function adminShow(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin', 'moderator']);
            
            $id = $this->getPathParam('id');
            if (!$id) {
                $this->error('KYC submission ID is required', 400);
                return;
            }
            
            $submission = KycSubmission::find((int)$id);
            if (!$submission) {
                $this->error('KYC submission not found', 404);
                return;
            }
            
            $submission->load('user', 'documents', 'reviewer');
            
            $this->success([
                'id' => $submission->id,
                'user_id' => $submission->user_id,
                'status' => $submission->status,
                'submission_date' => $submission->submission_date,
                'review_date' => $submission->review_date,
                'rejection_reason' => $submission->rejection_reason,
                'internal_notes' => $submission->internal_notes,
                'user' => $submission->user ? [
                    'id' => $submission->user->id,
                    'email' => $submission->user->email,
                    'first_name' => $submission->user->first_name,
                    'last_name' => $submission->user->last_name,
                ] : null,
                'documents' => $this->documentUploadService->getDocuments($submission->id),
                'reviewer' => $submission->reviewer ? [
                    'id' => $submission->reviewer->id,
                    'email' => $submission->reviewer->email,
                ] : null,
            ]);
        } catch (Exception $e) {
            $this->error('Failed to fetch KYC submission: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Approuver une soumission KYC
     * POST /api/admin/kyc/{id}/approve
     */
    public function approve(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin', 'moderator']);
            
            $id = $this->getPathParam('id');
            if (!$id) {
                $this->error('KYC submission ID is required', 400);
                return;
            }
            
            $submission = KycSubmission::find((int)$id);
            if (!$submission) {
                $this->error('KYC submission not found', 404);
                return;
            }
            
            $data = $this->getJsonBody([
                'internal_notes' => 'nullable|string',
            ]);
            
            $submission->status = 'approved';
            $submission->review_date = date('Y-m-d H:i:s');
            $submission->reviewer_id = $this->getCurrentUserId();
            if ($data && isset($data['internal_notes'])) {
                $submission->internal_notes = $data['internal_notes'];
            }
            $submission->save();
            
            $this->success([
                'id' => $submission->id,
                'status' => $submission->status,
                'review_date' => $submission->review_date,
            ], 'KYC submission approved successfully');
        } catch (Exception $e) {
            $this->error('Failed to approve KYC submission: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Rejeter une soumission KYC
     * POST /api/admin/kyc/{id}/reject
     */
    public function reject(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin', 'moderator']);
            
            $id = $this->getPathParam('id');
            if (!$id) {
                $this->error('KYC submission ID is required', 400);
                return;
            }
            
            $data = $this->getJsonBody([
                'rejection_reason' => 'required|string',
                'internal_notes' => 'nullable|string',
            ]);
            
            if ($data === null) {
                return;
            }
            
            $submission = KycSubmission::find((int)$id);
            if (!$submission) {
                $this->error('KYC submission not found', 404);
                return;
            }
            
            $submission->status = 'rejected';
            $submission->review_date = date('Y-m-d H:i:s');
            $submission->reviewer_id = $this->getCurrentUserId();
            $submission->rejection_reason = $data['rejection_reason'];
            if (isset($data['internal_notes'])) {
                $submission->internal_notes = $data['internal_notes'];
            }
            $submission->save();
            
            $this->success([
                'id' => $submission->id,
                'status' => $submission->status,
                'review_date' => $submission->review_date,
            ], 'KYC submission rejected');
        } catch (Exception $e) {
            $this->error('Failed to reject KYC submission: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Demander une révision de la soumission KYC
     * POST /api/admin/kyc/{id}/request-revision
     */
    public function requestRevision(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin', 'moderator']);
            
            $id = $this->getPathParam('id');
            if (!$id) {
                $this->error('KYC submission ID is required', 400);
                return;
            }
            
            $data = $this->getJsonBody([
                'revision_reason' => 'required|string',
            ]);
            
            if ($data === null) {
                return;
            }
            
            $submission = KycSubmission::find((int)$id);
            if (!$submission) {
                $this->error('KYC submission not found', 404);
                return;
            }
            
            $submission->status = 'revision_requested';
            $submission->review_date = date('Y-m-d H:i:s');
            $submission->reviewer_id = $this->getCurrentUserId();
            $submission->rejection_reason = $data['revision_reason'];
            $submission->save();
            
            $this->success([
                'id' => $submission->id,
                'status' => $submission->status,
                'review_date' => $submission->review_date,
            ], 'Revision requested');
        } catch (Exception $e) {
            $this->error('Failed to request revision: ' . $e->getMessage(), 500);
        }
    }
}
