<?php
require __DIR__ . '/../includes/init.php';
wajib_login();

$p  = pengguna_sekarang();
$id = (int)($_GET['id'] ?? 0);

// Mode edit: ambil data berita dan pastikan pengguna berhak mengubahnya
$berita = null;
if ($id > 0) {
    $berita = ambil_satu('SELECT * FROM berita WHERE id = ?', [$id]);
    if (!$berita) {
        halaman_galat(404, 'Berita tidak ditemukan.');
    }
    if (!adalah_admin() && (int)$berita['pengguna_id'] !== (int)$p['id']) {
        halaman_galat(403, 'Anda hanya bisa mengubah berita milik sendiri.');
    }
}

$daftar_kategori = ambil_semua('SELECT id, nama FROM kategori ORDER BY nama');

$input = [
    'judul'       => $berita['judul'] ?? '',
    'kategori_id' => $berita['kategori_id'] ?? '',
    'ringkasan'   => $berita['ringkasan'] ?? '',
    'isi'         => $berita['isi'] ?? '',
    'status'      => $berita['status'] ?? 'draft',
];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();

    $input['judul']       = trim($_POST['judul'] ?? '');
    $input['kategori_id'] = (int)($_POST['kategori_id'] ?? 0);
    $input['ringkasan']   = trim($_POST['ringkasan'] ?? '');
    $input['isi']         = trim($_POST['isi'] ?? '');
    $input['status']      = ($_POST['status'] ?? '') === 'terbit' ? 'terbit' : 'draft';

    if (mb_strlen($input['judul']) < 5 || mb_strlen($input['judul']) > 200) {
        $galat['judul'] = 'Judul harus 5 sampai 200 karakter.';
    }
    if (!ambil_satu('SELECT id FROM kategori WHERE id = ?', [$input['kategori_id']])) {
        $galat['kategori_id'] = 'Pilih kategori terlebih dahulu.';
    }
    if (mb_strlen($input['ringkasan']) < 10 || mb_strlen($input['ringkasan']) > 300) {
        $galat['ringkasan'] = 'Ringkasan harus 10 sampai 300 karakter.';
    }
    if (mb_strlen($input['isi']) < 20) {
        $galat['isi'] = 'Isi berita terlalu pendek (minimal 20 karakter).';
    }

    [$gambar_baru, $pesan_gambar] = unggah_gambar($_FILES['gambar'] ?? null);
    if ($pesan_gambar) {
        $galat['gambar'] = $pesan_gambar;
    }

    if (!$galat) {
        $sekarang = date('Y-m-d H:i:s');

        if ($berita) {
            // Slug tidak diubah saat edit supaya tautan lama tetap berfungsi
            $gambar = $berita['gambar'];
            if ($gambar_baru) {
                hapus_gambar($gambar);
                $gambar = $gambar_baru;
            } elseif (!empty($_POST['hapus_gambar'])) {
                hapus_gambar($gambar);
                $gambar = null;
            }

            $terbit_pada = $berita['terbit_pada'];
            if ($input['status'] === 'terbit' && !$terbit_pada) {
                $terbit_pada = $sekarang;
            }

            jalankan(
                'UPDATE berita SET kategori_id = ?, judul = ?, ringkasan = ?, isi = ?, gambar = ?,
                        status = ?, diubah_pada = ?, terbit_pada = ? WHERE id = ?',
                [$input['kategori_id'], $input['judul'], $input['ringkasan'], $input['isi'], $gambar,
                 $input['status'], $sekarang, $terbit_pada, $berita['id']]
            );
            flash_set('sukses', 'Perubahan berita disimpan.');
        } else {
            $slug = slug_unik('berita', buat_slug($input['judul']));
            jalankan(
                'INSERT INTO berita (kategori_id, pengguna_id, judul, slug, ringkasan, isi, gambar, status,
                                     dilihat, dibuat_pada, diubah_pada, terbit_pada)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?)',
                [$input['kategori_id'], $p['id'], $input['judul'], $slug, $input['ringkasan'], $input['isi'],
                 $gambar_baru, $input['status'], $sekarang, $sekarang,
                 $input['status'] === 'terbit' ? $sekarang : null]
            );
            flash_set('sukses', $input['status'] === 'terbit' ? 'Berita berhasil diterbitkan.' : 'Berita disimpan sebagai draft.');
        }
        redirect('admin/berita.php');
    }

    // Validasi gagal: gambar yang sempat terunggah dibuang supaya tidak menumpuk
    if ($gambar_baru) {
        hapus_gambar($gambar_baru);
    }
}

$judul_admin = $berita ? 'Edit berita' : 'Tulis berita';
$menu_aktif  = 'berita';
$gambar_saat_ini = $berita ? gambar_url($berita['gambar']) : null;
require __DIR__ . '/../includes/admin_header.php';
?>

<?php if (!$daftar_kategori): ?>
    <div class="pesan pesan-galat">
        Belum ada kategori.
        <?php if (adalah_admin()): ?>
            <a href="<?= e(url('admin/kategori.php')) ?>">Buat kategori dulu</a>.
        <?php else: ?>
            Minta admin untuk membuat kategori terlebih dahulu.
        <?php endif; ?>
    </div>
<?php else: ?>

<form method="post" enctype="multipart/form-data" class="form form-editor" novalidate>
    <?= csrf_field() ?>

    <div class="editor-utama">
        <div class="baris-form">
            <label for="judul">Judul</label>
            <input type="text" id="judul" name="judul" maxlength="200" value="<?= e($input['judul']) ?>" required>
            <?php if (isset($galat['judul'])): ?><span class="galat"><?= e($galat['judul']) ?></span><?php endif; ?>
        </div>

        <div class="baris-form">
            <label for="ringkasan">Ringkasan</label>
            <textarea id="ringkasan" name="ringkasan" rows="3" maxlength="300" data-hitung="#sisaRingkasan" required><?= e($input['ringkasan']) ?></textarea>
            <small class="bantuan">Tampil di daftar berita. <span id="sisaRingkasan">300</span> karakter tersisa.</small>
            <?php if (isset($galat['ringkasan'])): ?><span class="galat"><?= e($galat['ringkasan']) ?></span><?php endif; ?>
        </div>

        <div class="baris-form">
            <label for="isi">Isi berita</label>
            <textarea id="isi" name="isi" rows="18" required><?= e($input['isi']) ?></textarea>
            <small class="bantuan">Pisahkan paragraf dengan satu baris kosong.</small>
            <?php if (isset($galat['isi'])): ?><span class="galat"><?= e($galat['isi']) ?></span><?php endif; ?>
        </div>
    </div>

    <div class="editor-samping">
        <div class="panel">
            <div class="baris-form">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?= $input['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="terbit" <?= $input['status'] === 'terbit' ? 'selected' : '' ?>>Terbit</option>
                </select>
            </div>

            <div class="baris-form">
                <label for="kategori_id">Kategori</label>
                <select id="kategori_id" name="kategori_id" required>
                    <option value="">Pilih kategori</option>
                    <?php foreach ($daftar_kategori as $k): ?>
                        <option value="<?= (int)$k['id'] ?>" <?= (int)$input['kategori_id'] === (int)$k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($galat['kategori_id'])): ?><span class="galat"><?= e($galat['kategori_id']) ?></span><?php endif; ?>
            </div>

            <div class="baris-form">
                <label for="gambar">Foto utama</label>
                <?php if ($gambar_saat_ini): ?>
                    <img src="<?= e($gambar_saat_ini) ?>" alt="Foto saat ini" class="pratinjau-gambar" id="pratinjauGambar">
                    <label class="centang"><input type="checkbox" name="hapus_gambar" value="1"> Hapus foto ini</label>
                <?php else: ?>
                    <img src="" alt="" class="pratinjau-gambar" id="pratinjauGambar" hidden>
                <?php endif; ?>
                <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp" data-pratinjau="#pratinjauGambar">
                <small class="bantuan">JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                <?php if (isset($galat['gambar'])): ?><span class="galat"><?= e($galat['gambar']) ?></span><?php endif; ?>
            </div>

            <button type="submit" class="tombol tombol-penuh">Simpan</button>
            <?php if ($berita): ?>
                <p class="bantuan"><a href="<?= e(link_berita($berita)) ?>" target="_blank" rel="noopener">Lihat di situs &nearr;</a></p>
            <?php endif; ?>
            <p class="bantuan"><a href="<?= e(url('admin/berita.php')) ?>">&larr; Kembali ke daftar</a></p>
        </div>
    </div>
</form>

<?php endif; ?>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
