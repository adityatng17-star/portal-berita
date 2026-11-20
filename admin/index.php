<?php
require __DIR__ . '/../includes/init.php';
wajib_login();

$p     = pengguna_sekarang();
$admin = adalah_admin();
// Editor hanya melihat angka untuk berita miliknya sendiri
$syarat = $admin ? '1=1' : 'b.pengguna_id = ' . (int)$p['id'];

$stat = [
    'Total berita' => hitung("SELECT COUNT(*) FROM berita b WHERE $syarat"),
    'Terbit'       => hitung("SELECT COUNT(*) FROM berita b WHERE b.status = 'terbit' AND $syarat"),
    'Draft'        => hitung("SELECT COUNT(*) FROM berita b WHERE b.status = 'draft' AND $syarat"),
    'Total dibaca' => hitung("SELECT COALESCE(SUM(b.dilihat), 0) FROM berita b WHERE $syarat"),
];

$terbaru = ambil_semua(SQL_BERITA . " WHERE $syarat ORDER BY b.dibuat_pada DESC, b.id DESC LIMIT 6");

$komentar_baru = [];
if ($admin) {
    $komentar_baru = ambil_semua(
        "SELECT c.*, b.judul FROM komentar c JOIN berita b ON b.id = c.berita_id
         WHERE c.status = 'menunggu' ORDER BY c.dibuat_pada DESC LIMIT 5"
    );
}

$judul_admin = 'Dashboard';
$menu_aktif  = 'dashboard';
$aksi_kepala = '<a class="tombol" href="' . e(url('admin/berita_form.php')) . '">+ Tulis berita</a>';
require __DIR__ . '/../includes/admin_header.php';
?>

<p class="sapaan">Halo, <?= e($p['nama']) ?>. <?= $admin ? 'Berikut ringkasan seluruh situs.' : 'Berikut ringkasan tulisan Anda.' ?></p>

<div class="deret-stat">
    <?php foreach ($stat as $label => $nilai): ?>
        <div class="stat">
            <span class="stat-angka"><?= number_format($nilai, 0, ',', '.') ?></span>
            <span class="stat-label"><?= e($label) ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="dua-kolom">
    <section class="panel">
        <h2>Berita terbaru</h2>
        <?php if (!$terbaru): ?>
            <p class="kosong">Belum ada berita. <a href="<?= e(url('admin/berita_form.php')) ?>">Tulis yang pertama</a>.</p>
        <?php else: ?>
            <table class="tabel">
                <tbody>
                <?php foreach ($terbaru as $b): ?>
                    <tr>
                        <td>
                            <a href="<?= e(url('admin/berita_form.php?id=' . (int)$b['id'])) ?>"><?= e($b['judul']) ?></a>
                            <div class="sub"><?= e($b['kategori']) ?> &middot; <?= e(waktu_lalu($b['dibuat_pada'])) ?></div>
                        </td>
                        <td class="rata-kanan"><span class="lencana lencana-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <?php if ($admin): ?>
        <section class="panel">
            <h2>Komentar menunggu persetujuan</h2>
            <?php if (!$komentar_baru): ?>
                <p class="kosong">Tidak ada komentar yang menunggu.</p>
            <?php else: ?>
                <ul class="daftar-ringkas">
                    <?php foreach ($komentar_baru as $k): ?>
                        <li>
                            <strong><?= e($k['nama']) ?></strong> pada <em><?= e(ringkas($k['judul'], 45)) ?></em>
                            <div class="sub"><?= e(ringkas($k['isi'], 90)) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><a href="<?= e(url('admin/komentar.php')) ?>">Kelola komentar &rarr;</a></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
