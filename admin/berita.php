<?php
require __DIR__ . '/../includes/init.php';
wajib_login();

$p = pengguna_sekarang();

// ---------- Filter ----------
$q      = trim((string)($_GET['q'] ?? ''));
$status = (string)($_GET['status'] ?? '');
$kat    = (int)($_GET['kategori'] ?? 0);

$where = ['1=1'];
$param = [];
if (!adalah_admin()) {
    $where[] = 'b.pengguna_id = ?';
    $param[] = $p['id'];
}
if ($q !== '') {
    $where[] = "b.judul LIKE ? ESCAPE '|'";
    $param[] = '%' . str_replace(['|', '%', '_'], ['||', '|%', '|_'], $q) . '%';
}
if (in_array($status, ['draft', 'terbit'], true)) {
    $where[] = 'b.status = ?';
    $param[] = $status;
}
if ($kat > 0) {
    $where[] = 'b.kategori_id = ?';
    $param[] = $kat;
}
$syarat = implode(' AND ', $where);

$per_hal = 10;
$hal     = halaman_sekarang();
$total   = hitung("SELECT COUNT(*) FROM berita b WHERE $syarat", $param);
$total_halaman = max(1, (int)ceil($total / $per_hal));
$hal = min($hal, $total_halaman);

$daftar = ambil_semua(
    SQL_BERITA . " WHERE $syarat ORDER BY b.dibuat_pada DESC, b.id DESC
                   LIMIT $per_hal OFFSET " . (($hal - 1) * $per_hal),
    $param
);
$semua_kategori = ambil_semua('SELECT id, nama FROM kategori ORDER BY nama');

$judul_admin = adalah_admin() ? 'Semua berita' : 'Berita saya';
$menu_aktif  = 'berita';
$aksi_kepala = '<a class="tombol" href="' . e(url('admin/berita_form.php')) . '">+ Tulis berita</a>';
require __DIR__ . '/../includes/admin_header.php';
?>

<form method="get" class="filter">
    <input type="search" name="q" placeholder="Cari judul" value="<?= e($q) ?>">
    <select name="status">
        <option value="">Semua status</option>
        <option value="terbit" <?= $status === 'terbit' ? 'selected' : '' ?>>Terbit</option>
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
    </select>
    <select name="kategori">
        <option value="0">Semua kategori</option>
        <?php foreach ($semua_kategori as $k): ?>
            <option value="<?= (int)$k['id'] ?>" <?= $kat === (int)$k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="tombol tombol-garis">Terapkan</button>
    <?php if ($q !== '' || $status !== '' || $kat > 0): ?>
        <a href="<?= e(url('admin/berita.php')) ?>">Reset</a>
    <?php endif; ?>
</form>

<section class="panel">
    <?php if (!$daftar): ?>
        <p class="kosong">Tidak ada berita yang cocok dengan filter.</p>
    <?php else: ?>
        <div class="gulir-tabel">
        <table class="tabel">
            <thead>
                <tr>
                    <th>Judul</th>
                    <?php if (adalah_admin()): ?><th>Penulis</th><?php endif; ?>
                    <th>Status</th>
                    <th class="rata-kanan">Dibaca</th>
                    <th>Dibuat</th>
                    <th class="rata-kanan">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($daftar as $b): ?>
                <tr>
                    <td>
                        <a href="<?= e(url('admin/berita_form.php?id=' . (int)$b['id'])) ?>"><?= e($b['judul']) ?></a>
                        <div class="sub"><?= e($b['kategori']) ?></div>
                    </td>
                    <?php if (adalah_admin()): ?><td><?= e($b['penulis']) ?></td><?php endif; ?>
                    <td><span class="lencana lencana-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
                    <td class="rata-kanan"><?= number_format((int)$b['dilihat'], 0, ',', '.') ?></td>
                    <td><?= e(tanggal_indo($b['dibuat_pada'])) ?></td>
                    <td class="rata-kanan aksi">
                        <a href="<?= e(link_berita($b)) ?>" target="_blank" rel="noopener">Lihat</a>
                        <a href="<?= e(url('admin/berita_form.php?id=' . (int)$b['id'])) ?>">Edit</a>
                        <form method="post" action="<?= e(url('admin/berita_hapus.php')) ?>" data-konfirmasi="Hapus berita ini beserta komentarnya? Tindakan ini tidak bisa dibatalkan.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                            <button type="submit" class="tautan-bahaya">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?= paginasi($hal, $total_halaman) ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
