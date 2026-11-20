<?php
// Kerangka halaman admin. Set $judul_admin dan $menu_aktif sebelum meng-include file ini.
$pengguna    = pengguna_sekarang();
$judul_admin = $judul_admin ?? 'Dashboard';
$menu_aktif  = $menu_aktif ?? '';
$menunggu    = adalah_admin() ? hitung("SELECT COUNT(*) FROM komentar WHERE status = 'menunggu'") : 0;

$menu = [
    'dashboard' => ['Dashboard', 'admin/', false],
    'berita'    => ['Berita', 'admin/berita.php', false],
    'kategori'  => ['Kategori', 'admin/kategori.php', true],
    'komentar'  => ['Komentar', 'admin/komentar.php', true],
    'pengguna'  => ['Pengguna', 'admin/pengguna.php', true],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_admin) ?> - Redaksi <?= e(NAMA_SITUS) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="admin">

<aside class="samping">
    <a class="samping-nama" href="<?= e(url('admin/')) ?>"><?= e(NAMA_SITUS) ?><small>Ruang redaksi</small></a>

    <nav>
        <ul>
            <?php foreach ($menu as $kunci => [$label, $tujuan, $khusus_admin]): ?>
                <?php if ($khusus_admin && !adalah_admin()) continue; ?>
                <li>
                    <a href="<?= e(url($tujuan)) ?>" class="<?= $menu_aktif === $kunci ? 'aktif' : '' ?>">
                        <?= e($label) ?>
                        <?php if ($kunci === 'komentar' && $menunggu > 0): ?>
                            <span class="lencana-angka"><?= $menunggu ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="samping-bawah">
        <a href="<?= e(url()) ?>" target="_blank" rel="noopener">Lihat situs &nearr;</a>
        <p class="samping-pengguna">
            <?= e($pengguna['nama']) ?><br>
            <small><?= e(ucfirst($pengguna['peran'])) ?></small>
        </p>
        <form method="post" action="<?= e(url('admin/logout.php')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="tombol-keluar">Keluar</button>
        </form>
    </div>
</aside>

<div class="konten">
    <header class="konten-kepala">
        <h1><?= e($judul_admin) ?></h1>
        <?php if (!empty($aksi_kepala)): ?><div><?= $aksi_kepala ?></div><?php endif; ?>
    </header>

    <?= flash_tampil() ?>
