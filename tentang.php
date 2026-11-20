<?php
require __DIR__ . '/includes/init.php';

$judul_halaman = 'Tentang & Redaksi';

// TODO: ganti nama, NIM, dan peran di bawah ini dengan data anggota kelompok yang sebenarnya
$tim = [
    ['nama' => 'Nama Anggota 1', 'nim' => '00000000', 'peran' => 'Project Manager & Backend Developer',
     'tugas' => 'Rancangan database, koneksi dan keamanan, sistem login, manajemen pengguna.'],
    ['nama' => 'Nama Anggota 2', 'nim' => '00000000', 'peran' => 'Frontend Developer',
     'tugas' => 'Tata letak dan tampilan halaman publik, CSS responsif, interaksi JavaScript.'],
    ['nama' => 'Nama Anggota 3', 'nim' => '00000000', 'peran' => 'Backend Developer (Panel Admin)',
     'tugas' => 'Dashboard, kelola berita dan kategori, unggah gambar.'],
    ['nama' => 'Nama Anggota 4', 'nim' => '00000000', 'peran' => 'Fitur Interaksi, QA & Dokumentasi',
     'tugas' => 'Halaman detail, komentar dan moderasi, pencarian, pengujian, dokumentasi.'],
];

require __DIR__ . '/includes/header.php';
?>

<div class="wadah">
    <div class="judul-halaman">
        <h1>Tentang <?= e(NAMA_SITUS) ?></h1>
    </div>

    <div class="tata-letak">
        <article class="artikel">
            <div class="isi-artikel">
                <p><?= e(NAMA_SITUS) ?> adalah portal berita yang dibuat sebagai tugas akhir mata kuliah Pemrograman Web 2.
                   Situs ini memuat berita dari beberapa kategori, dilengkapi pencarian, kolom komentar dengan moderasi,
                   serta panel redaksi untuk mengelola konten.</p>
                <p>Seluruh isi berita pada versi pengembangan ini adalah contoh dan tidak merujuk pada peristiwa nyata.</p>
            </div>

            <h2 class="judul-bagian">Tim Pengembang</h2>
            <div class="daftar-tim">
                <?php foreach ($tim as $t): ?>
                    <div class="anggota">
                        <p class="anggota-nama"><?= e($t['nama']) ?> <span class="meta">(<?= e($t['nim']) ?>)</span></p>
                        <p class="anggota-peran"><?= e($t['peran']) ?></p>
                        <p class="meta"><?= e($t['tugas']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <?php require __DIR__ . '/includes/sidebar.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
