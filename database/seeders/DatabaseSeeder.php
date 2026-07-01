<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Hanya seeder yang idempoten dan aman untuk semua environment yang
     * dipanggil di sini. Seeder demo (User, Polyclinic, dll) ditempatkan
     * pada seeder lain dan dipanggil eksplisit di environment dev.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);
    }
}
