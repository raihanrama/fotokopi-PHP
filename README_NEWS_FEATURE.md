# Fitur Berita - CepatCopy JWP

## Deskripsi
Fitur berita memungkinkan admin untuk menambahkan, mengedit, dan menghapus berita yang akan ditampilkan kepada user di halaman About. Berita ini dapat berisi informasi tentang layanan, promosi, atau berita terkait bisnis fotokopi.

## File yang Dibuat

### 1. Database
- `create_news_table.sql` - File SQL untuk membuat tabel berita dan about
- `setup_news_tables.php` - Script PHP untuk setup database

### 2. Admin Panel
- `admin/manage_news.php` - Halaman untuk mengelola berita (list, hapus)
- `admin/add_news.php` - Halaman untuk menambah berita baru
- `admin/edit_news.php` - Halaman untuk mengedit berita
- `admin/manage_about.php` - Halaman untuk mengelola informasi about

### 3. User Interface
- `about.php` - Halaman about untuk user yang menampilkan informasi bisnis dan berita terbaru

## Cara Setup

### 1. Jalankan Setup Database
Akses file `setup_news_tables.php` melalui browser:
```
http://localhost/cepatcopy_JWP/setup_news_tables.php
```

### 2. Pastikan Folder Uploads
Pastikan folder `uploads/` memiliki permission write untuk upload gambar berita.

## Fitur Admin

### Kelola Berita (`admin/manage_news.php`)
- Melihat daftar semua berita
- Menambah berita baru
- Mengedit berita
- Menghapus berita
- Melihat status berita (published/draft)

### Tambah Berita (`admin/add_news.php`)
- Form untuk menambah berita baru
- Upload gambar berita
- Set status berita (published/draft)
- Validasi input

### Edit Berita (`admin/edit_news.php`)
- Form untuk mengedit berita
- Update gambar berita
- Preview gambar saat ini
- Validasi input

### Kelola About (`admin/manage_about.php`)
- Edit informasi bisnis
- Edit visi dan misi
- Edit alamat, telepon, email
- Preview perubahan

## Fitur User

### Halaman About (`about.php`)
- Menampilkan informasi bisnis
- Menampilkan visi dan misi
- Menampilkan kontak (alamat, telepon, email)
- Menampilkan berita terbaru (6 berita terbaru)
- Modal untuk membaca berita lengkap

## Struktur Database

### Tabel `news`
```sql
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('published','draft') NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`)
);
```

### Tabel `about_info`
```sql
CREATE TABLE `about_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `vision` text,
  `mission` text,
  `address` text,
  `phone` varchar(50),
  `email` varchar(100),
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

## Navigasi

### Admin Dashboard
- Link "Kelola Berita" ditambahkan ke sidebar admin
- Link "Kelola About" ditambahkan ke sidebar admin

### User Navigation
- Link "Tentang Kami" ditambahkan ke navbar user

## Keamanan
- Validasi input untuk mencegah XSS
- Validasi file upload (hanya gambar)
- Sanitasi output untuk mencegah XSS
- Session check untuk admin panel

## Responsive Design
- Menggunakan Bootstrap 5
- Responsive untuk mobile dan desktop
- Modern UI dengan gradient dan shadow
- Smooth animations dan transitions

## Cara Penggunaan

### Untuk Admin:
1. Login ke admin panel
2. Klik "Kelola Berita" di sidebar
3. Klik "Tambah Berita Baru" untuk menambah berita
4. Isi form dan upload gambar (opsional)
5. Set status "Published" untuk menampilkan ke user
6. Klik "Kelola About" untuk mengedit informasi bisnis

### Untuk User:
1. Klik "Tentang Kami" di navbar
2. Lihat informasi bisnis dan berita terbaru
3. Klik "Baca Selengkapnya" untuk membaca berita lengkap

## Catatan Penting
- Pastikan folder `uploads/` memiliki permission write
- Gambar berita akan disimpan di folder `uploads/`
- Berita dengan status "draft" tidak akan ditampilkan ke user
- Hanya berita dengan status "published" yang ditampilkan di halaman about 