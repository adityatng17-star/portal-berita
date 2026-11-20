</main>

<footer class="footer">
    <div class="wadah footer-isi">
        <div>
            <p class="footer-nama"><?= e(NAMA_SITUS) ?></p>
            <p class="footer-kecil"><?= e(TAGLINE) ?>.</p>
        </div>
        <div>
            <p class="footer-judul">Kategori</p>
            <ul>
                <?php foreach (kategori_semua() as $k): ?>
                    <li><a href="<?= e(link_kategori($k['slug'])) ?>"><?= e($k['nama']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <p class="footer-judul">Informasi</p>
            <ul>
                <li><a href="<?= e(url('tentang.php')) ?>">Tentang &amp; Redaksi</a></li>
                <li><a href="<?= e(url('admin/login.php')) ?>">Masuk redaksi</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bawah">
        <div class="wadah">&copy; <?= date('Y') ?> <?= e(NAMA_SITUS) ?>. Proyek tugas akhir Pemrograman Web 2.</div>
    </div>
</footer>

<script src="<?= e(url('assets/js/main.js')) ?>"></script>
</body>
</html>
