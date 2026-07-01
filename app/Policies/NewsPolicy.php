<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\News;
use App\Models\User;

/**
 * Policy untuk resource News.
 *
 * Memetakan aksi Filament/Controller ke permission Spatie
 * dengan format `news.<verb>`. Verb `publish` adalah permission
 * tambahan di luar CRUD standar (Requirement 19.3).
 *
 * Validates: Requirements 15.2, 15.3, 15.4, 19.3.
 */
class NewsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('news.view');
    }

    public function view(User $user, News $news): bool
    {
        return $user->can('news.view');
    }

    public function create(User $user): bool
    {
        return $user->can('news.create');
    }

    public function update(User $user, News $news): bool
    {
        return $user->can('news.update');
    }

    public function delete(User $user, News $news): bool
    {
        return $user->can('news.delete');
    }

    public function restore(User $user, News $news): bool
    {
        return $user->can('news.update');
    }

    public function forceDelete(User $user, News $news): bool
    {
        return $user->can('news.delete');
    }

    /**
     * Aksi publish News dilindungi permission `news.publish`
     * (Requirement 19.3 — non-publisher menerima HTTP 403).
     */
    public function publish(User $user, News $news): bool
    {
        return $user->can('news.publish');
    }
}
