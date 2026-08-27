<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use RuntimeException;

class FileStorage
{
    public function __construct(private readonly string $rootPath)
    {
    }

    public function storeUploadedFile(array $file, string $directory, array $allowedMimeTypes, int $maxBytes = 5242880): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Upload de fichier invalide.');
        }

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if (! is_uploaded_file($temporaryPath) || $size < 1 || $size > $maxBytes) {
            throw new InvalidArgumentException('Fichier refuse.');
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        if (! is_string($mimeType) || ! in_array($mimeType, $allowedMimeTypes, true)) {
            throw new InvalidArgumentException('Type de fichier refuse.');
        }

        $extension = match ($mimeType) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new InvalidArgumentException('Extension non autorisee.'),
        };
        $targetDirectory = rtrim($this->rootPath, '/\\') . DIRECTORY_SEPARATOR . trim($directory, '/\\');
        if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0750, true) && ! is_dir($targetDirectory)) {
            throw new RuntimeException('Impossible de creer le dossier de stockage.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;
        if (! move_uploaded_file($temporaryPath, $targetPath)) {
            throw new RuntimeException('Impossible de sauvegarder le fichier.');
        }

        chmod($targetPath, 0640);

        return trim($directory, '/\\') . '/' . $filename;
    }
}