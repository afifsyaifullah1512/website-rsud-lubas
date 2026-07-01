<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Polyclinic;
use App\Models\User;

/**
 * Policy untuk resource Polyclinic.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `polyclinic.<verb>` (Requirements 15.2, 15.3, 15.4, 17.x).
 */
class PolyclinicPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('polyclinic.view');
    }

    public function view(User $user, Polyclinic $polyclinic): bool
    {
        return $user->can('polyclinic.view');
    }

    public function create(User $user): bool
    {
        return $user->can('polyclinic.create');
    }

    public function update(User $user, Polyclinic $polyclinic): bool
    {
        return $user->can('polyclinic.update');
    }

    public function delete(User $user, Polyclinic $polyclinic): bool
    {
        return $user->can('polyclinic.delete');
    }

    public function restore(User $user, Polyclinic $polyclinic): bool
    {
        return $user->can('polyclinic.update');
    }

    public function forceDelete(User $user, Polyclinic $polyclinic): bool
    {
        return $user->can('polyclinic.delete');
    }
}
