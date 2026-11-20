<?php
require __DIR__ . '/includes/init.php';

$q = $_GET['q'] ?? '';
$q = is_string($q) ? trim(mb_substr($q, 0, 80)) : '';

$hal    = halaman_sekarang();
$daftar = [];
$total  = 0;
$total_halaman = 1;

if ($q !== '') {
    // Karakter % dan _ milik LIKE di-escape supaya dianggap teks biasa
    $kata   = '%' . str_replace(['|', '%', '_'], ['||', '|%', '|_'], $q) . '%';
    $syarat = "b.status = 'terbit' AND (b.judul LIKE ? ESCAPE '|' OR b.ringkasan LIKE ? ESCAPE '|' OR b.isi LIKE ? ESCAPE '|')";
    $param  = [$kata, $kata, $kata];

    $total = hitung("SELECT COUNT(*) FROM berita b WHERE $syarat", $param);
    $total_halaman = max(1, (int)ceil($total / PER_HALAMAN));
    $daftar = ambil_semua(
        SQL_BERITA . " WHERE $syarat ORDER BY b.terbit_pada DESC, b.id DESC
                       LIMIT " . PER_HALAMAN . " OFFSET " . (($hal - 1) * PER_HALAMAN),
        $param
    );
}

$judul_halaman = $q !== '' ? 'Hasil pencarian: ' . $q : 'Pencarian';
require __DIR__ . '/includes/header.php';
?>

<div class="wadah">
    <div class="judul-halaman">
        <p class="meta">Pencarian</p>
        <?php if ($q === ''): ?>
            <h1>Cari berita</h1>
            <p class="meta">Ketik kata kunci pada kolom di atas.</p>
        <?php else: ?>
            <h1>&ldquo;<?= e($q) ?>&rdquo;</h1>
            <p class="meta"><?= $total ?> berita ditemukan</p>
        <?php endif; ?>
    </div>

    <div class="tata-letak">
        <section>
            <?php if ($q !== '' && !$daftar): ?>
                <p class="kosong">Tidak ada berita yang cocok. Coba kata kunci lain yang lebih umum.</p>
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
