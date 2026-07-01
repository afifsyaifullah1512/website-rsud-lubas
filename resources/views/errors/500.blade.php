{{--
    Halaman error 500 ber-branding RSUD (kelengkapan Requirement 28).
    Ditampilkan untuk error server tak terduga. SENGAJA self-contained: tidak
    @extends('layouts.public') maupun memanggil SiteSettingService/navbar/footer
    yang mengambil data dari database — halaman error harus tetap tampil meski
    komponen lain bermasalah. Semua gaya di-inline.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>500 - Terjadi Kesalahan | RSUD</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter','Segoe UI',system-ui,-apple-system,sans-serif;background:#f8fafc;color:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;line-height:1.6}
        .card{max-width:32rem;width:100%;text-align:center}
        .badge{display:inline-flex;align-items:center;gap:.5rem;font-size:.8rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;padding:.35rem .75rem;border-radius:9999px}
        .code{font-size:clamp(3.5rem,12vw,6rem);font-weight:800;color:#dc2626;line-height:1;margin:1.25rem 0 .5rem}
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
        <div class="code">500</div>
        <h1>Terjadi kesalahan</h1>
        <p>Mohon maaf, terjadi kesalahan tak terduga pada sistem kami. Tim kami telah diberi tahu. Silakan coba beberapa saat lagi atau kembali ke beranda.</p>
        <a class="btn" href="/">&larr; Kembali ke Beranda</a>
        <div class="brand">Rumah Sakit Umum Daerah</div>
    </main>
</body>
</html>
