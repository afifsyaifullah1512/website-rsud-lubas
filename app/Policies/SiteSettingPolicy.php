<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SiteSetting;
use App\Models\User;

/**
 * Policy untuk resource SiteSetting.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `setting.<verb>` (Requirements 15.2, 15.3, 15.4, 26.x).
 *
 * Catatan: SiteSetting menggunakan key string sebagai PK
 * (bukan auto-increment id). Method tetap menerima instance
 * model agar kompatibel dengan kontrak Laravel Gate/Policy.
 */
class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('setting.view');
    }

    public function view(User $user, SiteSetting $setting): bool
    {
        return $user->can('setting.view');
    }

    public function create(User $user): bool
    {
        return $user->can('setting.create');
    }

    public function update(User $user, SiteSetting $setting): bool
    {
        return $user->can('setting.update');
    }

    public function delete(User $user, SiteSetting $setting): bool
    {
        return $user->can('setting.delete');
    }
}
