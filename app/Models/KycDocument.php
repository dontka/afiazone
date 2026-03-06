<?php

declare(strict_types=1);

namespace App\Models;

class KycDocument extends BaseModel
{
    protected string $table = 'kyc_documents';

    protected array $fillable = [
        'kyc_submission_id',
        'document_type',
        'file_url',
        'file_name',
        'mime_type',
        'file_size',
        'verification_status',
    ];
}
