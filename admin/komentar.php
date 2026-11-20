<?php
require __DIR__ . '/../includes/init.php';
wajib_admin();

// ---------- Aksi moderasi ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $id   = (int)($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'setujui') {
        jalankan("UPDATE komentar SET status = 'tampil' WHERE id = ?", [$id]);
        flash_set('sukses', 'Komentar disetujui dan sekarang tampil di situs.');
    } elseif ($aksi === 'hapus') {
        jalankan('DELETE FROM komentar WHERE id = ?', [$id]);
        flash_set('sukses', 'Komentar dihapus.');
    }
    // Kembali ke halaman yang sama dengan filter yang sedang dipakai
    $kembali = 'admin/komentar.php';
    if (in_array($_POST['status'] ?? '', ['menunggu', 'tampil', 'semua'], true)) {
        $kembali .= '?status=' . $_POST['status'];
    }
    redirect($kembali);
}

$status = $_GET['status'] ?? 'menunggu';
if (!in_array($status, ['menunggu', 'tampil', 'semua'], true)) {
    $status = 'menunggu';
}
$syarat = $status === 'semua' ? '1=1' : 'c.status = ?';
$param  = $status === 'semua' ? [] : [$status];

$per_hal = 15;
$total   = hitung("SELECT COUNT(*) FROM komentar c WHERE $syarat", $param);
$total_halaman = max(1, (int)ceil($total / $per_hal));
$hal     = min(halaman_sekarang(), $total_halaman);

$daftar = ambil_semua(
    "SELECT c.*, b.judul, b.slug FROM komentar c
     JOIN berita b ON b.id = c.berita_id
     WHERE $syarat ORDER BY c.dibuat_pada DESC
     LIMIT $per_hal OFFSET " . (($hal - 1) * $per_hal),
    $param
);

$jumlah = [
    'menunggu' => hitung("SELECT COUNT(*) FROM komentar WHERE status = 'menunggu'"),
    'tampil'   => hitung("SELECT COUNT(*) FROM komentar WHERE status = 'tampil'"),
];

$judul_admin = 'Komentar';
$menu_aktif  = 'komentar';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="tab">
    <a href="?status=menunggu" class="<?= $status === 'menunggu' ? 'aktif' : '' ?>">Menunggu (<?= $jumlah['menunggu'] ?>)</a>
    <a href="?status=tampil" class="<?= $status === 'tampil' ? 'aktif' : '' ?>">Tampil (<?= $jumlah['tampil'] ?>)</a>
    <a href="?status=semua" class="<?= $status === 'semua' ? 'aktif' : '' ?>">Semua</a>
</div>

<section class="panel">
    <?php if (!$daftar): ?>
        <p class="kosong">Tidak ada komentar pada kategori ini.</p>
    <?php endif; ?>

    <?php foreach ($daftar as $k): ?>
        <div class="item-komentar">
            <div class="item-komentar-isi">
                <p>
                    <strong><?= e($k['nama']) ?></strong>
                    <?php if ($k['email']): ?><span class="sub">&lt;<?= e($k['email']) ?>&gt;</span><?php endif; ?>
                    <span class="lencana lencana-<?= $k['status'] === 'tampil' ? 'terbit' : 'draft' ?>"><?= e($k['status']) ?></span>
                </p>
                <p class="teks-komentar"><?= nl2br(e($k['isi'])) ?></p>
                <p class="sub">
                    Pada <a href="<?= e(url('berita.php?slug=' . urlencode($k['slug']) . '#komentar')) ?>" target="_blank" rel="noopener"><?= e(ringkas($k['judul'], 60)) ?></a>
                    &middot; <?= e(tanggal_indo($k['dibuat_pada'], true)) ?>
                </p>
            </div>
            <div class="item-komentar-aksi">
                <?php if ($k['status'] === 'menunggu'): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="setujui">
                        <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                        <input type="hidden" name="status" value="<?= e($status) ?>">
                        <button type="submit" class="tombol tombol-kecil">Setujui</button>
                    </form>
                <?php endif; ?>
                <form method="post" data-konfirmasi="Hapus komentar dari <?= e($k['nama']) ?>?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                    <input type="hidden" name="status" value="<?= e($status) ?>">
                    <button type="submit" class="tombol tombol-kecil tombol-bahaya">Hapus</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <?= paginasi($hal, $total_halaman) ?>
</section>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
