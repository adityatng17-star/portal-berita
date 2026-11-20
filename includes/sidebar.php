<?php
// Sidebar yang dipakai bersama oleh beranda, detail berita, dan halaman kategori
$populer = ambil_semua(SQL_BERITA . " WHERE b.status = 'terbit' ORDER BY b.dilihat DESC, b.id DESC LIMIT 5");
?>
<aside class="sidebar">
    <section class="kotak">
        <h2 class="judul-kotak">Terpopuler</h2>
        <?php if ($populer): ?>
            <ol class="daftar-populer">
                <?php foreach ($populer as $p): ?>
                    <li>
                        <a href="<?= e(link_berita($p)) ?>"><?= e($p['judul']) ?></a>
                        <span class="meta"><?= number_format((int)$p['dilihat'], 0, ',', '.') ?> kali dibaca</span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p class="meta">Belum ada data.</p>
        <?php endif; ?>
    </section>

    <section class="kotak">
        <h2 class="judul-kotak">Kategori</h2>
        <ul class="daftar-kategori">
            <?php foreach (kategori_semua() as $k): ?>
                <li>
                    <a href="<?= e(link_kategori($k['slug'])) ?>"><?= e($k['nama']) ?></a>
                    <span><?= (int)$k['jumlah'] ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</aside>
