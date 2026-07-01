<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PpidDocument;
use App\Models\User;

/**
 * Policy untuk resource PpidDocument.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `ppid.<verb>` (Requirements 15.2, 15.3, 15.4, 23.x).
 */
class PpidDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ppid.view');
    }

    public function view(User $user, PpidDocument $document): bool
    {
        return $user->can('ppid.view');
    }

    public function create(User $user): bool
    {
        return $user->can('ppid.create');
    }

    public function update(User $user, PpidDocument $document): bool
    {
        return $user->can('ppid.update');
    }

    public function delete(User $user, PpidDocument $document): bool
    {
        return $user->can('ppid.delete');
    }

    public function restore(User $user, PpidDocument $document): bool
    {
        return $user->can('ppid.update');
    }

    public function forceDelete(User $user, PpidDocument $document): bool
    {
        return $user->can('ppid.delete');
    }
}
