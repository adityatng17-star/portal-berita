<?php
require __DIR__ . '/../includes/init.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/berita.php');
}
csrf_cek();

$p      = pengguna_sekarang();
$berita = ambil_satu('SELECT * FROM berita WHERE id = ?', [(int)($_POST['id'] ?? 0)]);

if (!$berita) {
    flash_set('galat', 'Berita tidak ditemukan.');
    redirect('admin/berita.php');
}
if (!adalah_admin() && (int)$berita['pengguna_id'] !== (int)$p['id']) {
    halaman_galat(403, 'Anda hanya bisa menghapus berita milik sendiri.');
}

// Komentar dihapus lebih dulu (di MySQL sebenarnya sudah otomatis lewat ON DELETE CASCADE)
jalankan('DELETE FROM komentar WHERE berita_id = ?', [$berita['id']]);
jalankan('DELETE FROM berita WHERE id = ?', [$berita['id']]);
hapus_gambar($berita['gambar']);

flash_set('sukses', 'Berita "' . ringkas($berita['judul'], 50) . '" dihapus.');
redirect('admin/berita.php');
