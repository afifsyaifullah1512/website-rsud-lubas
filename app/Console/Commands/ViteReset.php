<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Hapus `public/hot` sehingga `@vite()` directive berhenti merujuk ke
 * Vite dev server (yang mungkin sudah mati) dan kembali memakai
 * asset hasil `npm run build` di `public/build/manifest.json`.
 *
 * Dipanggil bila Anda meng-`Ctrl+C` `npm run dev` tetapi flag file
 * tidak terhapus, atau setelah crash tab terminal.
 */
class ViteReset extends Command
{
    protected $signature = 'vite:reset {--clear-views : juga hapus compiled views}';

    protected $description = 'Hapus public/hot agar @vite() pakai asset build (perbaikan setelah Vite dev server mati).';

    public function handle(): int
    {
        $hot = public_path('hot');
        if (is_file($hot)) {
            unlink($hot);
            $this->info('public/hot terhapus.');
        } else {
            $this->info('public/hot tidak ada — sudah bersih.');
        }

        if ($this->option('clear-views')) {
            $this->call('view:clear');
        }

        return self::SUCCESS;
    }
}
