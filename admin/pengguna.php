<?php
require __DIR__ . '/../includes/init.php';
wajib_admin();

$saya    = pengguna_sekarang();
$galat   = '';
$edit_id = (int)($_GET['edit'] ?? 0);
$input   = ['nama' => '', 'email' => '', 'peran' => 'editor'];

if ($edit_id > 0) {
    $diedit = ambil_satu('SELECT id, nama, email, peran FROM pengguna WHERE id = ?', [$edit_id]);
    if (!$diedit) {
        redirect('admin/pengguna.php');
    }
    $input = ['nama' => $diedit['nama'], 'email' => $diedit['email'], 'peran' => $diedit['peran']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id'] ?? 0);

    if ($aksi === 'simpan') {
        $edit_id = $id;
        $input['nama']  = trim($_POST['nama'] ?? '');
        $input['email'] = trim($_POST['email'] ?? '');
        $input['peran'] = ($_POST['peran'] ?? '') === 'admin' ? 'admin' : 'editor';
        $password       = $_POST['password'] ?? '';

        $lama = $id > 0 ? ambil_satu('SELECT * FROM pengguna WHERE id = ?', [$id]) : null;

        if (mb_strlen($input['nama']) < 2 || mb_strlen($input['nama']) > 80) {
            $galat = 'Nama harus 2 sampai 80 karakter.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $galat = 'Format email tidak valid.';
        } elseif (ambil_satu('SELECT id FROM pengguna WHERE email = ? AND id <> ?', [$input['email'], $id])) {
            $galat = 'Email sudah dipakai pengguna lain.';
        } elseif (!$lama && strlen($password) < 6) {
            $galat = 'Password minimal 6 karakter.';
        } elseif ($lama && $password !== '' && strlen($password) < 6) {
            $galat = 'Password baru minimal 6 karakter (kosongkan jika tidak ingin diganti).';
        } elseif ($lama && $lama['peran'] === 'admin' && $input['peran'] !== 'admin'
                  && hitung("SELECT COUNT(*) FROM pengguna WHERE peran = 'admin'") <= 1) {
            $galat = 'Minimal harus ada satu admin.';
        }

        if ($galat === '') {
            if ($lama) {
                if ($password !== '') {
                    jalankan('UPDATE pengguna SET nama = ?, email = ?, peran = ?, password = ? WHERE id = ?',
                        [$input['nama'], $input['email'], $input['peran'], password_hash($password, PASSWORD_DEFAULT), $id]);
                } else {
                    jalankan('UPDATE pengguna SET nama = ?, email = ?, peran = ? WHERE id = ?',
                        [$input['nama'], $input['email'], $input['peran'], $id]);
                }
                flash_set('sukses', 'Data pengguna diperbarui.');
            } else {
                jalankan('INSERT INTO pengguna (nama, email, password, peran, dibuat_pada) VALUES (?, ?, ?, ?, ?)',
                    [$input['nama'], $input['email'], password_hash($password, PASSWORD_DEFAULT), $input['peran'], date('Y-m-d H:i:s')]);
                flash_set('sukses', 'Pengguna baru ditambahkan.');
            }
            redirect('admin/pengguna.php');
        }
    }

    if ($aksi === 'hapus') {
        if ($id === (int)$saya['id']) {
            flash_set('galat', 'Anda tidak bisa menghapus akun sendiri.');
        } elseif (hitung('SELECT COUNT(*) FROM berita WHERE pengguna_id = ?', [$id]) > 0) {
            flash_set('galat', 'Pengguna tidak bisa dihapus karena masih memiliki berita.');
        } else {
            jalankan('DELETE FROM pengguna WHERE id = ?', [$id]);
            flash_set('sukses', 'Pengguna dihapus.');
        }
        redirect('admin/pengguna.php');
    }
}

$daftar = ambil_semua(
    'SELECT p.*, COUNT(b.id) AS jumlah_berita FROM pengguna p
     LEFT JOIN berita b ON b.pengguna_id = p.id
     GROUP BY p.id ORDER BY p.nama'
);

$judul_admin = 'Pengguna';
$menu_aktif  = 'pengguna';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="dua-kolom">
    <section class="panel">
        <h2>Daftar pengguna</h2>
        <div class="gulir-tabel">
        <table class="tabel">
            <thead>
                <tr><th>Nama</th><th>Peran</th><th class="rata-kanan">Berita</th><th class="rata-kanan">Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($daftar as $u): ?>
                <tr>
                    <td>
                        <?= e($u['nama']) ?><?= (int)$u['id'] === (int)$saya['id'] ? ' <span class="sub">(Anda)</span>' : '' ?>
                        <div class="sub"><?= e($u['email']) ?></div>
                    </td>
                    <td><span class="lencana lencana-<?= $u['peran'] === 'admin' ? 'terbit' : 'draft' ?>"><?= e($u['peran']) ?></span></td>
                    <td class="rata-kanan"><?= (int)$u['jumlah_berita'] ?></td>
                    <td class="rata-kanan aksi">
                        <a href="<?= e(url('admin/pengguna.php?edit=' . (int)$u['id'])) ?>">Edit</a>
                        <?php if ((int)$u['id'] !== (int)$saya['id']): ?>
                            <form method="post" data-konfirmasi="Hapus pengguna <?= e($u['nama']) ?>?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="tautan-bahaya">Hapus</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </section>

    <section class="panel">
        <h2><?= $edit_id > 0 ? 'Edit pengguna' : 'Tambah pengguna' ?></h2>
        <?php if ($galat): ?><div class="pesan pesan-galat"><?= e($galat) ?></div><?php endif; ?>
        <form method="post" class="form" autocomplete="off">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="simpan">
            <input type="hidden" name="id" value="<?= $edit_id ?>">

            <div class="baris-form">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" maxlength="80" value="<?= e($input['nama']) ?>" required>
            </div>
            <div class="baris-form">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="120" value="<?= e($input['email']) ?>" required>
            </div>
            <div class="baris-form">
                <label for="peran">Peran</label>
                <select id="peran" name="peran">
                    <option value="editor" <?= $input['peran'] === 'editor' ? 'selected' : '' ?>>Editor (hanya berita sendiri)</option>
                    <option value="admin" <?= $input['peran'] === 'admin' ? 'selected' : '' ?>>Admin (akses penuh)</option>
                </select>
            </div>
            <div class="baris-form">
                <label for="password">Password <?php if ($edit_id > 0): ?><small>(kosongkan jika tidak diganti)</small><?php endif; ?></label>
                <input type="password" id="password" name="password" autocomplete="new-password" <?= $edit_id > 0 ? '' : 'required' ?>>
            </div>

            <button type="submit" class="tombol"><?= $edit_id > 0 ? 'Simpan perubahan' : 'Tambah pengguna' ?></button>
            <?php if ($edit_id > 0): ?>
                <a href="<?= e(url('admin/pengguna.php')) ?>" class="tautan-batal">Batal</a>
            <?php endif; ?>
        </form>
    </section>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
