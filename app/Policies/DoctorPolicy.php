<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Doctor;
use App\Models\User;

/**
 * Policy untuk resource Doctor.
 *
 * Memetakan aksi Filament/Controller ke permission Spatie
 * dengan prefix `doctor.<verb>` (Requirements 15.2, 15.3, 15.4).
 */
class DoctorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('doctor.view');
    }

    public function view(User $user, Doctor $doctor): bool
    {
        return $user->can('doctor.view');
    }

    public function create(User $user): bool
    {
        return $user->can('doctor.create');
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->can('doctor.update');
    }

    public function delete(User $user, Doctor $doctor): bool
    {
        return $user->can('doctor.delete');
    }

    public function restore(User $user, Doctor $doctor): bool
    {
        return $user->can('doctor.update');
    }

    public function forceDelete(User $user, Doctor $doctor): bool
    {
        return $user->can('doctor.delete');
    }
}
