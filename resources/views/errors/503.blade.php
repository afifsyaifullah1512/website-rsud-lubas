{{--
    Halaman maintenance 503 ber-branding RSUD (Requirement 28.2).
    Ditampilkan ketika terjadi QueryException / database tidak tersedia saat
    melayani halaman publik inti. KARENA ITU halaman ini WAJIB self-contained:
    tidak boleh @extends('layouts.public') maupun memanggil SiteSettingService,
    navbar, atau footer yang mengambil data dari database — kalau tidak, render
    503 akan memicu QueryException kedua. Semua gaya di-inline.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>503 - Sedang Dalam Pemeliharaan | RSUD</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter','Segoe UI',system-ui,-apple-system,sans-serif;background:#f8fafc;color:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;line-height:1.6}
        .card{max-width:32rem;width:100%;text-align:center}
        .badge{display:inline-flex;align-items:center;gap:.5rem;font-size:.8rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#047857;background:#ecfdf5;border:1px solid #a7f3d0;padding:.35rem .75rem;border-radius:9999px}
        .code{font-size:clamp(3.5rem,12vw,6rem);font-weight:800;color:#047857;line-height:1;margin:1.25rem 0 .5rem}
        h1{font-size:clamp(1.25rem,4vw,1.75rem);font-weight:700;color:#0f172a;margin-bottom:.75rem}
        p{color:#475569;font-size:1rem;max-width:26rem;margin:0 auto 1.75rem}
        .btn{display:inline-flex;align-items:center;gap:.5rem;background:#047857;color:#fff;font-weight:600;font-size:.95rem;padding:.7rem 1.4rem;border-radius:.5rem;text-decoration:none;transition:background .15s ease}
        .btn:hover,.btn:focus{background:#065f46}
        .brand{margin-top:2.5rem;font-size:.85rem;color:#94a3b8;font-weight:600;letter-spacing:.04em}
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">RSUD</span>
        <div class="code">503</div>
        <h1>Sedang dalam pemeliharaan</h1>
        <p>Mohon maaf, layanan kami sedang mengalami gangguan sementara dan tengah dalam pemeliharaan. Silakan coba beberapa saat lagi. Terima kasih atas pengertian Anda.</p>
        <a class="btn" href="/">&larr; Kembali ke Beranda</a>
        <div class="brand">Rumah Sakit Umum Daerah</div>
    </main>
</body>
</html>
