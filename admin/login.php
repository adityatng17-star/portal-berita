<?php
require __DIR__ . '/../includes/init.php';

if (pengguna_sekarang()) {
    redirect('admin/');
}

$galat = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $galat = 'Email dan password wajib diisi.';
    } elseif (login($email, $password)) {
        redirect('admin/');
    } else {
        usleep(400000); // jeda kecil supaya tebak-tebakan password tidak terlalu cepat
        $galat = 'Email atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Redaksi <?= e(NAMA_SITUS) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="halaman-login">

<main class="kotak-login">
    <a class="login-nama" href="<?= e(url()) ?>"><?= e(NAMA_SITUS) ?></a>
    <p class="login-sub">Masuk ke ruang redaksi</p>

    <?php if (isset($_GET['keluar'])): ?>
        <div class="pesan pesan-info">Anda sudah keluar.</div>
    <?php endif; ?>
    <?php if ($galat): ?>
        <div class="pesan pesan-galat"><?= e($galat) ?></div>
    <?php endif; ?>

    <form method="post" class="form">
        <?= csrf_field() ?>
        <div class="baris-form">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
        </div>
        <div class="baris-form">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="tombol tombol-penuh">Masuk</button>
    </form>

    <p class="login-kaki"><a href="<?= e(url()) ?>">&larr; Kembali ke situs</a></p>
</main>

</body>
</html>
