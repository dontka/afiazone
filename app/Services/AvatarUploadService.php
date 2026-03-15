<?php

declare(strict_types=1);

namespace App\Services;

use Exception;

class AvatarUploadService extends BaseService
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const UPLOAD_DIR = 'storage/uploads/avatars';

    /**
     * Uploader et traiter un avatar
     */
    public function upload(string $filePath): string
    {
        try {
            // Vérifier que le fichier existe
            if (!file_exists($filePath)) {
                throw new Exception('File not found');
            }
            
            // Obtenir les infos du fichier
            $fileSize = filesize($filePath);
            $mimeType = mime_content_type($filePath);
            $fileName = basename($filePath);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Vérifier la taille
            if ($fileSize > self::MAX_FILE_SIZE) {
                throw new Exception('File size exceeds maximum allowed (5MB)');
            }
            
            // Vérifier le type MIME
            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                throw new Exception('File type not allowed. Allowed types: JPEG, PNG, GIF, WebP');
            }
            
            // Vérifier l'extension
            if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
                throw new Exception('File extension not allowed');
            }
            
            // Créer le répertoire s'il n'existe pas
            if (!is_dir(self::UPLOAD_DIR)) {
                mkdir(self::UPLOAD_DIR, 0755, true);
            }
            
            // Générer un nouveau nom de fichier unique
            $newFileName = $this->generateFileName($ext);
            $newFilePath = self::UPLOAD_DIR . '/' . $newFileName;
            
            // Redimensionner et optimiser l'image
            $this->processImage($filePath, $newFilePath, $ext);
            
            // Retourner l'URL relative
            return '/' . $newFilePath;
        } catch (Exception $e) {
            throw new Exception('Failed to upload avatar: ' . $e->getMessage());
        }
    }

    /**
     * Traiter (redimensionner, optimiser) une image avatar
     */
    private function processImage(string $sourcePath, string $destPath, string $ext): void
    {
        try {
            // Vérifier si GD est disponible
            if (!extension_loaded('gd')) {
                // Si GD n'est pas disponible, copier le fichier tel quel
                copy($sourcePath, $destPath);
                return;
            }
            
            // Charger l'image selon son type
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($sourcePath);
                    break;
                case 'png':
                    $image = imagecreatefrompng($sourcePath);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($sourcePath);
                    break;
                case 'webp':
                    $image = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    throw new Exception('Unsupported image type');
            }
            
            if (!$image) {
                throw new Exception('Failed to load image');
            }
            
            // Obtenir les dimensions
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Redimensionner si nécessaire (max 500x500)
            $maxSize = 500;
            if ($width > $maxSize || $height > $maxSize) {
                $newWidth = $maxSize;
                $newHeight = $maxSize;
                
                // Calculer les dimensions proportionnelles
                $aspectRatio = $width / $height;
                if ($aspectRatio > 1) {
                    $newHeight = (int)($maxSize / $aspectRatio);
                } else {
                    $newWidth = (int)($maxSize * $aspectRatio);
                }
                
                // Créer une nouvelle image redimensionnée
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                
                // Préserver la transparence pour PNG
                if ($ext === 'png') {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                    imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
                }
                
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }
            
            // Sauvegarder l'image optimisée
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, $destPath, 85); // Qualité 85%
                    break;
                case 'png':
                    imagepng($image, $destPath, 9); // Compression max
                    break;
                case 'gif':
                    imagegif($image, $destPath);
                    break;
                case 'webp':
                    imagewebp($image, $destPath, 85);
                    break;
            }
            
            imagedestroy($image);
        } catch (Exception $e) {
            throw new Exception('Failed to process image: ' . $e->getMessage());
        }
    }

    /**
     * Générer un nom de fichier unique
     */
    private function generateFileName(string $ext): string
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return "avatar_{$timestamp}_{$random}.{$ext}";
    }

    /**
     * Supprimer un avatar
     */
    public function delete(string $avatarUrl): bool
    {
        try {
            $filePath = ltrim($avatarUrl, '/');
            
            if (file_exists($filePath)) {
                return unlink($filePath);
            }
            
            return false;
        } catch (Exception $e) {
            throw new Exception('Failed to delete avatar: ' . $e->getMessage());
        }
    }
}
