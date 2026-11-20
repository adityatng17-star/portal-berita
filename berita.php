<?php
require __DIR__ . '/includes/init.php';

$slug = $_GET['slug'] ?? '';
$b = is_string($slug) ? ambil_satu(SQL_BERITA . " WHERE b.slug = ?", [$slug]) : null;
if (!$b) {
    tampil_404();
}

// Berita draft hanya boleh dilihat penulisnya sendiri atau admin (mode pratinjau)
$pratinjau = false;
if ($b['status'] !== 'terbit') {
    $p = pengguna_sekarang();
    if (!$p || ($p['peran'] !== 'admin' && (int)$p['id'] !== (int)$b['pengguna_id'])) {
        tampil_404();
    }
    $pratinjau = true;
}

// ---------- Kirim komentar ----------
$galat = [];
$input = ['nama' => '', 'email' => '', 'isi' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pratinjau) {
    csrf_cek();
    $kembali = 'berita.php?slug=' . urlencode($b['slug']) . '#komentar';

    // Kolom "website" disembunyikan lewat CSS. Manusia tidak akan mengisinya, bot biasanya iya.
    if (!empty($_POST['website'])) {
        redirect($kembali);
    }

    $input['nama']  = trim($_POST['nama'] ?? '');
    $input['email'] = trim($_POST['email'] ?? '');
    $input['isi']   = trim($_POST['isi'] ?? '');

    if (mb_strlen($input['nama']) < 2 || mb_strlen($input['nama']) > 60) {
        $galat['nama'] = 'Nama harus 2 sampai 60 karakter.';
    }
    if ($input['email'] !== '' && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $galat['email'] = 'Format email tidak valid.';
    }
    if (mb_strlen($input['isi']) < 3 || mb_strlen($input['isi']) > 1000) {
        $galat['isi'] = 'Komentar harus 3 sampai 1000 karakter.';
    }
    // Batasi: satu komentar per 20 detik untuk setiap pengunjung
    if (!$galat && isset($_SESSION['komentar_terakhir']) && time() - $_SESSION['komentar_terakhir'] < 20) {
        $galat['isi'] = 'Terlalu cepat. Tunggu beberapa detik sebelum mengirim komentar lagi.';
    }

    if (!$galat) {
        jalankan(
            "INSERT INTO komentar (berita_id, nama, email, isi, status, dibuat_pada)
             VALUES (?, ?, ?, ?, 'menunggu', ?)",
            [$b['id'], $input['nama'], $input['email'] !== '' ? $input['email'] : null, $input['isi'], date('Y-m-d H:i:s')]
        );
        $_SESSION['komentar_terakhir'] = time();
        flash_set('sukses', 'Terima kasih. Komentar Anda akan tampil setelah disetujui redaksi.');
        redirect($kembali);
    }
}

// ---------- Hitung pembaca (sekali per sesi untuk tiap berita) ----------
if (!$pratinjau && empty($_SESSION['sudah_dibaca'][$b['id']])) {
    jalankan('UPDATE berita SET dilihat = dilihat + 1 WHERE id = ?', [$b['id']]);
    $_SESSION['sudah_dibaca'][$b['id']] = true;
    $b['dilihat']++;
}

$komentar = ambil_semua(
    "SELECT * FROM komentar WHERE berita_id = ? AND status = 'tampil' ORDER BY dibuat_pada ASC",
    [$b['id']]
);
$terkait = ambil_semua(
    SQL_BERITA . " WHERE b.status = 'terbit' AND b.kategori_id = ? AND b.id <> ?
                   ORDER BY b.terbit_pada DESC LIMIT 3",
    [$b['kategori_id'], $b['id']]
);

$judul_halaman     = $b['judul'];
$deskripsi_halaman = $b['ringkasan'];
$nav_aktif         = $b['kategori_slug'];
$gambar            = gambar_url($b['gambar']);
$tanggal           = $b['terbit_pada'] ?: $b['dibuat_pada'];

require __DIR__ . '/includes/header.php';
?>

<div class="wadah">
    <?php if ($pratinjau): ?>
        <div class="pesan pesan-info">Mode pratinjau: berita ini masih berstatus draft dan belum tampil untuk pengunjung.</div>
    <?php endif; ?>
    <?= flash_tampil() ?>

    <div class="tata-letak">
        <article class="artikel">
            <nav class="remah" aria-label="Lokasi">
                <a href="<?= e(url()) ?>">Beranda</a> /
                <a href="<?= e(link_kategori($b['kategori_slug'])) ?>"><?= e($b['kategori']) ?></a>
            </nav>

            <h1><?= e($b['judul']) ?></h1>
            <p class="lead"><?= e($b['ringkasan']) ?></p>
            <p class="meta meta-artikel">
                Oleh <strong><?= e($b['penulis']) ?></strong> &middot;
                <?= e(tanggal_indo($tanggal, true)) ?> &middot;
                <?= menit_baca($b['isi']) ?> menit baca &middot;
                <?= number_format((int)$b['dilihat'], 0, ',', '.') ?> pembaca
            </p>

            <?php if ($gambar): ?>
                <figure class="foto-artikel">
                    <img src="<?= e($gambar) ?>" alt="<?= e($b['judul']) ?>">
                </figure>
            <?php endif; ?>

            <div class="isi-artikel">
                <?= paragraf($b['isi']) ?>
            </div>

            <?php if ($terkait): ?>
                <section class="terkait">
                    <h2 class="judul-bagian">Berita Terkait</h2>
                    <div class="daftar-kartu">
                        <?php foreach ($terkait as $t): ?>
                            <?= kartu_berita($t) ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="komentar" id="komentar">
                <h2 class="judul-bagian">Komentar (<?= count($komentar) ?>)</h2>

                <?php if (!$komentar): ?>
                    <p class="kosong">Belum ada komentar. Jadilah yang pertama.</p>
                <?php endif; ?>

                <?php foreach ($komentar as $k): ?>
                    <div class="komentar-item">
                        <p class="komentar-kepala">
                            <strong><?= e($k['nama']) ?></strong>
                            <span class="meta"><?= e(waktu_lalu($k['dibuat_pada'])) ?></span>
                        </p>
                        <p><?= nl2br(e($k['isi'])) ?></p>
                    </div>
                <?php endforeach; ?>

                <?php if (!$pratinjau): ?>
                    <form method="post" class="form form-komentar" action="#komentar" novalidate>
                        <h3>Tulis komentar</h3>
                        <?= csrf_field() ?>

                        <div class="baris-form">
                            <label for="nama">Nama</label>
                            <input type="text" id="nama" name="nama" maxlength="60" required value="<?= e($input['nama']) ?>">
                            <?php if (isset($galat['nama'])): ?><span class="galat"><?= e($galat['nama']) ?></span><?php endif; ?>
                        </div>

                        <div class="baris-form">
                            <label for="email">Email <small>(opsional, tidak ditampilkan)</small></label>
                            <input type="email" id="email" name="email" maxlength="120" value="<?= e($input['email']) ?>">
                            <?php if (isset($galat['email'])): ?><span class="galat"><?= e($galat['email']) ?></span><?php endif; ?>
                        </div>

                        <div class="baris-form">
                            <label for="isi">Komentar</label>
                            <textarea id="isi" name="isi" rows="5" maxlength="1000" required data-hitung="#sisaKarakter"><?= e($input['isi']) ?></textarea>
                            <small class="bantuan"><span id="sisaKarakter">1000</span> karakter tersisa</small>
                            <?php if (isset($galat['isi'])): ?><span class="galat"><?= e($galat['isi']) ?></span><?php endif; ?>
                        </div>

                        <div class="jebakan" aria-hidden="true">
                            <label for="website">Jangan diisi</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <button type="submit" class="tombol">Kirim komentar</button>
                    </form>
                <?php endif; ?>
            </section>
        </article>

        <?php require __DIR__ . '/includes/sidebar.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
