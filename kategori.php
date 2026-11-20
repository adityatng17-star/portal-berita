<?php
require __DIR__ . '/includes/init.php';

$slug = $_GET['slug'] ?? '';
$kategori = is_string($slug) ? ambil_satu('SELECT * FROM kategori WHERE slug = ?', [$slug]) : null;
if (!$kategori) {
    tampil_404();
}

$hal   = halaman_sekarang();
$total = hitung("SELECT COUNT(*) FROM berita WHERE status = 'terbit' AND kategori_id = ?", [$kategori['id']]);
$total_halaman = max(1, (int)ceil($total / PER_HALAMAN));
if ($hal > $total_halaman) {
    tampil_404();
}

$daftar = ambil_semua(
    SQL_BERITA . " WHERE b.status = 'terbit' AND b.kategori_id = ?
                   ORDER BY b.terbit_pada DESC, b.id DESC
                   LIMIT " . PER_HALAMAN . " OFFSET " . (($hal - 1) * PER_HALAMAN),
    [$kategori['id']]
);

$judul_halaman     = 'Kategori ' . $kategori['nama'];
$deskripsi_halaman = 'Kumpulan berita ' . $kategori['nama'] . ' terbaru dari ' . NAMA_SITUS . '.';
$nav_aktif         = $kategori['slug'];
require __DIR__ . '/includes/header.php';
?>

<div class="wadah">
    <div class="judul-halaman">
        <p class="meta">Kategori</p>
        <h1><?= e($kategori['nama']) ?></h1>
        <p class="meta"><?= $total ?> berita</p>
    </div>

    <div class="tata-letak">
        <section>
            <?php if (!$daftar): ?>
                <p class="kosong">Belum ada berita di kategori ini.</p>
            <?php endif; ?>

            <div class="daftar-kartu">
                <?php foreach ($daftar as $b): ?>
                    <?= kartu_berita($b) ?>
                <?php endforeach; ?>
            </div>

            <?= paginasi($hal, $total_halaman) ?>
        </section>

        <?php require __DIR__ . '/includes/sidebar.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
