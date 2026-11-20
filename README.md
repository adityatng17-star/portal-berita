# Berita Nusa

Portal berita sederhana untuk tugas akhir mata kuliah **Pemrograman Web 2**.
Dibuat dengan PHP native (PDO), MySQL, HTML, CSS, dan sedikit JavaScript tanpa framework.

## Fitur

**Pengunjung**
- Beranda dengan headline, daftar berita terbaru, dan sidebar berita terpopuler
- Halaman per kategori, pencarian berita, dan paginasi
- Halaman detail berita dengan jumlah pembaca, estimasi waktu baca, dan berita terkait
- Kolom komentar (tampil setelah disetujui admin)

**Redaksi (admin dan editor)**
- Login dengan password ter-hash
- Dashboard ringkasan
- Tulis, edit, hapus berita dengan unggah foto, status draft/terbit, dan pratinjau draft
- Admin: kelola kategori, moderasi komentar, kelola pengguna
- Editor hanya bisa mengelola berita miliknya sendiri

## Cara menjalankan (XAMPP)

1. Salin folder `portal-berita` ke `C:\xampp\htdocs\`
2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel
3. Buka `http://localhost/phpmyadmin`, pilih tab **Import**, lalu impor file `database/portal_berita.sql`
   (database `portal_berita` dibuat otomatis)
4. Periksa `config/config.php`. Bawaan XAMPP (user `root`, password kosong) sudah sesuai
5. Buka `http://localhost/portal-berita/`
6. Panel redaksi ada di `http://localhost/portal-berita/admin/`

| Peran  | Email                     | Password   |
|--------|---------------------------|------------|
| Admin  | admin@beritanusa.test     | admin123   |
| Editor | editor@beritanusa.test    | editor123  |

Segera ganti password kedua akun ini lewat menu **Pengguna** sebelum dipresentasikan atau diunggah ke hosting.

## Struktur folder

```
portal-berita/
├── index.php, berita.php, kategori.php, cari.php, tentang.php   halaman publik
├── admin/                    panel redaksi (login, dashboard, berita, kategori, komentar, pengguna)
├── config/config.php         pengaturan database, nama situs, koneksi PDO
├── includes/                 fungsi bantu, autentikasi, header/footer, sidebar
├── assets/css, assets/js     tampilan dan skrip
├── uploads/                  foto berita yang diunggah
├── database/portal_berita.sql  skema tabel dan data contoh
└── docs/pembagian-tugas.md   pembagian peran 4 anggota
```

## Yang sudah diterapkan dari sisi keamanan

- Semua query memakai prepared statement (PDO), jadi aman dari SQL injection
- Semua keluaran HTML di-escape dengan `htmlspecialchars` (`e()`), jadi aman dari XSS
- Password disimpan dengan `password_hash` dan dicek dengan `password_verify`
- Token CSRF di setiap form yang mengubah data, logout hanya lewat POST
- Unggahan gambar divalidasi tipe MIME asli, ukuran (maks 2 MB), dan diberi nama acak
- Folder `uploads/` tidak boleh mengeksekusi file PHP (`.htaccess`)
- Komentar memakai moderasi, kolom jebakan (honeypot), dan batas kirim per 20 detik
- Hak akses per peran: halaman admin tertentu dan berita orang lain tidak bisa dibuka editor

## Masalah yang sering muncul

- **CSS tidak tampil / link salah**: `BASE_URL` dihitung otomatis dari lokasi folder. Kalau salah, isi manual di `config/config.php`, misalnya `define('BASE_URL', '/portal-berita');`
- **Koneksi database gagal**: pastikan MySQL menyala dan nama database, user, password di `config/config.php` sudah benar
- **Foto gagal diunggah**: cek `upload_max_filesize` di `php.ini` (minimal 2M) dan pastikan folder `uploads/` bisa ditulis
- **Foto tidak muncul setelah di-commit ke Git**: isi `uploads/` memang diabaikan lewat `.gitignore`, tiap anggota mengunggah foto contoh sendiri
- Semua berita bawaan adalah **data contoh fiktif**. Hapus atau ganti lewat panel admin sebelum dikumpulkan.
