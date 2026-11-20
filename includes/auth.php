<?php
// ------------------------------------------------------------
// Autentikasi pengguna (admin dan editor)
// ------------------------------------------------------------

function pengguna_sekarang() {
    static $cache = false;
    if ($cache !== false) {
        return $cache;
    }
    if (empty($_SESSION['pengguna_id'])) {
        return $cache = null;
    }
    $cache = ambil_satu(
        'SELECT id, nama, email, peran FROM pengguna WHERE id = ?',
        [$_SESSION['pengguna_id']]
    );
    if (!$cache) {
        unset($_SESSION['pengguna_id']);
    }
    return $cache;
}

function adalah_admin() {
    $p = pengguna_sekarang();
    return $p && $p['peran'] === 'admin';
}

function login($email, $password) {
    $p = ambil_satu('SELECT * FROM pengguna WHERE email = ?', [trim($email)]);
    if (!$p || !password_verify($password, $p['password'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['pengguna_id'] = (int)$p['id'];
    return true;
}

function logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $c = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $c['path'], $c['domain'], $c['secure'], $c['httponly']);
    }
    session_destroy();
}

function wajib_login() {
    if (!pengguna_sekarang()) {
        redirect('admin/login.php');
    }
}

function wajib_admin() {
    wajib_login();
    if (!adalah_admin()) {
        http_response_code(403);
        exit('<!DOCTYPE html><meta charset="utf-8"><title>Akses ditolak</title>'
           . '<p style="font-family:sans-serif;padding:2rem">Halaman ini hanya bisa dibuka oleh admin. '
           . '<a href="' . e(url('admin/')) . '">Kembali ke dashboard</a></p>');
    }
}
