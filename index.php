<?php
require __DIR__ . '/includes/init.php';

$hal   = halaman_sekarang();
$total = hitung("SELECT COUNT(*) FROM berita WHERE status = 'terbit'");

// Berita paling baru jadi headline di halaman 1, sisanya masuk daftar "Terbaru"
$headline = null;
if ($hal === 1) {
    $headline = ambil_satu(SQL_BERITA . " WHERE b.status = 'terbit' ORDER BY b.terbit_pada DESC, b.id DESC LIMIT 1");
}
$offset = 1 + ($hal - 1) * PER_HALAMAN;
$daftar = ambil_semua(
    SQL_BERITA . " WHERE b.status = 'terbit' ORDER BY b.terbit_pada DESC, b.id DESC
                   LIMIT " . PER_HALAMAN . " OFFSET " . $offset
);
$total_halaman = max(1, (int)ceil(max(0, $total - 1) / PER_HALAMAN));

if ($hal > 1 && $hal > $total_halaman) {
    tampil_404();
}

$nav_aktif = 'beranda';
require __DIR__ . '/includes/header.php';
?>

<div class="wadah">
    <?= flash_tampil() ?>

    <?php if ($headline): ?>
        <section class="headline">
            <a class="headline-gambar" href="<?= e(link_berita($headline)) ?>" tabindex="-1"><?= thumb_html($headline) ?></a>
            <div class="headline-isi">
                <a class="label-kategori" href="<?= e(link_kategori($headline['kategori_slug'])) ?>"><?= e($headline['kategori']) ?></a>
                <h1><a href="<?= e(link_berita($headline)) ?>"><?= e($headline['judul']) ?></a></h1>
                <p class="ringkasan"><?= e($headline['ringkasan']) ?></p>
                <p class="meta"><?= e($headline['penulis']) ?> &middot; <?= e(tanggal_indo($headline['terbit_pada'])) ?></p>
            </div>
        </section>
    <?php endif; ?>

    <div class="tata-letak">
        <section>
            <h2 class="judul-bagian">Berita Terbaru</h2>

            <?php if (!$daftar && !$headline): ?>
                <p class="kosong">Belum ada berita yang diterbitkan.</p>
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
