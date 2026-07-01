<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

/**
 * Policy untuk resource Page (halaman profil/konten statis).
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `page.<verb>` (Requirements 15.2, 15.3, 15.4, 16.x).
 */
class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('page.view');
    }

    public function view(User $user, Page $page): bool
    {
        return $user->can('page.view');
    }

    public function create(User $user): bool
    {
        return $user->can('page.create');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->can('page.update');
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->can('page.delete');
    }

    public function restore(User $user, Page $page): bool
    {
        return $user->can('page.update');
    }

    public function forceDelete(User $user, Page $page): bool
    {
        return $user->can('page.delete');
    }
}
