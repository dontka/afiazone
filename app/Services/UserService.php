<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Exception;

class UserService extends BaseService
{
    /**
     * Récupérer le profil utilisateur complet
     */
    public function getUserProfile(int $userId): ?array
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return null;
            }
            
            $profile = UserProfile::query()->where('user_id', $userId)->first();
            
            return [
                'id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'status' => $user->status,
                'email_verified_at' => $user->email_verified_at,
                'phone_verified_at' => $user->phone_verified_at,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
                'profile' => $profile ? [
                    'bio' => $profile->bio,
                    'avatar_url' => $profile->avatar_url,
                    'phone_number' => $profile->phone_number,
                    'country' => $profile->country,
                    'city' => $profile->city,
                    'address' => $profile->address,
                    'postal_code' => $profile->postal_code,
                    'company_name' => $profile->company_name,
                    'company_type' => $profile->company_type,
                ] : null,
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to get user profile: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour le profil utilisateur
     */
    public function updateProfile(int $userId, array $data): User
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Vérifier l'unicité de l'email si modifié
            if (isset($data['email']) && $data['email'] !== $user->email) {
                $existing = User::query()->where('email', $data['email'])->first();
                if ($existing && $existing->id !== $userId) {
                    throw new Exception('Email already exists');
                }
            }
            
            // Vérifier l'unicité du téléphone si modifié
            if (isset($data['phone']) && $data['phone'] !== $user->phone) {
                $existing = User::query()->where('phone', $data['phone'])->first();
                if ($existing && $existing->id !== $userId) {
                    throw new Exception('Phone already exists');
                }
            }
            
            // Mettre à jour les infos utilisateur
            $userFields = ['email', 'phone', 'first_name', 'last_name'];
            foreach ($userFields as $field) {
                if (isset($data[$field])) {
                    $user->{$field} = $data[$field];
                }
            }
            $user->save();
            
            // Mettre à jour ou créer le profil utilisateur
            $profile = UserProfile::query()->where('user_id', $userId)->first();
            if (!$profile) {
                $profile = new UserProfile();
                $profile->user_id = $userId;
            }
            
            $profileFields = ['bio', 'phone_number', 'country', 'city', 'address', 'postal_code', 'company_name', 'company_type'];
            foreach ($profileFields as $field) {
                if (isset($data[$field])) {
                    $profile->{$field} = $data[$field];
                }
            }
            $profile->save();
            
            return $user;
        } catch (Exception $e) {
            throw new Exception('Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Changer le mot de passe de l'utilisateur
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Vérifier l'ancien mot de passe
            if (!password_verify($oldPassword, $user->password_hash)) {
                throw new Exception('Old password is incorrect');
            }
            
            // Vérifier force du nouveau mot de passe
            if (strlen($newPassword) < 8) {
                throw new Exception('New password must be at least 8 characters');
            }
            
            // Mettre à jour le mot de passe
            $user->password_hash = password_hash($newPassword, PASSWORD_ARGON2ID);
            $user->save();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to change password: ' . $e->getMessage());
        }
    }

    /**
     * Définir l'avatar de l'utilisateur
     */
    public function setAvatar(int $userId, string $avatarUrl): bool
    {
        try {
            $profile = UserProfile::query()->where('user_id', $userId)->first();
            if (!$profile) {
                $profile = new UserProfile();
                $profile->user_id = $userId;
            }
            
            $profile->avatar_url = $avatarUrl;
            $profile->save();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to set avatar: ' . $e->getMessage());
        }
    }

    /**
     * Marquer l'email comme vérifié
     */
    public function verifyEmail(int $userId): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->status = 'active';
            $user->save();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to verify email: ' . $e->getMessage());
        }
    }

    /**
     * Marquer le téléphone comme vérifié
     */
    public function verifyPhone(int $userId): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            $user->phone_verified_at = date('Y-m-d H:i:s');
            $user->save();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to verify phone: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour le dernier login
     */
    public function updateLastLogin(int $userId): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            $user->last_login_at = date('Y-m-d H:i:s');
            $user->save();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to update last login: ' . $e->getMessage());
        }
    }

    /**
     * Bannir un utilisateur
     */
    public function banUser(int $userId, string $reason = ''): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            $user->status = 'banned';
            $user->save();
            
            // TODO: Enregistrer la raison dans la table d'audit
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to ban user: ' . $e->getMessage());
        }
    }

    /**
     * Débannir un utilisateur
     */
    public function unbanUser(int $userId): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            $user->status = 'active';
            $user->save();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to unban user: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un utilisateur (soft delete)
     */
    public function deleteUser(int $userId): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            $user->status = 'inactive';
            $user->save();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to delete user: ' . $e->getMessage());
        }
    }
}
