<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\KycDocument;
use Exception;

class KycDocumentUploadService extends BaseService
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    private const ALLOWED_MIME_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];
    private const UPLOAD_DIR = 'storage/uploads/kyc';

    /**
     * Types de documents KYC valides
     */
    private const VALID_DOCUMENT_TYPES = [
        'id_card',
        'passport',
        'national_id',
        'driver_license',
        'proof_of_address',
        'business_license',
        'tax_certificate',
    ];

    /**
     * Uploader un document KYC
     */
    public function upload(
        int $kycSubmissionId,
        string $documentType,
        string $filePath
    ): KycDocument {
        try {
            // Vérifier que le fichier existe
            if (!file_exists($filePath)) {
                throw new Exception('File not found');
            }
            
            // Vérifier le type de document
            if (!in_array($documentType, self::VALID_DOCUMENT_TYPES)) {
                throw new Exception('Invalid document type. Valid types: ' . implode(', ', self::VALID_DOCUMENT_TYPES));
            }
            
            // Obtenir les infos du fichier
            $fileSize = filesize($filePath);
            $mimeType = mime_content_type($filePath);
            $fileName = basename($filePath);
            
            // Vérifier la taille
            if ($fileSize > self::MAX_FILE_SIZE) {
                throw new Exception('File size exceeds maximum allowed (10MB)');
            }
            
            // Vérifier le type MIME
            if (!array_key_exists($mimeType, self::ALLOWED_MIME_TYPES)) {
                throw new Exception('File type not allowed');
            }
            
            // Créer le répertoire s'il n'existe pas
            if (!is_dir(self::UPLOAD_DIR)) {
                mkdir(self::UPLOAD_DIR, 0755, true);
            }
            
            // Générer un nouveau nom de fichier unique
            $ext = self::ALLOWED_MIME_TYPES[$mimeType];
            $newFileName = $this->generateFileName($documentType, $ext);
            $newFilePath = self::UPLOAD_DIR . '/' . $newFileName;
            
            // Copier le fichier
            if (!copy($filePath, $newFilePath)) {
                throw new Exception('Failed to save uploaded file');
            }
            
            // Créer l'enregistrement dans la BDD
            $document = new KycDocument();
            $document->kyc_submission_id = $kycSubmissionId;
            $document->document_type = $documentType;
            $document->file_url = '/' . $newFilePath;
            $document->file_name = $fileName;
            $document->mime_type = $mimeType;
            $document->file_size = $fileSize;
            $document->verification_status = 'pending';
            $document->save();
            
            return $document;
        } catch (Exception $e) {
            throw new Exception('Failed to upload KYC document: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier un document KYC
     */
    public function verifyDocument(int $documentId, bool $verified = true, string $reason = ''): KycDocument
    {
        try {
            $document = KycDocument::find($documentId);
            if (!$document) {
                throw new Exception('Document not found');
            }
            
            $document->verification_status = $verified ? 'verified' : 'rejected';
            $document->verified_at = date('Y-m-d H:i:s');
            $document->save();
            
            return $document;
        } catch (Exception $e) {
            throw new Exception('Failed to verify document: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un document KYC
     */
    public function delete(int $documentId): bool
    {
        try {
            $document = KycDocument::find($documentId);
            if (!$document) {
                throw new Exception('Document not found');
            }
            
            // Supprimer le fichier physique
            $filePath = ltrim($document->file_url, '/');
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Supprimer l'enregistrement de la BDD
            $document->delete();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to delete document: ' . $e->getMessage());
        }
    }

    /**
     * Générer un nom de fichier unique
     */
    private function generateFileName(string $documentType, string $ext): string
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return "kyc_{$documentType}_{$timestamp}_{$random}.{$ext}";
    }

    /**
     * Vérifier si tous les documents requis ont été soumis
     */
    public function hasAllRequiredDocuments(int $kycSubmissionId): bool
    {
        try {
            $requiredTypes = ['id_card', 'proof_of_address'];
            
            $documents = KycDocument::query()->where('kyc_submission_id', $kycSubmissionId)
                ->whereIn('document_type', $requiredTypes)
                ->get();
            
            $submittedTypes = $documents->pluck('document_type')->toArray();
            
            return count(array_diff($requiredTypes, $submittedTypes)) === 0;
        } catch (Exception $e) {
            throw new Exception('Failed to check required documents: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier si tous les documents soumis ont été approuvés
     */
    public function areAllDocumentsVerified(int $kycSubmissionId): bool
    {
        try {
            $documents = KycDocument::query()->where('kyc_submission_id', $kycSubmissionId)->get();
            
            if ($documents->isEmpty()) {
                return false;
            }
            
            foreach ($documents as $doc) {
                if ($doc->verification_status !== 'verified') {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to check document verification: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir tous les documents d'une soumission KYC
     */
    public function getDocuments(int $kycSubmissionId): array
    {
        try {
            $documents = KycDocument::query()->where('kyc_submission_id', $kycSubmissionId)
                ->orderBy('document_type')
                ->get();
            
            $result = [];
            foreach ($documents as $doc) {
                $result[] = [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'file_url' => $doc->file_url,
                    'file_name' => $doc->file_name,
                    'mime_type' => $doc->mime_type,
                    'file_size' => $doc->file_size,
                    'verification_status' => $doc->verification_status,
                    'uploaded_at' => $doc->uploaded_at,
                    'verified_at' => $doc->verified_at,
                ];
            }
            
            return $result;
        } catch (Exception $e) {
            throw new Exception('Failed to get documents: ' . $e->getMessage());
        }
    }
}
