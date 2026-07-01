<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NavItem;
use App\Models\User;

/**
 * Policy untuk resource NavItem (menu navigasi publik).
 *
 * Dipetakan ke permission Spatie `nav_item.*`. Hanya admin/super-admin
 * yang berwenang mengubah menu publik.
 */
class NavItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('nav_item.view');
    }

    public function view(User $user, NavItem $item): bool
    {
        return $user->can('nav_item.view');
    }

    public function create(User $user): bool
    {
        return $user->can('nav_item.create');
    }

    public function update(User $user, NavItem $item): bool
    {
        return $user->can('nav_item.update');
    }

    public function delete(User $user, NavItem $item): bool
    {
        return $user->can('nav_item.delete');
    }
}
