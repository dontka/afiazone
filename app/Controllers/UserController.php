<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\UserService;
use App\Services\AvatarUploadService;
use Exception;

class UserController extends BaseController
{
    private UserService $userService;
    private AvatarUploadService $avatarUploadService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
        $this->avatarUploadService = new AvatarUploadService();
    }

    /**
     * Récupérer mon profil
     * GET /api/me
     */
    public function profile(): void
    {
        try {
            $this->requireAuth();
            
            $userId = $this->getCurrentUserId();
            $userProfile = $this->userService->getUserProfile($userId);
            
            if (!$userProfile) {
                $this->error('User not found', 404);
                return;
            }
            
            $this->success($userProfile);
        } catch (Exception $e) {
            $this->error('Failed to fetch profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour mon profil
     * PUT /api/me
     */
    public function updateProfile(): void
    {
        try {
            $this->requireAuth();
            
            $userId = $this->getCurrentUserId();
            $data = $this->getJsonBody([
                'first_name' => 'nullable|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'email' => 'nullable|email|unique:users',
                'phone' => 'nullable|string|unique:users',
                'bio' => 'nullable|string',
                'country' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:512',
                'postal_code' => 'nullable|string|max:20',
                'company_name' => 'nullable|string|max:255',
                'company_type' => 'nullable|string',
            ]);
            
            if ($data === null) {
                return;
            }
            
            $updated = $this->userService->updateProfile($userId, $data);
            
            $this->success([
                'id' => $updated->id,
                'email' => $updated->email,
                'first_name' => $updated->first_name,
                'last_name' => $updated->last_name,
                'phone' => $updated->phone,
                'updated_at' => $updated->updated_at,
            ], 'Profile updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Changer le mot de passe
     * POST /api/me/password
     */
    public function changePassword(): void
    {
        try {
            $this->requireAuth();
            
            $userId = $this->getCurrentUserId();
            $data = $this->getJsonBody([
                'old_password' => 'required|string|min:8',
                'new_password' => 'required|string|min:8',
                'confirm_password' => 'required|string|min:8',
            ]);
            
            if ($data === null) {
                return;
            }
            
            // Vérifier que les nouveaux mots de passe correspondent
            if ($data['new_password'] !== $data['confirm_password']) {
                $this->error('New passwords do not match', 400);
                return;
            }
            
            $this->userService->changePassword(
                $userId,
                $data['old_password'],
                $data['new_password']
            );
            
            $this->success([], 'Password changed successfully');
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Upload avatar
     * POST /api/me/avatar
     */
    public function uploadAvatar(): void
    {
        try {
            $this->requireAuth();
            
            if (!isset($_FILES['avatar'])) {
                $this->error('Avatar file is required', 400);
                return;
            }
            
            $file = $_FILES['avatar'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->error('File upload error: ' . $file['error'], 400);
                return;
            }
            
            $userId = $this->getCurrentUserId();
            $avatarUrl = $this->avatarUploadService->upload($file['tmp_name']);
            
            // Sauvegarder l'URL dans le profil utilisateur
            $this->userService->setAvatar($userId, $avatarUrl);
            
            $this->success([
                'avatar_url' => $avatarUrl,
            ], 'Avatar uploaded successfully', 201);
        } catch (Exception $e) {
            $this->error('Failed to upload avatar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher le profil d'un autre utilisateur
     * GET /api/users/{id}
     */
    public function show(): void
    {
        try {
            $this->requireAuth();
            
            $id = $this->getPathParam('id');
            if (!$id) {
                $this->error('User ID is required', 400);
                return;
            }
            
            $userProfile = $this->userService->getUserProfile((int)$id);
            
            if (!$userProfile) {
                $this->error('User not found', 404);
                return;
            }
            
            $this->success($userProfile);
        } catch (Exception $e) {
            $this->error('Failed to fetch user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour un utilisateur (admin seulement)
     * PUT /api/users/{id}
     */
    public function update(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin']);
            
            $id = $this->getPathParam('id');
            if (!$id) {
                $this->error('User ID is required', 400);
                return;
            }
            
            $data = $this->getJsonBody([
                'first_name' => 'nullable|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'status' => 'nullable|in:active,inactive,banned,pending_verification',
            ]);
            
            if ($data === null) {
                return;
            }
            
            $updated = $this->userService->updateProfile((int)$id, $data);
            
            $this->success([
                'id' => $updated->id,
                'email' => $updated->email,
                'first_name' => $updated->first_name,
                'last_name' => $updated->last_name,
                'status' => $updated->status,
                'updated_at' => $updated->updated_at,
            ], 'User updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update user: ' . $e->getMessage(), 500);
        }
    }
}
