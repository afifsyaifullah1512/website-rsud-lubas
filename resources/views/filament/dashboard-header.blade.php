<div class="mb-6">
    <div class="flex items-center gap-3">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-gray-100">{{ $name }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Panel Administrasi &mdash; Selamat datang kembali, {{ auth()->user()?->name ?? 'Admin' }} 👋</p>
        </div>
    </div>

    <details class="mt-4 group rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-3 text-sm font-semibold text-amber-800 dark:text-amber-300">
            <span class="flex items-center gap-2">💡 Panduan Singkat Penggunaan</span>
            <span class="text-xs font-normal text-amber-700/70 group-open:hidden">klik untuk buka</span>
        </summary>
        <div class="border-t border-amber-200/70 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 dark:border-amber-500/20">
            <ul class="list-disc space-y-1.5 pl-5">
                <li><strong>Pengaturan Situs</strong> (grup Pengaturan): atur nama RS, logo, kontak, nomor IGD, URL pendaftaran, <strong>warna tema</strong>, dan seluruh teks/section beranda (Tentang, Fasilitas, Statistik, Aksi Cepat, Keunggulan) — termasuk menyalakan/mematikan section.</li>
                <li><strong>Menu navigasi</strong> diatur di <em>Menu</em> (grup Pengaturan): tambah/sembunyikan item, ubah urutan.</li>
                <li><strong>Dokter &amp; Jadwal</strong>: foto dokter sebaiknya <strong>persegi (1:1)</strong>, gunakan editor crop saat unggah. Jadwal bentrok otomatis ditolak.</li>
                <li><strong>Berita/Galeri/Layanan</strong> (grup Konten/Layanan): gambar diunggah lewat tombol unggah; biarkan kosong untuk pakai gambar default.</li>
                <li><strong>Pengaduan</strong> masuk dari publik. Ubah status lewat tombol aksi; isi pengaduan hanya terlihat oleh petugas berwenang.</li>
                <li>Setelah mengubah data, perubahan langsung tampil di situs (tidak perlu aksi tambahan).</li>
            </ul>
        </div>
    </details>
</div>
