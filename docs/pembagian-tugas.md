# Pembagian Tugas Kelompok - Berita Nusa

Isi nama dan NIM tiap anggota pada tabel di bawah, lalu samakan juga dengan data di `tentang.php`.

| Anggota | Peran |
|---------|-------|
| Anggota 1 (nama / NIM) | Project Manager & Backend Developer |
| Anggota 2 (nama / NIM) | Frontend Developer |
| Anggota 3 (nama / NIM) | Backend Developer (Panel Admin) |
| Anggota 4 (nama / NIM) | Fitur Interaksi, QA & Dokumentasi |

---

## Anggota 1 - Project Manager & Backend Developer

**Tanggung jawab:** fondasi sistem. Anggota lain bergantung pada bagian ini, jadi kerjakan lebih dulu.

- Rancangan database (ERD, relasi antar tabel, normalisasi) dan file SQL
- Koneksi database PDO dan konfigurasi aplikasi
- Fungsi bantu umum: escape output, slug, tanggal Indonesia, flash message, CSRF
- Sistem login, logout, session, dan pembagian hak akses admin/editor
- Manajemen pengguna di panel admin
- Mengatur repository Git dan membagi tugas

**File:** `config/config.php`, `includes/init.php`, `includes/functions.php`, `includes/auth.php`,
`database/portal_berita.sql`, `admin/login.php`, `admin/logout.php`, `admin/pengguna.php`

**Bahan presentasi:** ERD 4 tabel, alasan memakai PDO dan prepared statement, alur login, bedanya peran admin dan editor.

---

## Anggota 2 - Frontend Developer

**Tanggung jawab:** semua yang dilihat pengunjung.

- Tata letak halaman publik (masthead, navigasi, headline, kartu berita, sidebar, footer)
- CSS responsif untuk desktop dan HP
- Beranda, halaman kategori, dan halaman tentang
- JavaScript: menu mobile, penghitung karakter

**File:** `includes/header.php`, `includes/footer.php`, `includes/sidebar.php`, `index.php`, `kategori.php`,
`tentang.php`, `assets/css/style.css`, `assets/js/main.js`

**Bahan presentasi:** pemilihan warna dan font, cara layout berubah di layar kecil (media query), pengganti foto jika berita belum punya gambar.

---

## Anggota 3 - Backend Developer (Panel Admin)

**Tanggung jawab:** semua alat redaksi untuk mengelola konten.

- Dashboard dengan statistik
- CRUD berita: tambah, edit, hapus, status draft/terbit
- Unggah dan validasi gambar (tipe, ukuran, nama acak)
- CRUD kategori dengan pengecekan relasi sebelum dihapus
- Tampilan panel admin (CSS dan JavaScript)

**File:** `admin/index.php`, `admin/berita.php`, `admin/berita_form.php`, `admin/berita_hapus.php`,
`admin/kategori.php`, `includes/admin_header.php`, `includes/admin_footer.php`,
`assets/css/admin.css`, `assets/js/admin.js`

**Bahan presentasi:** alur menulis berita sampai terbit, keamanan unggah file, kenapa slug tidak berubah saat judul diedit.

---

## Anggota 4 - Fitur Interaksi, QA & Dokumentasi

**Tanggung jawab:** fitur yang melibatkan pengunjung, serta memastikan semuanya berjalan.

- Halaman detail berita: hitung pembaca, estimasi waktu baca, berita terkait
- Komentar: formulir, validasi, honeypot, batas kirim, dan moderasi di panel admin
- Pencarian berita dan paginasi
- Pengujian seluruh fitur (tabel skenario uji) dan pencatatan bug
- Laporan, dokumentasi, dan penyusunan data berita asli

**File:** `berita.php`, `cari.php`, `admin/komentar.php`, `README.md`, `docs/`

**Bahan presentasi:** alur komentar dari pengunjung sampai tampil, tabel hasil uji, demo pencarian.

---

## Saran alur kerja (Git)

1. Satu repository bersama. Anggota 1 membuat repository dan commit awal.
2. Setiap anggota bekerja di branch sendiri (`fitur-frontend`, `fitur-admin`, dan seterusnya), lalu digabung lewat pull request.
3. Commit kecil dengan pesan yang jelas, misalnya `tambah validasi ukuran gambar`.
4. Pertemuan singkat tiap beberapa hari untuk mengecek apakah bagian tiap orang sudah saling terhubung.

## Daftar cek sebelum dikumpulkan

- [ ] Nama anggota dan NIM di `tentang.php` sudah diisi
- [ ] Nama situs dan tagline di `config/config.php` sudah disesuaikan
- [ ] Data berita contoh diganti atau ditambah dengan konten kelompok sendiri, lengkap dengan foto
- [ ] Password akun admin dan editor sudah diganti
- [ ] Setiap anggota paham dan bisa menjelaskan kode bagiannya sendiri
- [ ] Tabel skenario pengujian sudah diisi
