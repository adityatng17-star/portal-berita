<?php
// ------------------------------------------------------------
// Fungsi-fungsi bantu yang dipakai di banyak halaman
// ------------------------------------------------------------

// Query dasar untuk mengambil berita lengkap dengan nama kategori dan penulis
const SQL_BERITA = "SELECT b.*, k.nama AS kategori, k.slug AS kategori_slug, p.nama AS penulis
                    FROM berita b
                    JOIN kategori k ON k.id = b.kategori_id
                    JOIN pengguna p ON p.id = b.pengguna_id";

// ---------- Umum ----------

function e($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

// ---------- Database ----------

function ambil_semua($sql, $params = []) {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function ambil_satu($sql, $params = []) {
    $st = db()->prepare($sql);
    $st->execute($params);
    $baris = $st->fetch();
    return $baris ?: null;
}

function jalankan($sql, $params = []) {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function hitung($sql, $params = []) {
    $st = db()->prepare($sql);
    $st->execute($params);
    return (int)$st->fetchColumn();
}

// Daftar kategori beserta jumlah berita terbitnya (di-cache per request)
function kategori_semua() {
    static $data = null;
    if ($data === null) {
        $data = ambil_semua(
            "SELECT k.*, COUNT(b.id) AS jumlah
             FROM kategori k
             LEFT JOIN berita b ON b.kategori_id = k.id AND b.status = 'terbit'
             GROUP BY k.id
             ORDER BY k.nama"
        );
    }
    return $data;
}

// ---------- Pesan flash ----------

function flash_set($tipe, $pesan) {
    $_SESSION['flash'][] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function flash_tampil() {
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $html = '';
    foreach ($_SESSION['flash'] as $f) {
        $html .= '<div class="pesan pesan-' . e($f['tipe']) . '" role="status">' . e($f['pesan']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

// ---------- CSRF ----------

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_cek() {
    $kirim = $_POST['csrf'] ?? '';
    if (!is_string($kirim) || !hash_equals(csrf_token(), $kirim)) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

// ---------- Teks ----------

function buat_slug($teks) {
    $teks = mb_strtolower(trim($teks), 'UTF-8');
    $teks = preg_replace('/[^a-z0-9]+/', '-', $teks);
    $teks = rtrim(mb_substr(trim($teks, '-'), 0, 100), '-');
    return $teks !== '' ? $teks : 'berita';
}

// Pastikan slug belum dipakai di tabel tertentu (hanya dipanggil dengan nama tabel dari kode kita sendiri)
function slug_unik($tabel, $slug, $abaikan_id = 0) {
    $dasar = $slug;
    $i = 2;
    while (ambil_satu("SELECT id FROM $tabel WHERE slug = ? AND id <> ?", [$slug, $abaikan_id])) {
        $slug = $dasar . '-' . $i;
        $i++;
    }
    return $slug;
}

function ringkas($teks, $maks = 140) {
    $teks = trim(preg_replace('/\s+/', ' ', strip_tags($teks)));
    return mb_strimwidth($teks, 0, $maks, '...', 'UTF-8');
}

// Ubah teks biasa (dipisah baris kosong) menjadi paragraf HTML yang aman
function paragraf($teks) {
    $teks = str_replace("\r\n", "\n", $teks);
    $bagian = preg_split("/\n{2,}/", trim($teks));
    $html = '';
    foreach ($bagian as $b) {
        $b = trim($b);
        if ($b !== '') {
            $html .= '<p>' . nl2br(e($b)) . '</p>';
        }
    }
    return $html;
}

function hitung_kata($teks) {
    return str_word_count(strip_tags($teks)) ?: 0;
}

function menit_baca($teks) {
    return max(1, (int)ceil(hitung_kata($teks) / 200));
}

// ---------- Tanggal ----------

function tanggal_indo($waktu, $dengan_jam = false) {
    if (!$waktu) {
        return '-';
    }
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = strtotime($waktu);
    $hasil = date('j', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t);
    if ($dengan_jam) {
        $hasil .= ', ' . date('H:i', $t) . ' WIB';
    }
    return $hasil;
}

function hari_ini_indo() {
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return $hari[(int)date('w')] . ', ' . tanggal_indo(date('Y-m-d'));
}

function waktu_lalu($waktu) {
    if (!$waktu) {
        return '-';
    }
    $selisih = time() - strtotime($waktu);
    if ($selisih < 60)         return 'baru saja';
    if ($selisih < 3600)       return floor($selisih / 60) . ' menit lalu';
    if ($selisih < 86400)      return floor($selisih / 3600) . ' jam lalu';
    if ($selisih < 7 * 86400)  return floor($selisih / 86400) . ' hari lalu';
    return tanggal_indo($waktu);
}

// ---------- Gambar ----------

function gambar_url($nama) {
    if ($nama && is_file(UPLOAD_DIR . $nama)) {
        return UPLOAD_URL . $nama;
    }
    return null;
}

/**
 * Simpan gambar unggahan ke folder uploads/.
 * Hasil: [nama_file, pesan_error]. Kalau tidak ada file yang dipilih, dua-duanya null.
 */
function unggah_gambar($berkas) {
    if (!is_array($berkas) || ($berkas['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($berkas['error'] === UPLOAD_ERR_INI_SIZE || $berkas['error'] === UPLOAD_ERR_FORM_SIZE) {
        return [null, 'Ukuran gambar maksimal 2 MB.'];
    }
    if ($berkas['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Gambar gagal diunggah (kode galat ' . (int)$berkas['error'] . ').'];
    }
    if ($berkas['size'] > MAKS_UKURAN_GAMBAR) {
        return [null, 'Ukuran gambar maksimal 2 MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($berkas['tmp_name']);
    $boleh = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($boleh[$mime]) || !@getimagesize($berkas['tmp_name'])) {
        return [null, 'Format gambar harus JPG, PNG, atau WEBP.'];
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $nama = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $boleh[$mime];
    if (!move_uploaded_file($berkas['tmp_name'], UPLOAD_DIR . $nama)) {
        return [null, 'Gambar tidak bisa disimpan. Cek izin folder uploads/.'];
    }
    return [$nama, null];
}

function hapus_gambar($nama) {
    if ($nama && preg_match('/^[\w.\-]+$/', $nama)) {
        $path = UPLOAD_DIR . $nama;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

// ---------- Komponen tampilan ----------

function thumb_html($b, $kelas = '') {
    $url = !empty($b['gambar']) ? gambar_url($b['gambar']) : null;
    if ($url) {
        return '<img class="thumb ' . e($kelas) . '" src="' . e($url) . '" alt="' . e($b['judul']) . '" loading="lazy">';
    }
    // Belum ada foto: tampilkan blok warna sesuai kategori
    return '<div class="thumb tanpa-gambar warna-' . ((int)$b['kategori_id'] % 5) . ' ' . e($kelas) . '" aria-hidden="true">'
         . '<span>' . e(mb_substr($b['kategori'], 0, 1, 'UTF-8')) . '</span></div>';
}

function link_berita($b) {
    return url('berita.php?slug=' . urlencode($b['slug']));
}

function link_kategori($slug) {
    return url('kategori.php?slug=' . urlencode($slug));
}

function kartu_berita($b, $kelas = '') {
    ob_start(); ?>
    <article class="kartu <?= e($kelas) ?>">
        <a href="<?= e(link_berita($b)) ?>" class="kartu-gambar" tabindex="-1"><?= thumb_html($b) ?></a>
        <div class="kartu-isi">
            <a class="label-kategori" href="<?= e(link_kategori($b['kategori_slug'])) ?>"><?= e($b['kategori']) ?></a>
            <h3><a href="<?= e(link_berita($b)) ?>"><?= e($b['judul']) ?></a></h3>
            <p class="ringkasan"><?= e($b['ringkasan']) ?></p>
            <p class="meta"><?= e($b['penulis']) ?> &middot; <?= e(waktu_lalu($b['terbit_pada'])) ?></p>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

// Navigasi halaman (1 2 3 ...), mempertahankan parameter GET lain seperti q atau slug
function paginasi($halaman, $total_halaman) {
    if ($total_halaman <= 1) {
        return '';
    }
    $query = $_GET;
    $buat = function ($h) use ($query) {
        $query['hal'] = $h;
        return '?' . http_build_query($query);
    };

    $html = '<nav class="paginasi" aria-label="Navigasi halaman">';
    if ($halaman > 1) {
        $html .= '<a href="' . e($buat($halaman - 1)) . '">&laquo; Sebelumnya</a>';
    }
    $awal  = max(1, $halaman - 2);
    $akhir = min($total_halaman, $halaman + 2);
    for ($i = $awal; $i <= $akhir; $i++) {
        $html .= $i === $halaman
            ? '<span class="aktif">' . $i . '</span>'
            : '<a href="' . e($buat($i)) . '">' . $i . '</a>';
    }
    if ($halaman < $total_halaman) {
        $html .= '<a href="' . e($buat($halaman + 1)) . '">Berikutnya &raquo;</a>';
    }
    return $html . '</nav>';
}

function halaman_sekarang() {
    return max(1, (int)($_GET['hal'] ?? 1));
}

// Halaman galat sederhana untuk area admin (404, 403, dan sejenisnya)
function halaman_galat($kode, $pesan) {
    http_response_code($kode);
    exit('<!DOCTYPE html><meta charset="utf-8"><title>' . (int)$kode . '</title>'
       . '<body style="font-family:system-ui,sans-serif;padding:3rem;color:#1d1d1b">'
       . '<h1 style="margin:0 0 .5rem">' . (int)$kode . '</h1><p>' . e($pesan) . '</p>'
       . '<p><a href="' . e(url('admin/')) . '">Kembali ke dashboard</a></p></body>');
}

function tampil_404() {
    http_response_code(404);
    $judul_halaman = 'Halaman tidak ditemukan';
    require __DIR__ . '/header.php';
    echo '<div class="wadah kosong-halaman"><h1>404</h1><p>Halaman yang Anda cari tidak ditemukan atau sudah dihapus.</p>'
       . '<p><a href="' . e(url()) . '">Kembali ke beranda</a></p></div>';
    require __DIR__ . '/footer.php';
    exit;
}
