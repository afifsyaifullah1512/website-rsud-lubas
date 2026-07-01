<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

/**
 * Policy untuk resource Service.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `service.<verb>` (Requirements 15.2, 15.3, 15.4, 17.2).
 */
class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('service.view');
    }

    public function view(User $user, Service $service): bool
    {
        return $user->can('service.view');
    }

    public function create(User $user): bool
    {
        return $user->can('service.create');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->can('service.update');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->can('service.delete');
    }

    public function restore(User $user, Service $service): bool
    {
        return $user->can('service.update');
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $user->can('service.delete');
    }
}
