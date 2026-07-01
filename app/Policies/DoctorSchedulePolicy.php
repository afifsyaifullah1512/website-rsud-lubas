<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DoctorSchedule;
use App\Models\User;

/**
 * Policy untuk resource DoctorSchedule.
 *
 * Memetakan aksi ke permission Spatie dengan prefix
 * `schedule.<verb>` (Requirements 15.2, 15.3, 15.4).
 */
class DoctorSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('schedule.view');
    }

    public function view(User $user, DoctorSchedule $schedule): bool
    {
        return $user->can('schedule.view');
    }

    public function create(User $user): bool
    {
        return $user->can('schedule.create');
    }

    public function update(User $user, DoctorSchedule $schedule): bool
    {
        return $user->can('schedule.update');
    }

    public function delete(User $user, DoctorSchedule $schedule): bool
    {
        return $user->can('schedule.delete');
    }

    public function restore(User $user, DoctorSchedule $schedule): bool
    {
        return $user->can('schedule.update');
    }

    public function forceDelete(User $user, DoctorSchedule $schedule): bool
    {
        return $user->can('schedule.delete');
    }
}
