{{--
    Halaman error 422 ber-branding RSUD (Requirement 28.4).
    Ditampilkan untuk respons 422 langsung (mis. unggahan file melebihi batas
    ukuran atau MIME tidak valid). Validasi form publik biasa (Req 28.3)
    tetap memakai redirect-back bawaan Laravel, bukan halaman ini.
    SENGAJA self-contained agar tidak bergantung pada layout/DB.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>422 - Data Tidak Dapat Diproses | RSUD</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter','Segoe UI',system-ui,-apple-system,sans-serif;background:#f8fafc;color:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;line-height:1.6}
        .card{max-width:32rem;width:100%;text-align:center}
        .badge{display:inline-flex;align-items:center;gap:.5rem;font-size:.8rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#b45309;background:#fffbeb;border:1px solid #fcd34d;padding:.35rem .75rem;border-radius:9999px}
        .code{font-size:clamp(3.5rem,12vw,6rem);font-weight:800;color:#d97706;line-height:1;margin:1.25rem 0 .5rem}
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
        <div class="code">422</div>
        <h1>Data tidak dapat diproses</h1>
        <p>Permintaan Anda tidak dapat diproses. Pastikan data yang dikirim sudah benar, atau berkas yang diunggah sesuai jenis dan ukuran yang diizinkan, lalu coba kembali.</p>
        <a class="btn" href="/">&larr; Kembali ke Beranda</a>
        <div class="brand">Rumah Sakit Umum Daerah</div>
    </main>
</body>
</html>
