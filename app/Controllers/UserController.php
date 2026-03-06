<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\UserProfile;

class UserController extends BaseController
{
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
}
