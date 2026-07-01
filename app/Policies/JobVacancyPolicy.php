<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JobVacancy;
use App\Models\User;

/**
 * Policy untuk resource JobVacancy.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `vacancy.<verb>` (Requirements 15.2, 15.3, 15.4, 22.x).
 */
class JobVacancyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vacancy.view');
    }

    public function view(User $user, JobVacancy $vacancy): bool
    {
        return $user->can('vacancy.view');
    }

    public function create(User $user): bool
    {
        return $user->can('vacancy.create');
    }

    public function update(User $user, JobVacancy $vacancy): bool
    {
        return $user->can('vacancy.update');
    }

    public function delete(User $user, JobVacancy $vacancy): bool
    {
        return $user->can('vacancy.delete');
    }

    public function restore(User $user, JobVacancy $vacancy): bool
    {
        return $user->can('vacancy.update');
    }

    public function forceDelete(User $user, JobVacancy $vacancy): bool
    {
        return $user->can('vacancy.delete');
    }
}
