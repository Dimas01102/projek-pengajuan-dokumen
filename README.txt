Project Pengajuan Dokumen

Sistem Project Pengajuan Dokumen adalah aplikasi berbasis web yang digunakan untuk proses pengajuan, validasi, dan pencetakan dokumen oleh tiga jenis pengguna: Admin, Petugas, dan Warga. Sistem ini menyediakan alur kerja terstruktur mulai dari pendaftaran akun, pengajuan dokumen, verifikasi, hingga pencetakan dokumen resmi dalam bentuk PDF.

Aplikasi ini dibangun menggunakan PHP Native dengan arsitektur terstruktur, didukung oleh integrasi PDF Generator, sistem error handling khusus, serta implementasi keamanan dasar seperti pembatasan akses dan proteksi direktori logs.

Fitur Utama
1. Hak Akses Multi-Role

Admin
Mengelola seluruh data, user, riwayat pengajuan, dan master data.

Petugas
Mengelola validasi dokumen yang diajukan oleh warga.

Warga
Melakukan pengajuan dokumen dan mencetak hasil setelah disetujui.

2. Pengajuan Dokumen

Formulir lengkap untuk pengajuan.

Upload berkas persyaratan.

Status pengajuan real-time.

3. Validasi & Persetujuan

Petugas melakukan pengecekan dokumen.

Admin menerima notifikasi pengajuan baru.

Sistem mencatat seluruh riwayat perubahan status.

4. Pencetakan Dokumen (PDF)

Menggunakan TCPDF untuk membuat dokumen resmi dengan format yang telah distandarkan.

5. Sistem Error Handler Khusus

Menangani fatal error, exception, dan runtime error.

Redirect otomatis ke halaman 500 Internal Server Error.

Menyimpan log detail di folder logs/ yang dilindungi .htaccess.

6. Dashboard Per Role

Dashboard Admin

Dashboard Petugas

Dashboard Warga
Masing-masing dilengkapi informasi sesuai peran.

Teknologi yang Digunakan
Komponen	Teknologi
Bahasa Pemrograman	PHP 8+
Web Server	Apache (XAMPP)
Database	MySQL
PDF Generator	TCPDF
Frontend	HTML, CSS, Bootstrap
Session & Auth	PHP Native
Error Logging	Custom Error Handler
Struktur Folder (Ringkasan)
project_pengajuan_dokumen/
│
├── admin/                 # Modul Admin
├── handler/               # Handler request (Admin, Petugas, Warga)
├── includes/              # File global, koneksi, auth, error handler
├── logs/                  # Error log (diproteksi .htaccess)
│   └── php_errors.log
│
├── petugas/               # Modul Petugas
├── warga/                 # Modul Warga
│
├── vendor/                # Library TCPDF
└── ...

Instalasi
1. Siapkan Lingkungan

Install XAMPP

Pastikan Apache & MySQL berjalan

2. Extract Project

Tempatkan folder ini ke:

C:\xampp\htdocs\project_pengajuan_dokumen

3. Import Database

Buka phpMyAdmin

Buat database baru, misalnya:

project_dokumen


Import file .sql yang tersedia (jika ada)

4. Konfigurasi Koneksi Database

Edit file:

includes/config.php


Isi dengan parameter server lokal Anda:

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'project_dokumen';

Menjalankan Aplikasi

Buka browser dan akses:

http://localhost/project_pengajuan_dokumen/


Login sesuai role.
Jika pertama kali, buat akun atau gunakan data default (jika tersedia).

Keamanan & Error Handling
1. Error Handler Otomatis

Sistem menangani:

Fatal error

Parse error

Uncaught exception

User error

Semua dikirim ke:

logs/php_errors.log

2. Proteksi Log

File .htaccess dalam logs/ mencegah akses langsung dari browser.

3. Timeout Buffer & HTTP Code

Jika kesalahan serius terjadi:

Output buffer dibersihkan

HTTP 500 dikirim

Pengguna diarahkan ke 500.php tanpa menampilkan detail error sensitif

Lisensi

Project ini bersifat privat untuk kebutuhan internal dan tidak diperbolehkan disalin tanpa izin.

Kontak

Jika membutuhkan bantuan atau pengembangan lanjutan, silakan hubungi Developer.