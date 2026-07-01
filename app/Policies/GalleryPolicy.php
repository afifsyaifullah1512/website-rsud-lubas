<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

/**
 * Policy untuk resource Gallery.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `gallery.<verb>` (Requirements 15.2, 15.3, 15.4, 20.x).
 */
class GalleryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gallery.view');
    }

    public function view(User $user, Gallery $gallery): bool
    {
        return $user->can('gallery.view');
    }

    public function create(User $user): bool
    {
        return $user->can('gallery.create');
    }

    public function update(User $user, Gallery $gallery): bool
    {
        return $user->can('gallery.update');
    }

    public function delete(User $user, Gallery $gallery): bool
    {
        return $user->can('gallery.delete');
    }

    public function restore(User $user, Gallery $gallery): bool
    {
        return $user->can('gallery.update');
    }

    public function forceDelete(User $user, Gallery $gallery): bool
    {
        return $user->can('gallery.delete');
    }
}
