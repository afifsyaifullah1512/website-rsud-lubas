<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tariff;
use App\Models\User;

/**
 * Policy untuk resource Tariff.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `tariff.<verb>` (Requirements 15.2, 15.3, 15.4, 21.x).
 */
class TariffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tariff.view');
    }

    public function view(User $user, Tariff $tariff): bool
    {
        return $user->can('tariff.view');
    }

    public function create(User $user): bool
    {
        return $user->can('tariff.create');
    }

    public function update(User $user, Tariff $tariff): bool
    {
        return $user->can('tariff.update');
    }

    public function delete(User $user, Tariff $tariff): bool
    {
        return $user->can('tariff.delete');
    }

    public function restore(User $user, Tariff $tariff): bool
    {
        return $user->can('tariff.update');
    }

    public function forceDelete(User $user, Tariff $tariff): bool
    {
        return $user->can('tariff.delete');
    }
}
