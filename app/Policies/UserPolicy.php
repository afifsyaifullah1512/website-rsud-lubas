<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Policy untuk resource User.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `user.<verb>`. Sesuai Requirement 25.3, hanya role
 * `super-admin` yang memegang permission `user.*` di seeder
 * sehingga role lain otomatis menerima HTTP 403.
 *
 * Validates: Requirements 15.2, 15.3, 15.4, 25.3.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('user.view');
    }

    public function create(User $user): bool
    {
        return $user->can('user.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('user.update');
    }

    public function delete(User $user, User $model): bool
    {
        // Cegah user menghapus diri sendiri sebagai pengaman tambahan.
        if ($user->getKey() === $model->getKey()) {
            return false;
        }

        return $user->can('user.delete');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('user.update');
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($user->getKey() === $model->getKey()) {
            return false;
        }

        return $user->can('user.delete');
    }
}
