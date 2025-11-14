# 📚 PANDUAN INSTALASI APLIKASI PENGAJUAN DOKUMEN WARGA DIGITAL

## 🎯 Ringkasan Aplikasi

Aplikasi web untuk pengelolaan pengajuan dokumen kependudukan dengan fitur:
- ✅ 3 Role User (Admin, Petugas, Warga)
- ✅ 3 Jenis Dokumen (SKTM, SKU, SKD)
- ✅ Form Dinamis per Jenis Dokumen
- ✅ Upload Berkas Pendukung
- ✅ Validasi oleh Petugas
- ✅ Download Surat PDF
- ✅ Notifikasi SweetAlert2
- ✅ Design Modern Bootstrap 5

---

## 🛠️ INSTALASI STEP-BY-STEP

### LANGKAH 1: Persiapan Server

**Requirements:**
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache/Nginx Web Server
- phpMyAdmin (opsional, untuk manage database)

**Untuk XAMPP/WAMPP:**
1. Download dan install XAMPP dari https://www.apachefriends.org
2. Start Apache dan MySQL dari XAMPP Control Panel

### LANGKAH 2: Buat Database

1. Buka browser, akses http://localhost/phpmyadmin
2. Klik tab "SQL"
3. Copy paste seluruh isi file SQL yang telah disediakan
4. Klik "Go" untuk execute

Database akan otomatis membuat:
- 7 tabel utama
- 3 jenis dokumen default
- 3 akun user (admin, petugas, warga)

### LANGKAH 3: Setup Project Folder

1. Extract atau copy seluruh folder project ke:
   - XAMPP: `C:\xampp\htdocs\project_pengajuan_dokumen\`
   - WAMPP: `C:\wamp64\www\project_pengajuan_dokumen\`
   - Linux: `/var/www/html/project_pengajuan_dokumen/`

2. Buat folder untuk upload:
```bash
mkdir uploads
mkdir surat
chmod 777 uploads
chmod 777 surat
```

### LANGKAH 4: Konfigurasi Database

Edit file `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Isi jika ada password MySQL
define('DB_NAME', 'db_pengajuan_dokumen');
define('BASE_URL', 'http://localhost/project_pengajuan_dokumen/');
```

### LANGKAH 5: Testing Akses

1. Buka browser
2. Akses: http://localhost/project_pengajuan_dokumen/
3. Anda akan melihat landing page

---

## 👥 AKUN DEFAULT

### Admin
- **Username:** admin
- **Password:** password123
- **Akses:** Kelola user, lihat semua data

### Petugas
- **Username:** petugas1
- **Password:** password123
- **Akses:** Validasi pengajuan, terbitkan surat

### Warga
- **Username:** warga1
- **Password:** password123
- **Akses:** Ajukan dokumen, download surat

⚠️ **PENTING:** Ganti password default setelah instalasi!

---

## 📁 STRUKTUR FILE LENGKAP

```
project_pengajuan_dokumen/
│
├── 📄 index.php                    # Landing page
├── 📄 login.php                    # Form login
├── 📄 register.php                 # Form registrasi warga
│
├── 📁 assets/
│   ├── 📁 css/
│   │   └── style.css              # Custom CSS
│   ├── 📁 js/
│   │   └── main.js                # JavaScript utilities
│   └── 📁 img/                    # Gambar/ikon (buat sendiri)
│
├── 📁 includes/
│   ├── config.php                 # Konfigurasi database & session
│   ├── auth.php                   # Login, logout, register
│   ├── functions.php              # Fungsi utilitas
│   └── logout.php                 # Proses logout
│
├── 📁 dashboard/
│   ├── admin.php                  # Dashboard admin
│   ├── petugas.php                # Dashboard petugas
│   └── warga.php                  # Dashboard warga
│
├── 📁 pengajuan/
│   ├── form_pengajuan.php         # Form pengajuan dinamis
│   ├── proses_pengajuan.php       # Proses simpan pengajuan
│   ├── lihat_status.php           # Lihat status pengajuan
│   └── unduh_surat.php            # Download surat PDF
│
├── 📁 uploads/                     # Folder berkas warga (buat & chmod 777)
├── 📁 surat/                       # Folder surat terbit (buat & chmod 777)
│
└── 📄 README.txt                   # Dokumentasi
```

---

## 🎨 FITUR PER ROLE

### 👑 ADMIN
| Fitur | Deskripsi |
|-------|-----------|
| Dashboard | Statistik lengkap (warga, petugas, pengajuan) |
| Kelola User | Tambah, edit, aktifkan/nonaktifkan user |
| Kelola Dokumen | Tambah/edit jenis dokumen (max 3 aktif) |
| Laporan | Lihat semua pengajuan |

### 👮 PETUGAS
| Fitur | Deskripsi |
|-------|-----------|
| Dashboard | Daftar pengajuan perlu validasi |
| Verifikasi | Cek berkas & keterangan |
| Validasi | Setujui/tolak dengan catatan |
| Terbitkan Surat | Auto generate saat disetujui |

### 👤 WARGA
| Fitur | Deskripsi |
|-------|-----------|
| Registrasi | Daftar akun baru |
| Ajukan Dokumen | Form dinamis per jenis dokumen |
| Upload Berkas | PDF/JPG max 5MB |
| Cek Status | Real-time tracking |
| Download Surat | Jika disetujui |

---

## 📋 JENIS DOKUMEN & FIELD DINAMIS

### 1️⃣ SKTM (Surat Keterangan Tidak Mampu)
Field khusus:
- Nama Anak
- Nama Sekolah
- Penghasilan Orang Tua
- Alasan Permohonan

**Kegunaan:** Bantuan sosial, beasiswa pendidikan

### 2️⃣ SKU (Surat Keterangan Usaha)
Field khusus:
- Nama Usaha
- Jenis Usaha
- Alamat Usaha
- Lama Usaha

**Kegunaan:** Administrasi UMKM, perizinan

### 3️⃣ SKD (Surat Keterangan Domisili)
Field khusus:
- Alamat Asal
- Alamat Tujuan
- Lama Tinggal
- Alasan Pindah

**Kegunaan:** Data kependudukan, administrasi

---

## 🔒 FITUR KEAMANAN

- ✅ Password Hashing (bcrypt)
- ✅ SQL Injection Prevention
- ✅ XSS Protection
- ✅ Session Management
- ✅ Role-Based Access Control
- ✅ File Upload Validation
- ✅ CSRF Protection (form tokens)

---

## 🐛 TROUBLESHOOTING

### ❌ Error: Connection Failed
**Solusi:**
1. Cek MySQL service berjalan
2. Cek username/password di config.php
3. Pastikan database sudah dibuat

### ❌ Error: Cannot write to uploads/
**Solusi:**
```bash
chmod 777 uploads
chmod 777 surat
```
Atau:
```bash
chown -R www-data:www-data uploads surat  # Linux
```

### ❌ Error: SweetAlert tidak muncul
**Solusi:**
1. Cek koneksi internet (untuk CDN)
2. Atau download SweetAlert2 lokal

### ❌ File upload gagal
**Solusi:**
1. Edit php.ini:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```
2. Restart Apache

### ❌ Error: Headers already sent
**Solusi:**
1. Hapus spasi/enter sebelum `<?php`
2. Save file dengan encoding UTF-8 (no BOM)

---

## 🚀 PENGEMBANGAN LANJUTAN

### Fitur yang bisa ditambahkan:

1. **Notifikasi Email/WhatsApp**
   - PHPMailer untuk email
   - API WhatsApp untuk notif

2. **Export Laporan**
   - PHPExcel untuk Excel
   - TCPDF untuk PDF profesional

3. **Dashboard Analytics**
   - Chart.js untuk grafik
   - DataTables untuk tabel interaktif

4. **QR Code Verifikasi**
   - Library QR Code PHP
   - Scan untuk cek keaslian surat

5. **Digital Signature**
   - E-signature untuk petugas
   - Timestamp verification

6. **Mobile App**
   - Flutter/React Native
   - API REST untuk backend

---

## 📚 LIBRARY & CDN YANG DIGUNAKAN

| Library | Version | Fungsi |
|---------|---------|--------|
| Bootstrap | 5.3.0 | UI Framework |
| Font Awesome | 6.4.0 | Icons |
| SweetAlert2 | 11.x | Notifications |
| jQuery | - | Tidak digunakan (pure JS) |

Semua library diambil dari CDN untuk kemudahan instalasi.

---

## 💡 TIPS & BEST PRACTICES

1. **Keamanan:**
   - Ganti password default
   - Gunakan HTTPS di production
   - Backup database berkala

2. **Performance:**
   - Optimasi query database
   - Gunakan index pada kolom sering dicari
   - Compress gambar sebelum upload

3. **Maintenance:**
   - Log error ke file
   - Monitor disk space (folder uploads)
   - Update library secara berkala

4. **User Experience:**
   - Tambahkan loading indicator
   - Validasi form lebih detail
   - Responsive design testing

---

## 📞 SUPPORT

Jika menemui masalah:
1. Baca dokumentasi README.txt
2. Cek troubleshooting guide
3. Hubungi support

---

## 📄 LISENSI

MIT License - Free to use and modify

---

## ✅ CHECKLIST INSTALASI

- [ ] XAMPP/WAMPP terinstall
- [ ] MySQL berjalan
- [ ] Database di-import
- [ ] Folder project di htdocs
- [ ] Folder uploads & surat dibuat
- [ ] Permission 777 diberikan
- [ ] config.php disesuaikan
- [ ] Akses http://localhost/project_pengajuan_dokumen/
- [ ] Login berhasil
- [ ] Test ajukan dokumen
- [ ] Test validasi petugas
- [ ] Test download surat

---

**Selamat! Aplikasi siap digunakan! 🎉**