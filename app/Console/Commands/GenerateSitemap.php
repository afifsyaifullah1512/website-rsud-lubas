<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Doctor;
use App\Models\News;
use App\Models\Polyclinic;
use App\Support\Enums\NewsStatus;
use Illuminate\Console\Command;

/**
 * Generate `public/sitemap.xml` (Requirement 27.1).
 *
 * Implementasi sederhana berbasis array URL — tidak bergantung pada
 * `spatie/laravel-sitemap` agar tidak menambah ketergantungan saat
 * runtime testing/dev. Sinkron dengan format sitemap.org 0.9.
 */
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate public/sitemap.xml untuk SEO.';

    public function handle(): int
    {
        $urls = collect();

        $static = [
            'home', 'profil.sejarah', 'profil.visi-misi', 'profil.struktur', 'profil.direktur',
            'layanan.index', 'jadwal', 'berita.index', 'galeri.index',
            'pendaftaran', 'ppid.index', 'pengaduan.create', 'kontak', 'faq',
        ];
        foreach ($static as $name) {
            try {
                $urls->push(['loc' => route($name), 'lastmod' => now()->toAtomString()]);
            } catch (\Throwable) {
                // route belum terdaftar — skip
            }
        }

        News::query()->where('status', NewsStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->get(['slug', 'updated_at'])
            ->each(function (News $n) use ($urls): void {
                $urls->push([
                    'loc' => route('berita.show', $n->slug),
                    'lastmod' => optional($n->updated_at)->toAtomString(),
                ]);
            });

        Doctor::query()->where('is_active', true)->get(['slug', 'updated_at'])
            ->each(fn (Doctor $d) => $urls->push([
                'loc' => route('dokter.show', $d->slug),
                'lastmod' => optional($d->updated_at)->toAtomString(),
            ]));

        Polyclinic::query()->where('is_active', true)->get(['updated_at']);

        $xml = $this->buildXml($urls->all());
        file_put_contents(public_path('sitemap.xml'), $xml);

        $this->info('Sitemap dengan '.count($urls).' URL telah ditulis.');

        return self::SUCCESS;
    }

    /**
     * @param  array<int,array{loc:string,lastmod:?string}>  $urls
     */
    private function buildXml(array $urls): string
    {
        $items = '';
        foreach ($urls as $u) {
            $loc = htmlspecialchars($u['loc'], ENT_XML1);
            $items .= "  <url>\n    <loc>{$loc}</loc>\n";
            if (! empty($u['lastmod'])) {
                $items .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            }
            $items .= "  </url>\n";
        }
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
$items</urlset>
XML;
    }
}
