<?php
// Variabel opsional yang bisa di-set oleh halaman sebelum include header:
//   $judul_halaman, $deskripsi_halaman, $nav_aktif
$judul_halaman = $judul_halaman ?? null;
$judul_tab     = $judul_halaman ? $judul_halaman . ' - ' . NAMA_SITUS : NAMA_SITUS . ' - ' . TAGLINE;
$deskripsi     = $deskripsi_halaman ?? TAGLINE;
$nav_aktif     = $nav_aktif ?? '';
$pengguna      = pengguna_sekarang();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_tab) ?></title>
    <meta name="description" content="<?= e($deskripsi) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>

<div class="topbar">
    <div class="wadah topbar-isi">
        <span><?= e(hari_ini_indo()) ?></span>
        <span class="topbar-kanan">
            <?php if ($pengguna): ?>
                <a href="<?= e(url('admin/')) ?>">Dashboard (<?= e($pengguna['nama']) ?>)</a>
            <?php else: ?>
                <a href="<?= e(url('admin/login.php')) ?>">Masuk redaksi</a>
            <?php endif; ?>
        </span>
    </div>
</div>

<header class="masthead">
    <div class="wadah">
        <a class="nama-situs" href="<?= e(url()) ?>"><?= e(NAMA_SITUS) ?></a>
        <p class="tagline"><?= e(TAGLINE) ?></p>
    </div>
</header>

<nav class="navbar" aria-label="Kategori">
    <div class="wadah navbar-isi">
        <button type="button" class="tombol-menu" id="tombolMenu" aria-expanded="false" aria-controls="daftarMenu">Menu</button>
        <ul class="menu" id="daftarMenu">
            <li><a href="<?= e(url()) ?>" class="<?= $nav_aktif === 'beranda' ? 'aktif' : '' ?>">Beranda</a></li>
            <?php foreach (kategori_semua() as $k): ?>
                <li><a href="<?= e(link_kategori($k['slug'])) ?>" class="<?= $nav_aktif === $k['slug'] ? 'aktif' : '' ?>"><?= e($k['nama']) ?></a></li>
            <?php endforeach; ?>
        </ul>
        <form class="form-cari" action="<?= e(url('cari.php')) ?>" method="get" role="search">
            <label for="q" class="sr-only">Cari berita</label>
            <input type="search" id="q" name="q" placeholder="Cari berita" value="<?= e($_GET['q'] ?? '') ?>" maxlength="80">
            <button type="submit">Cari</button>
        </form>
    </div>
</nav>

<main>
