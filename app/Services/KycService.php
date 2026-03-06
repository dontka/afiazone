<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\KycSubmission;
use App\Models\KycDocument;

class KycService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getSubmission(int $userId): ?KycSubmission
    {
        return KycSubmission::findByUserId($userId);
    }

    public function submit(int $userId, array $data): KycSubmission
    {
        $errors = $this->validate($data, [
            'document_type' => 'required|in:id_card,passport,driver_license',
        ]);
        $this->throwIfErrors($errors);

        $existing = KycSubmission::findByUserId($userId);
        if ($existing && $existing->status === 'pending') {
            $this->throwIfErrors(['kyc' => 'A pending submission already exists']);
        }

        $submission = KycSubmission::create([
            'user_id' => $userId,
            'document_type' => $data['document_type'],
            'status' => 'pending',
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        $this->log('KYC submitted', ['user_id' => $userId, 'submission_id' => $submission->id]);
        return $submission;
    }

    public function addDocument(int $submissionId, string $type, string $filePath): KycDocument
    {
        return KycDocument::create([
            'kyc_submission_id' => $submissionId,
            'document_type' => $type,
            'file_path' => $filePath,
            'file_name' => basename($filePath),
            'file_size' => filesize($filePath) ?: 0,
        ]);
    }

    public function approve(int $submissionId, int $reviewerId, string $notes = ''): bool
    {
        $submission = KycSubmission::find($submissionId);
        if (!$submission) {
            return false;
        }

        $submission->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
        ]);

        $this->log('KYC approved', ['submission_id' => $submissionId, 'reviewer_id' => $reviewerId]);
        return true;
    }

    public function reject(int $submissionId, int $reviewerId, string $reason): bool
    {
        $submission = KycSubmission::find($submissionId);
        if (!$submission) {
            return false;
        }

        $submission->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason,
        ]);

        $this->log('KYC rejected', ['submission_id' => $submissionId, 'reason' => $reason]);
        return true;
    }
}
