-- ============================================================
-- Database Portal Berita (Berita Nusa)
-- Cara pakai: buka phpMyAdmin > Import > pilih file ini
-- Akun contoh ada di bagian paling bawah file
-- ============================================================

CREATE DATABASE IF NOT EXISTS portal_berita
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_berita;

DROP TABLE IF EXISTS komentar;
DROP TABLE IF EXISTS berita;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS pengguna;

CREATE TABLE pengguna (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama         VARCHAR(80)  NOT NULL,
  email        VARCHAR(120) NOT NULL,
  password     VARCHAR(255) NOT NULL,
  peran        ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  dibuat_pada  DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_pengguna_email (email)
) ENGINE=InnoDB;

CREATE TABLE kategori (
  id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama  VARCHAR(60) NOT NULL,
  slug  VARCHAR(80) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_kategori_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE berita (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kategori_id   INT UNSIGNED NOT NULL,
  pengguna_id   INT UNSIGNED NOT NULL,
  judul         VARCHAR(200) NOT NULL,
  slug          VARCHAR(220) NOT NULL,
  ringkasan     VARCHAR(300) NOT NULL,
  isi           MEDIUMTEXT NOT NULL,
  gambar        VARCHAR(120) DEFAULT NULL,
  status        ENUM('draft','terbit') NOT NULL DEFAULT 'draft',
  dilihat       INT UNSIGNED NOT NULL DEFAULT 0,
  dibuat_pada   DATETIME NOT NULL,
  diubah_pada   DATETIME NOT NULL,
  terbit_pada   DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_berita_slug (slug),
  KEY idx_berita_status_terbit (status, terbit_pada),
  CONSTRAINT fk_berita_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id),
  CONSTRAINT fk_berita_pengguna FOREIGN KEY (pengguna_id) REFERENCES pengguna(id)
) ENGINE=InnoDB;

CREATE TABLE komentar (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  berita_id    INT UNSIGNED NOT NULL,
  nama         VARCHAR(60)  NOT NULL,
  email        VARCHAR(120) DEFAULT NULL,
  isi          VARCHAR(1000) NOT NULL,
  status       ENUM('menunggu','tampil') NOT NULL DEFAULT 'menunggu',
  dibuat_pada  DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_komentar_berita (berita_id, status),
  CONSTRAINT fk_komentar_berita FOREIGN KEY (berita_id) REFERENCES berita(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Data awal
-- ------------------------------------------------------------

-- Login admin  : admin@beritanusa.test  / admin123
-- Login editor : editor@beritanusa.test / editor123
INSERT INTO pengguna (id, nama, email, password, peran, dibuat_pada) VALUES
(1, 'Administrator', 'admin@beritanusa.test', '$2y$10$BkAaiVUXq.blcTidA5bm2e9USSJg7wWO8zRaFdnbeOLKM8Th72oVW', 'admin',  '2026-11-01 08:00:00'),
(2, 'Rina Pratiwi',  'editor@beritanusa.test', '$2y$10$oCQMXQM7vC0GiZNAUMsOs.P80k.WaXTng4j/8AACwDqSs1mM3I2C.', 'editor', '2026-11-01 08:05:00');

INSERT INTO kategori (id, nama, slug) VALUES
(1, 'Nasional',   'nasional'),
(2, 'Ekonomi',    'ekonomi'),
(3, 'Teknologi',  'teknologi'),
(4, 'Olahraga',   'olahraga'),
(5, 'Pendidikan', 'pendidikan');

-- Catatan: seluruh berita di bawah adalah data contoh (fiktif) untuk
-- keperluan pengembangan. Hapus atau ganti lewat panel admin.
INSERT INTO berita (id, kategori_id, pengguna_id, judul, slug, ringkasan, isi, gambar, status, dilihat, dibuat_pada, diubah_pada, terbit_pada) VALUES
(1, 1, 2,
 'Pemkot Tambah Dua Jalur Sepeda di Kawasan Pusat Kota',
 'pemkot-tambah-dua-jalur-sepeda-di-kawasan-pusat-kota',
 'Dua ruas jalan di pusat kota akan dilengkapi jalur sepeda terpisah mulai awal tahun depan. Pengerjaan dibagi dalam dua tahap.',
 'Pemerintah kota berencana membangun dua jalur sepeda baru di kawasan pusat kota. Jalur pertama menghubungkan alun-alun dengan terminal, sedangkan jalur kedua membentang di sepanjang jalan utama menuju kampus.\n\nKepala Dinas Perhubungan setempat menyebutkan pengerjaan akan dilakukan bertahap agar arus lalu lintas tidak terganggu. Tahap pertama ditargetkan selesai dalam tiga bulan, sementara tahap kedua menunggu hasil evaluasi.\n\nSejumlah komunitas pesepeda menyambut baik rencana ini, namun meminta pemerintah memastikan jalur tidak dipakai untuk parkir kendaraan. Mereka juga mengusulkan adanya penanda yang jelas di setiap persimpangan.\n\nWarga dapat menyampaikan masukan melalui kanal pengaduan resmi pemerintah kota hingga akhir bulan.',
 NULL, 'terbit', 412, '2026-11-19 07:30:00', '2026-11-19 07:30:00', '2026-11-19 07:30:00'),
(2, 2, 2,
 'Harga Cabai Berangsur Turun Setelah Pasokan dari Sentra Produksi Lancar',
 'harga-cabai-berangsur-turun-setelah-pasokan-lancar',
 'Pedagang di pasar tradisional melaporkan harga cabai rawit turun dalam sepekan terakhir seiring meningkatnya pasokan.',
 'Harga cabai rawit di sejumlah pasar tradisional turun dalam sepekan terakhir. Pedagang menyebut pasokan dari daerah sentra produksi kembali lancar setelah panen raya.\n\nSeorang pedagang di pasar induk mengatakan harga yang sebelumnya sempat melonjak kini mulai mendekati harga normal. Ia berharap kondisi ini bertahan karena daya beli pembeli ikut membaik.\n\nDinas perdagangan setempat akan terus memantau harga bahan pokok dan menggelar pasar murah apabila diperlukan.',
 NULL, 'terbit', 298, '2026-11-18 09:10:00', '2026-11-18 09:10:00', '2026-11-18 09:10:00'),
(3, 3, 2,
 'Mahasiswa Kembangkan Aplikasi Pemantau Kualitas Udara Berbiaya Rendah',
 'mahasiswa-kembangkan-aplikasi-pemantau-kualitas-udara',
 'Tim mahasiswa teknik merancang alat sensor sederhana yang datanya bisa dilihat lewat aplikasi web.',
 'Sekelompok mahasiswa teknik mengembangkan alat pemantau kualitas udara yang dirakit dari komponen murah. Data dari sensor dikirim ke server dan ditampilkan dalam bentuk grafik di aplikasi web.\n\nMenurut ketua tim, alat ini dibuat agar bisa dipasang di banyak titik tanpa biaya besar. Prototipe pertama sudah diuji di lingkungan kampus selama dua bulan.\n\nMereka berencana membuka kode program agar dapat dipelajari mahasiswa lain, sambil terus memperbaiki akurasi pembacaan sensor.',
 NULL, 'terbit', 535, '2026-11-17 13:45:00', '2026-11-17 13:45:00', '2026-11-17 13:45:00'),
(4, 4, 1,
 'Tim Voli Putri Kota Lolos ke Final Kejuaraan Antarkota',
 'tim-voli-putri-kota-lolos-ke-final-kejuaraan-antarkota',
 'Setelah bermain lima set, tim voli putri berhasil menyingkirkan juara bertahan di babak semifinal.',
 'Tim voli putri kota memastikan tempat di final kejuaraan antarkota setelah menang dalam pertandingan lima set yang berlangsung ketat. Mereka menyingkirkan juara bertahan dengan skor akhir 3-2.\n\nPelatih tim mengaku puas dengan kekompakan pemainnya, terutama saat tertinggal di set ketiga. Menurutnya, perubahan formasi di pertengahan laga menjadi kunci kemenangan.\n\nFinal dijadwalkan berlangsung akhir pekan ini di gelanggang olahraga kota dan terbuka untuk umum.',
 NULL, 'terbit', 187, '2026-11-16 20:15:00', '2026-11-16 20:15:00', '2026-11-16 20:15:00'),
(5, 5, 2,
 'Perpustakaan Daerah Perpanjang Jam Layanan Selama Masa Ujian',
 'perpustakaan-daerah-perpanjang-jam-layanan-selama-masa-ujian',
 'Pengunjung kini bisa memanfaatkan ruang baca hingga pukul sembilan malam pada hari kerja.',
 'Perpustakaan daerah memperpanjang jam layanan selama masa ujian akhir semester. Ruang baca dan layanan peminjaman kini dibuka hingga pukul sembilan malam pada hari kerja.\n\nKebijakan ini diambil setelah banyak pelajar dan mahasiswa meminta tempat belajar yang tenang pada malam hari. Pihak perpustakaan juga menambah jumlah kursi dan colokan listrik di lantai dua.\n\nJam layanan akan kembali normal setelah masa ujian berakhir pertengahan Desember.',
 NULL, 'terbit', 143, '2026-11-15 10:00:00', '2026-11-15 10:00:00', '2026-11-15 10:00:00'),
(6, 1, 1,
 'Warga Bantaran Sungai Ikuti Simulasi Evakuasi Banjir',
 'warga-bantaran-sungai-ikuti-simulasi-evakuasi-banjir',
 'Ratusan warga berlatih menuju titik kumpul dalam simulasi yang digelar menjelang puncak musim hujan.',
 'Ratusan warga yang tinggal di bantaran sungai mengikuti simulasi evakuasi banjir pada Minggu pagi. Latihan ini digelar menjelang puncak musim hujan yang diperkirakan terjadi bulan depan.\n\nPeserta berlatih menyelamatkan dokumen penting, membantu lansia dan anak-anak, lalu bergerak menuju titik kumpul di lapangan kelurahan. Petugas badan penanggulangan bencana mendampingi sepanjang kegiatan.\n\nMenurut petugas, simulasi seperti ini perlu rutin dilakukan supaya warga tidak panik ketika air benar-benar naik.',
 NULL, 'terbit', 226, '2026-11-14 11:20:00', '2026-11-14 11:20:00', '2026-11-14 11:20:00'),
(7, 2, 2,
 'UMKM Kuliner Mulai Terima Pembayaran Digital di Pasar Malam',
 'umkm-kuliner-mulai-terima-pembayaran-digital-di-pasar-malam',
 'Puluhan pedagang makanan di pasar malam kini memasang kode QR untuk pembayaran nontunai.',
 'Puluhan pedagang makanan di pasar malam mulai menerima pembayaran digital melalui kode QR. Program ini didampingi oleh dinas koperasi dan UMKM yang membantu proses pendaftaran.\n\nSalah seorang pedagang mie ayam mengaku awalnya ragu, tetapi kini merasa terbantu karena tidak perlu repot mencari uang kembalian. Pembeli muda pun disebut lebih sering bertransaksi.\n\nPendampingan akan dilanjutkan dengan pelatihan pencatatan keuangan sederhana bagi para pelaku usaha.',
 NULL, 'terbit', 164, '2026-11-13 18:40:00', '2026-11-13 18:40:00', '2026-11-13 18:40:00'),
(8, 3, 2,
 'Sekolah Menengah Mulai Ajarkan Dasar Pemrograman Lewat Ekstrakurikuler',
 'sekolah-menengah-mulai-ajarkan-dasar-pemrograman',
 'Kelas pemrograman dasar dibuka sebagai ekstrakurikuler dan diikuti puluhan siswa kelas sepuluh.',
 'Sebuah sekolah menengah atas membuka kelas pemrograman dasar sebagai kegiatan ekstrakurikuler. Puluhan siswa kelas sepuluh mendaftar pada pekan pertama.\n\nMateri diawali dengan logika berpikir dan algoritma sederhana sebelum masuk ke bahasa pemrograman. Pengajarnya adalah guru informatika yang dibantu alumni dari perguruan tinggi.\n\nPihak sekolah berharap kegiatan ini dapat menumbuhkan minat siswa terhadap bidang teknologi sejak dini.',
 NULL, 'terbit', 121, '2026-11-12 14:05:00', '2026-11-12 14:05:00', '2026-11-12 14:05:00'),
(9, 4, 1,
 'Lari 10K Akhir Pekan Diikuti Ribuan Peserta',
 'lari-10k-akhir-pekan-diikuti-ribuan-peserta',
 'Peserta dari berbagai kota memadati rute lari yang melintasi tepi danau dan pusat kota.',
 'Ribuan peserta dari berbagai kota memadati acara lari 10K yang digelar Minggu pagi. Rute melintasi tepi danau, taman kota, dan berakhir di depan balai kota.\n\nPanitia menyiapkan sejumlah titik air minum serta petugas medis di sepanjang rute. Beberapa ruas jalan ditutup sementara dan pengendara diimbau mencari jalur alternatif.\n\nSelain kategori umum, panitia juga membuka kategori pelajar dan pelari senior.',
 NULL, 'terbit', 98, '2026-11-10 06:50:00', '2026-11-10 06:50:00', '2026-11-10 06:50:00'),
(10, 5, 2,
 'Seminar Literasi Digital Digelar untuk Mahasiswa Baru',
 'seminar-literasi-digital-digelar-untuk-mahasiswa-baru',
 'Kegiatan membahas cara memilah informasi dan menjaga keamanan akun di dunia maya.',
 'Seminar literasi digital digelar untuk mahasiswa baru di aula kampus. Narasumber membahas cara memeriksa kebenaran informasi serta menjaga keamanan akun di dunia maya.\n\nPeserta juga diajak berlatih mengenali pesan penipuan yang kerap beredar lewat aplikasi percakapan. Sesi tanya jawab berlangsung hangat dan melebihi waktu yang dijadwalkan.\n\nPenyelenggara berencana membuat seri lanjutan dengan topik etika bermedia sosial.',
 NULL, 'terbit', 76, '2026-11-08 09:30:00', '2026-11-08 09:30:00', '2026-11-08 09:30:00'),
(11, 3, 2,
 'Rencana Pembaruan Layanan Aplikasi Kelurahan (Draf)',
 'rencana-pembaruan-layanan-aplikasi-kelurahan',
 'Draf tulisan yang belum siap terbit.',
 'Masih dalam tahap penulisan. Menunggu konfirmasi dari narasumber.',
 NULL, 'draft', 0, '2026-11-19 16:00:00', '2026-11-19 16:00:00', NULL);

INSERT INTO komentar (berita_id, nama, email, isi, status, dibuat_pada) VALUES
(1, 'Dimas', 'dimas@contoh.id', 'Semoga benar-benar dikerjakan dan jalurnya tidak dipakai parkir motor.', 'tampil', '2026-11-19 09:12:00'),
(1, 'Wulan', NULL, 'Setuju. Sekalian tambah rambu di perempatan dekat terminal ya.', 'tampil', '2026-11-19 10:40:00'),
(3, 'Fajar', 'fajar@contoh.id', 'Keren, ditunggu kode programnya dibuka.', 'tampil', '2026-11-17 19:05:00'),
(2, 'Bu Sari', NULL, 'Alhamdulillah, semoga harga tetap stabil sampai akhir tahun.', 'menunggu', '2026-11-18 12:30:00');
