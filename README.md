# MatchIn — Sistem Matching Partner Tugas Mahasiswa

Sistem informasi (MIS) + sistem pendukung keputusan (DSS) berbasis metode **SAW (Simple Additive Weighting)** untuk rekomendasi partner tugas antar mahasiswa dalam kelas yang sama.

## Fitur

- Login & registrasi mahasiswa
- Dashboard dengan statistik
- Manajemen kelas per semester (7 mata kuliah wajib)
- Manajemen skill & preferensi partner
- Rekomendasi partner dengan metode SAW
- Visualisasi 5 tabel hasil perhitungan SAW
- UI pastel modern, responsif, Bahasa Indonesia

## Tech Stack

- **Frontend:** HTML, Tailwind CSS, JavaScript
- **Backend:** PHP (PDO)
- **Database:** MySQL
- **Server:** XAMPP (Apache + MySQL)

## Instalasi

### 1. Import Database

Buka phpMyAdmin (`http://localhost/phpmyadmin`), lalu import:

1. `sql/schema.sql`
2. `sql/seed.sql`

### 2. Konfigurasi Database

Edit `config/database.php` jika perlu:

```php
$host = 'localhost';
$dbname = 'matchin';
$username = 'root';
$password = '';
```

### 3. Build CSS (Tailwind)

```bash
cd c:\xampp\htdocs\MatchIn
npm install
npm run build
```

Untuk development dengan auto-rebuild:

```bash
npm run dev
```

### 4. Akses Aplikasi

Buka: **http://localhost/MatchIn/**

## Akun Demo

| Email | Password |
|-------|----------|
| mahasiswa1@demo.ac.id | password |
| mahasiswa2@demo.ac.id | password |

Semua akun demo (mahasiswa1–6) menggunakan password: `password`

## Alur Penggunaan

1. **Masuk** ke akun
2. **Pilih Kelas** — daftar ke kelas semester aktif
3. **Isi Skill** — tambahkan keahlian
4. **Atur Preferensi** — skill & gaya kerja yang dicari per kelas
5. **Cari Partner** → klik **Tentukan Rekomendasi**
6. Lihat **5 tabel hasil SAW** + rekomendasi Top 3

## Kriteria SAW

| Kode | Kriteria | Bobot |
|------|----------|-------|
| C1 | Kesesuaian Skill | 40% |
| C2 | Kesesuaian Preferensi | 30% |
| C3 | Kesesuaian Gaya Kerja | 20% |
| C4 | Pengalaman | 10% |

## Struktur Folder

```
MatchIn/
├── auth/           # Login, register, logout
├── config/         # App & database config
├── includes/       # Layout & components
├── pages/          # Halaman aplikasi
├── services/       # Business logic
├── api/            # JSON endpoints
├── assets/         # CSS & JS
└── sql/            # Schema & seed data
```

## Lisensi

Proyek akademik — bebas digunakan untuk pembelajaran.
