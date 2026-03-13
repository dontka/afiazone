<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\AvatarUploadService;
use App\Exceptions\ValidationException;

class UserController extends BaseController
{
    private AvatarUploadService $avatarService;
class UserController extends BaseController
{
    private AvatarUploadService $avatarService;

    public function __construct()
    {
        parent::__construct();
        $this->avatarService = new AvatarUploadService();
    }

    /**
     * GET /api/users/{id}
     * Retrieve user profile information
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $user = User::find($id);
        if (!$user) {
            $this->errorResponse('User not found', 404);
            return;
        }

        // Permission check: owner or admin
        if ((int) $this->authUser->id !== $id && !$this->authUser->hasRole('admin')) {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        $profile = UserProfile::findBy('user_id', (string) $id);

        $this->jsonResponse([
            'user' => $user->toArray(),
            'profile' => $profile?->toArray() ?? [],
        ]);
    }

    public function profile(): void
    {
        $this->requireAuth();
        $user = $this->authUser;
        $profile = UserProfile::find($user->id);

        $this->jsonResponse([
            'user' => $user->toArray(),
            'profile' => $profile?->toArray() ?? [],
        ]);
    }

    /**
     * PUT /api/users/{id}
     * Update user profile (owner or admin only)
     */
    public function update(int $id): void
    {
        $this->requireAuth();

        $user = User::find($id);
        if (!$user) {
            $this->errorResponse('User not found', 404);
            return;
        }

        // Permission check: owner or admin
        if ((int) $this->authUser->id !== $id && !$this->authUser->hasRole('admin')) {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        $data = $this->getData();

        // Update User table fields
        $userFields = array_intersect_key($data, array_flip(['first_name', 'last_name', 'phone']));
        if (!empty($userFields)) {
            $user->update($userFields);
        }

        // Update UserProfile table fields
        $profileFields = array_intersect_key($data, array_flip([
            'date_of_birth', 'gender', 'address_line_1', 'address_line_2',
            'city', 'state', 'postal_code', 'country', 'bio', 'company_name', 'preferred_locale',
        ]));
        if (!empty($profileFields)) {
            $profile = UserProfile::findBy('user_id', (string) $id);
            if ($profile) {
                $profile->update($profileFields);
            } else {
                $profileFields['user_id'] = $id;
                UserProfile::create($profileFields);
            }
        }

        $this->jsonResponse([
            'message' => 'Profile updated',
            'user' => $user->toArray(),
        ]);
    }

    public function updateProfile(): void
    {
        $this->requireAuth();
        $data = $this->getData();
        $user = $this->authUser;

        $userFields = array_intersect_key($data, array_flip(['first_name', 'last_name', 'phone']));
        if (!empty($userFields)) {
            $user->update($userFields);
        }

        $profileFields = array_intersect_key($data, array_flip([
            'date_of_birth', 'gender', 'address_line_1', 'address_line_2',
            'city', 'state', 'postal_code', 'country',
        ]));
        if (!empty($profileFields)) {
            $profile = UserProfile::find($user->id);
            if ($profile) {
                $profile->update($profileFields);
            } else {
                $profileFields['user_id'] = $user->id;
                UserProfile::create($profileFields);
            }
        }

        $this->jsonResponse(['message' => 'Profile updated']);
    }

    public function changePassword(): void
    {
        $this->requireAuth();
        $current = (string) $this->getData('current_password');
        $new = (string) $this->getData('new_password');

        if (!password_verify($current, $this->authUser->password_hash ?? '')) {
            $this->errorResponse('Current password is incorrect', 400);
            return;
        }

        $this->authUser->update([
            'password_hash' => password_hash($new, PASSWORD_BCRYPT),
        ]);

        $this->jsonResponse(['message' => 'Password changed']);
    }

    /**
     * POST /api/me/avatar
     * Upload user avatar with automatic resizing
     */
    public function uploadAvatar(): void
    {
        $this->requireAuth();

        if (empty($_FILES['avatar'])) {
            $this->errorResponse('Avatar file is required', 400);
            return;
        }

        try {
            $result = $this->avatarService->uploadAvatar($this->authUserId(), $_FILES['avatar']);
            $this->jsonResponse($result, 200, 'Avatar uploaded successfully');
        } catch (ValidationException $e) {
            $this->errorResponse($e->getMessage(), 422, $e->getErrors());
        } catch (\Throwable $e) {
            $this->errorResponse($e->getMessage(), 400);
        }
    }
}
