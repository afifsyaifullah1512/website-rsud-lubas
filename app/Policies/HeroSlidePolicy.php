<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HeroSlide;
use App\Models\User;

/**
 * Policy untuk resource HeroSlide (hero slider beranda).
 *
 * Memetakan aksi ke permission Spatie dengan prefix `slider.<verb>`
 * (Requirements 36.5, 36.6). Permission tersedia di RoleSeeder
 * (`slider.view`, `slider.create`, `slider.update`, `slider.delete`).
 */
class HeroSlidePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('slider.view');
    }

    public function view(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('slider.view');
    }

    public function create(User $user): bool
    {
        return $user->can('slider.create');
    }

    public function update(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('slider.update');
    }

    public function delete(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('slider.delete');
    }

    public function restore(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('slider.update');
    }

    public function forceDelete(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('slider.delete');
    }
}
