<?php
require __DIR__ . '/../includes/init.php';
wajib_admin();

$galat   = '';
$edit_id = (int)($_GET['edit'] ?? 0);
$nama    = '';

if ($edit_id > 0) {
    $sedang_diedit = ambil_satu('SELECT * FROM kategori WHERE id = ?', [$edit_id]);
    if (!$sedang_diedit) {
        redirect('admin/kategori.php');
    }
    $nama = $sedang_diedit['nama'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $id   = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $edit_id = $id;

        if (mb_strlen($nama) < 2 || mb_strlen($nama) > 60) {
            $galat = 'Nama kategori harus 2 sampai 60 karakter.';
        } elseif (ambil_satu('SELECT id FROM kategori WHERE LOWER(nama) = LOWER(?) AND id <> ?', [$nama, $id])) {
            $galat = 'Kategori dengan nama itu sudah ada.';
        } else {
            $slug = slug_unik('kategori', buat_slug($nama), $id);
            if ($id > 0) {
                jalankan('UPDATE kategori SET nama = ?, slug = ? WHERE id = ?', [$nama, $slug, $id]);
                flash_set('sukses', 'Kategori diperbarui.');
            } else {
                jalankan('INSERT INTO kategori (nama, slug) VALUES (?, ?)', [$nama, $slug]);
                flash_set('sukses', 'Kategori ditambahkan.');
            }
            redirect('admin/kategori.php');
        }
    }

    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if (hitung('SELECT COUNT(*) FROM berita WHERE kategori_id = ?', [$id]) > 0) {
            flash_set('galat', 'Kategori tidak bisa dihapus karena masih dipakai oleh berita.');
        } else {
            jalankan('DELETE FROM kategori WHERE id = ?', [$id]);
            flash_set('sukses', 'Kategori dihapus.');
        }
        redirect('admin/kategori.php');
    }
}

$daftar = ambil_semua(
    'SELECT k.*, COUNT(b.id) AS jumlah FROM kategori k
     LEFT JOIN berita b ON b.kategori_id = k.id
     GROUP BY k.id ORDER BY k.nama'
);

$judul_admin = 'Kategori';
$menu_aktif  = 'kategori';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="dua-kolom">
    <section class="panel">
        <h2>Daftar kategori</h2>
        <?php if (!$daftar): ?>
            <p class="kosong">Belum ada kategori.</p>
        <?php else: ?>
            <table class="tabel">
                <thead>
                    <tr><th>Nama</th><th>Slug</th><th class="rata-kanan">Berita</th><th class="rata-kanan">Aksi</th></tr>
                </thead>
                <tbody>
                <?php foreach ($daftar as $k): ?>
                    <tr>
                        <td><?= e($k['nama']) ?></td>
                        <td class="sub"><?= e($k['slug']) ?></td>
                        <td class="rata-kanan"><?= (int)$k['jumlah'] ?></td>
                        <td class="rata-kanan aksi">
                            <a href="<?= e(url('admin/kategori.php?edit=' . (int)$k['id'])) ?>">Edit</a>
                            <form method="post" data-konfirmasi="Hapus kategori <?= e($k['nama']) ?>?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                                <button type="submit" class="tautan-bahaya">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2><?= $edit_id > 0 ? 'Edit kategori' : 'Tambah kategori' ?></h2>
        <?php if ($galat): ?><div class="pesan pesan-galat"><?= e($galat) ?></div><?php endif; ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="simpan">
            <input type="hidden" name="id" value="<?= $edit_id ?>">
            <div class="baris-form">
                <label for="nama">Nama kategori</label>
                <input type="text" id="nama" name="nama" maxlength="60" value="<?= e($nama) ?>" required>
            </div>
            <button type="submit" class="tombol"><?= $edit_id > 0 ? 'Simpan perubahan' : 'Tambah' ?></button>
            <?php if ($edit_id > 0): ?>
                <a href="<?= e(url('admin/kategori.php')) ?>" class="tautan-batal">Batal</a>
            <?php endif; ?>
        </form>
    </section>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
