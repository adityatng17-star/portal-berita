<?php
// ------------------------------------------------------------
// Konfigurasi utama aplikasi
// ------------------------------------------------------------

date_default_timezone_set('Asia/Jakarta');

define('NAMA_SITUS', 'Berita Nusa');
define('TAGLINE', 'Kabar terkini dari berbagai penjuru');

// Pengaturan database, sesuaikan dengan XAMPP masing-masing
define('DB_HOST', 'localhost');
define('DB_NAME', 'portal_berita');
define('DB_USER', 'root');
define('DB_PASS', '');

// BASE_URL dihitung otomatis dari lokasi folder proyek terhadap document root.
// Contoh: kalau proyek ada di htdocs/portal-berita maka hasilnya "/portal-berita".
// Kalau hasilnya salah, isi manual saja di sini.
$root_proyek = str_replace('\\', '/', dirname(__DIR__));
$doc_root    = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$base        = '';
if ($doc_root !== '' && stripos($root_proyek, $doc_root) === 0) {
    $base = substr($root_proyek, strlen($doc_root));
}
define('BASE_URL', $base);

// Pengaturan unggah gambar
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('MAKS_UKURAN_GAMBAR', 2 * 1024 * 1024); // 2 MB

// Jumlah berita per halaman
define('PER_HALAMAN', 8);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_name('bn_sesi');
    session_start();
}

/**
 * Koneksi database (PDO). Dipanggil lewat db() di mana saja.
 */
function db() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // BN_DSN hanya dipakai saat pengujian otomatis, abaikan saat development biasa
    $dsn  = getenv('BN_DSN') ?: 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $user = getenv('BN_DSN') ? null : DB_USER;
    $pass = getenv('BN_DSN') ? null : DB_PASS;

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Koneksi database gagal. Periksa pengaturan di config/config.php dan pastikan MySQL sudah berjalan.');
    }
    return $pdo;
}
