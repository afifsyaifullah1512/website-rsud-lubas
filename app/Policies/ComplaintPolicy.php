<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

/**
 * Policy untuk resource Complaint.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `complaint.<verb>`. Permission ekstra:
 *   - `complaint.respond` untuk aksi `changeStatus` (Requirement 24.4).
 *
 * Method `viewBody()` mengimplementasikan Requirement 15.5:
 * hanya role `super-admin` dan `petugas-pengaduan` yang boleh
 * melihat body pengaduan; role lain tidak.
 *
 * Validates: Requirements 15.2, 15.3, 15.4, 15.5, 24.4.
 */
class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('complaint.view');
    }

    public function view(User $user, Complaint $complaint): bool
    {
        return $user->can('complaint.view');
    }

    public function create(User $user): bool
    {
        return $user->can('complaint.create');
    }

    public function update(User $user, Complaint $complaint): bool
    {
        return $user->can('complaint.update');
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return $user->can('complaint.delete');
    }

    public function restore(User $user, Complaint $complaint): bool
    {
        return $user->can('complaint.update');
    }

    public function forceDelete(User $user, Complaint $complaint): bool
    {
        return $user->can('complaint.delete');
    }

    /**
     * Permission khusus untuk merespons / mengubah status Complaint
     * (NEW → IN_REVIEW → RESPONDED → CLOSED). Lihat Requirement 24.4.
     */
    public function respond(User $user, Complaint $complaint): bool
    {
        return $user->can('complaint.respond');
    }

    /**
     * Otorisasi untuk melihat body (`message`) Complaint.
     *
     * Requirement 15.5: hanya `super-admin` dan
     * `petugas-pengaduan` yang boleh melihat body pengaduan.
     */
    public function viewBody(User $user, ?Complaint $complaint = null): bool
    {
        return $user->hasAnyRole(['super-admin', 'petugas-pengaduan']);
    }
}
