<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder peran (role) dan permission Spatie sesuai matriks RBAC RSUD.
 *
 * Memenuhi Requirements 15.1, 15.2, 15.4. Idempoten sehingga aman
 * dipanggil berulang kali oleh `migrate:fresh --seed`, deployment
 * pipeline, atau saat menyelaraskan permission baru tanpa perlu
 * `truncate`.
 *
 * Daftar resource & verb:
 *   - CRUD standar (`view`, `create`, `update`, `delete`) untuk:
 *     news, doctor, schedule, polyclinic, service, tariff, gallery,
 *     slider, vacancy (job_vacancy), ppid (ppid_document), page, faq,
 *     news_category, ppid_category, complaint, user, role, setting.
 *   - Verb tambahan:
 *       * `news.publish`        — Requirement 19.3.
 *       * `complaint.respond`   — Requirement 24.4.
 *
 * Matriks Role → Permission (selaras design.md):
 *   - **super-admin**       : seluruh permission (juga di-allow oleh
 *                             Gate::before di AuthServiceProvider).
 *   - **admin**             : seluruh kecuali manajemen `user` & `role`.
 *   - **editor**            : full CRUD news + news.publish, faq,
 *                             news_category, gallery, page (view+update),
 *                             slider.view, dapat melihat resource lain
 *                             (read-only).
 *   - **humas**             : full CRUD gallery, hero slider, news (tanpa
 *                             delete & publish), faq, page (view+update),
 *                             news_category (view+create+update).
 *   - **petugas-pengaduan** : complaint (view, update, respond) + view
 *                             pada resource publik (news, schedule).
 *   - **viewer**            : read-only seluruh resource.
 */
class RoleSeeder extends Seeder
{
    /** Verb CRUD standar yang diturunkan ke setiap resource. */
    private const CRUD = ['view', 'create', 'update', 'delete'];

    /** Resource dengan CRUD standar (tanpa verb tambahan). */
    private const RESOURCES_CRUD = [
        'doctor',
        'schedule',
        'polyclinic',
        'service',
        'tariff',
        'gallery',
        'slider',
        'vacancy',
        'ppid',
        'page',
        'faq',
        'news_category',
        'ppid_category',
        'user',
        'role',
        'setting',
        'nav_item',
    ];

    /** Verb ekstra di luar CRUD. */
    private const EXTRA_VERBS = [
        'news.publish',
        'complaint.respond',
    ];

    public function run(): void
    {
        // Pastikan tabel permission tersedia. Saat menjalankan
        // `migrate:fresh --seed` di lingkungan baru, migration sudah
        // membuat tabel ini; pengecekan tambahan menjaga seeder agar
        // tidak meledak ketika dijalankan terhadap database parsial.
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        $guard = config('auth.defaults.guard', 'web');

        // 1) Buat semua permission idempoten.
        $permissions = $this->buildPermissionList();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        // 2) Definisi matriks role → permissions.
        $matrix = $this->roleMatrix($permissions);

        foreach ($matrix as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->syncPermissions($rolePermissions);
        }

        // 3) Bersihkan cache permission Spatie.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Bangun daftar lengkap permission yang dipakai sistem.
     *
     * @return array<int,string>
     */
    private function buildPermissionList(): array
    {
        $permissions = [];

        // News: CRUD + publish.
        foreach (self::CRUD as $verb) {
            $permissions[] = 'news.'.$verb;
        }
        // Complaint: CRUD + respond.
        foreach (self::CRUD as $verb) {
            $permissions[] = 'complaint.'.$verb;
        }
        foreach (self::EXTRA_VERBS as $verb) {
            $permissions[] = $verb;
        }

        // Resource CRUD lainnya.
        foreach (self::RESOURCES_CRUD as $resource) {
            foreach (self::CRUD as $verb) {
                $permissions[] = $resource.'.'.$verb;
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * Susun mapping role → kumpulan permission name.
     *
     * @param  array<int,string>  $allPermissions
     * @return array<string,array<int,string>>
     */
    private function roleMatrix(array $allPermissions): array
    {
        // super-admin : semua permission.
        $superAdmin = $allPermissions;

        // admin : semua kecuali user.* dan role.*
        $admin = array_values(array_filter(
            $allPermissions,
            static fn (string $p): bool => ! str_starts_with($p, 'user.')
                && ! str_starts_with($p, 'role.')
        ));

        // viewer : seluruh permission view-* (read-only)
        $viewer = array_values(array_filter(
            $allPermissions,
            static fn (string $p): bool => str_ends_with($p, '.view')
        ));

        // editor : news.* (full + publish), news_category.*, gallery.*,
        //          faq.*, page.{view,update}, schedule.view, doctor.view,
        //          polyclinic.view, service.view, tariff.view.
        $editor = [
            'news.view', 'news.create', 'news.update', 'news.delete', 'news.publish',
            'news_category.view', 'news_category.create', 'news_category.update', 'news_category.delete',
            'gallery.view', 'gallery.create', 'gallery.update', 'gallery.delete',
            'faq.view', 'faq.create', 'faq.update', 'faq.delete',
            'page.view', 'page.update',
            'slider.view',
            'doctor.view', 'schedule.view', 'polyclinic.view', 'service.view', 'tariff.view',
        ];

        // humas : gallery.*, news.{view,create,update}, faq.*, page.{view,update},
        //         news_category.{view,create,update}.
        $humas = [
            'gallery.view', 'gallery.create', 'gallery.update', 'gallery.delete',
            'slider.view', 'slider.create', 'slider.update', 'slider.delete',
            'news.view', 'news.create', 'news.update',
            'news_category.view', 'news_category.create', 'news_category.update',
            'faq.view', 'faq.create', 'faq.update', 'faq.delete',
            'page.view', 'page.update',
        ];

        // petugas-pengaduan : complaint.{view,update,respond}, schedule.view, news.view.
        $petugasPengaduan = [
            'complaint.view', 'complaint.update', 'complaint.respond',
            'schedule.view', 'news.view',
        ];

        return [
            'super-admin'        => $superAdmin,
            'admin'              => $admin,
            'editor'             => $editor,
            'humas'              => $humas,
            'petugas-pengaduan'  => $petugasPengaduan,
            'viewer'             => $viewer,
        ];
    }
}
