<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * Sajikan `public/sitemap.xml` (Requirement 27.1–27.2).
 *
 * Sitemap di-generate oleh command `sitemap:generate` (Task 9.6).
 * Controller ini hanya melayani file yang sudah disimpan di
 * `public/sitemap.xml`. Bila file belum ada, fallback ke sitemap
 * minimal yang menyertakan halaman statis.
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $path = public_path('sitemap.xml');
        $content = is_file($path)
            ? (string) file_get_contents($path)
            : $this->fallback();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function fallback(): string
    {
        $urls = [
            route('home'),
            route('layanan.index'),
            route('jadwal'),
            route('berita.index'),
            route('ppid.index'),
            route('pengaduan.create'),
            route('kontak'),
            route('faq'),
        ];

        $entries = '';
        foreach ($urls as $url) {
            $entries .= '  <url><loc>'.htmlspecialchars($url, ENT_XML1)."</loc></url>\n";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
$entries</urlset>
XML;
    }
}
