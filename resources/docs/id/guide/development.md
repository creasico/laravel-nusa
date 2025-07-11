# Pengaturan Pengembangan

Panduan ini mencakup pengaturan Laravel Nusa untuk pengembangan, kontribusi, dan bekerja dengan sumber data *upstream*.

## Prasyarat

### Perangkat Lunak yang Dibutuhkan

- **PHP** ≥ 8.2 dengan ekstensi:
  - `ext-sqlite3` - Untuk dukungan database SQLite
  - `ext-json` - Untuk penanganan JSON
  - `ext-mbstring` - Untuk manipulasi string
- **Node.js** ≥ 20 dengan manajer paket pnpm
- **Git** dengan dukungan *submodule*
- **Docker** (direkomendasikan) atau *server* MySQL lokal
- **Alat CLI SQLite** dengan `sqldiff` untuk manajemen database SQLite

### Alat Pengembangan

- **Composer** untuk manajemen dependensi PHP
- **pnpm** untuk dependensi Node.js (lebih cepat dari npm)
- **Docker Compose** untuk lingkungan pengembangan terkontainerisasi

## Mulai Cepat

### 1. Kloning Repositori

```bash
# Kloning dengan submodule (penting!)
git clone --recurse-submodules https://github.com/creasico/laravel-nusa.git
cd laravel-nusa

# Jika Anda lupa --recurse-submodules
git submodule update --init --recursive
```

### 2. Instal Dependensi

```bash
# Instal dependensi PHP
composer install

# Instal dependensi Node.js
pnpm install
```

### 3. Pengaturan Lingkungan

```bash
# Salin file lingkungan
cp workbench/.env.example workbench/.env

# Edit konfigurasi sesuai kebutuhan
nano workbench/.env
```

### 4. Pengaturan Database

**Opsi A: Docker (Direkomendasikan)**

Laravel Nusa menyediakan pengaturan Docker lengkap untuk pengembangan:

```bash
# Mulai layanan Docker
composer upstream:up

# Data secara otomatis diimpor oleh upstream:up
# Untuk mengimpor secara manual: composer testbench nusa:import

# Hasilkan database distribusi
composer testbench nusa:dist
```

**Opsi B: MySQL Lokal**

```bash
# Buat database yang dibutuhkan
mysql -e 'CREATE DATABASE testing;'
mysql -e 'CREATE DATABASE nusantara;'

# Impor data
composer testbench nusa:import --fresh
```

## Lingkungan Pengembangan Docker

### Perintah yang Tersedia

Laravel Nusa menyertakan skrip Composer yang nyaman untuk manajemen Docker:

```bash
# Mulai layanan (MySQL + phpMyAdmin)
composer upstream:up

# Hentikan layanan
composer upstream:down

# Impor data baru dari upstream
composer testbench nusa:import --fresh

# Buat database distribusi
composer testbench nusa:dist

# Hasilkan statistik
composer testbench nusa:stat

# Lihat log (menggunakan docker compose)
composer upstream logs

# Akses CLI MySQL (menggunakan docker compose)
composer upstream exec mysql mysql -u root -psecret nusantara

# Hasilkan file statis
composer testbench nusa:generate-static
```

### Layanan Docker

Lingkungan pengembangan meliputi:

- **MySQL 8.0** - *Server* database utama
- **phpMyAdmin** - Administrasi database berbasis web
- **Volume** - Penyimpanan data persisten

Akses phpMyAdmin di: `http://localhost:8080`
- Nama Pengguna: `root`
- Kata Sandi: `secret`

### Konfigurasi Docker

Pengaturan Docker didefinisikan dalam `docker-compose.yml`:

```yaml
services:
  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: nusantara
    volumes:
      - mysql_data:/var/lib/mysql

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "8080:80"
    environment:
      PMA_HOST: mysql
      PMA_USER: root
      PMA_PASSWORD: secret
```

## Perintah yang Tersedia

### Skrip Composer

Laravel Nusa menyediakan beberapa skrip *composer* untuk pengembangan:

```bash
# Lingkungan pengembangan
composer upstream:up          # Mulai layanan Docker + impor data
composer upstream:down        # Hentikan layanan Docker + bersihkan
composer upstream [args]      # Teruskan argumen ke docker-compose

# Pengujian dan kualitas
composer test                 # Jalankan suite pengujian
composer fix                  # Perbaiki gaya kode dengan Laravel Pint
composer testbench [args]     # Jalankan perintah testbench
composer testbench:purge      # Bersihkan kerangka workbench
composer tinker               # Mulai sesi tinker
```

### Perintah Nusa

Tersedia melalui `composer testbench nusa:*`:

#### `nusa:import`
Impor data dari sumber *upstream*:
```bash
composer testbench nusa:import           # Impor data dari upstream
composer testbench nusa:import --fresh   # Buat ulang database + impor
composer testbench nusa:import --dist    # Impor + buat database distribusi
```

#### `nusa:dist`
Buat database distribusi (menghapus koordinat untuk privasi):
```bash
composer testbench nusa:dist             # Buat database distribusi
composer testbench nusa:dist --force     # Paksa timpa distribusi yang sudah ada
```

#### `nusa:stat`
Hasilkan statistik database dan tampilkan perubahan:
```bash
composer testbench nusa:stat             # Tampilkan statistik database dan perubahan
```

#### `nusa:generate-static`
Hasilkan file statis (format CSV, JSON):
```bash
composer testbench nusa:generate-static  # Hasilkan file data statis
```

## Manajemen Data

### Memahami Sumber Data

Laravel Nusa mengintegrasikan data dari beberapa repositori *upstream*:

```
workbench/submodules/
├── wilayah/              # Data administratif inti
├── wilayah_kodepos/      # Pemetaan kode pos  
└── wilayah_boundaries/   # Batas geografis
```

### Proses Impor

Proses impor menarik data dari *submodule* Git *upstream* dan memprosesnya:

```bash
# Proses impor lengkap (direkomendasikan)
composer testbench nusa:import --fresh

# Impor tanpa membuat ulang database
composer testbench nusa:import

# Impor dan buat database distribusi
composer testbench nusa:import --dist
```

::: tip Opsi Impor
Perintah impor hanya mendukung opsi `--fresh` dan `--dist`. Ini secara otomatis mengimpor semua tingkat administratif (provinsi, kabupaten/kota, kecamatan, desa/kelurahan) dari sumber *upstream*.
:::

### Database Distribusi

Buat database distribusi yang sesuai privasi (menghapus data koordinat):

```bash
# Hasilkan database distribusi
composer testbench nusa:dist

# Paksa timpa database distribusi yang sudah ada
composer testbench nusa:dist --force
```

::: warning Kepatuhan Privasi
Database distribusi secara otomatis menghapus semua data koordinat untuk memastikan kepatuhan privasi. Ini adalah database yang disertakan dalam distribusi paket.
:::

### Statistik Data

Lihat statistik database dan perubahan:

```bash
# Tampilkan statistik database dan perubahan dari upstream
composer testbench nusa:stat
```

Perintah ini membandingkan database distribusi saat ini dengan database pengembangan untuk menunjukkan apa yang telah berubah.

## Alur Kerja Pengembangan

### 1. Melakukan Perubahan

```bash
# Buat branch fitur
git checkout -b feature/fitur-anda

# Lakukan perubahan Anda
# ... edit file ...

# Jalankan pengujian
composer test

# Perbaiki gaya kode
composer fix
```

### 2. Pengujian

```bash
# Jalankan suite pengujian lengkap
composer test

# Jalankan pengujian tertentu
vendor/bin/phpunit tests/Models/ProvinceTest.php

# Jalankan dengan cakupan
composer test -- --coverage-html tests/reports/html

# Uji fitur tertentu
vendor/bin/phpunit --filter testProvinceRelationships
```

### 3. Kualitas Kode

```bash
# Perbaiki gaya kode dengan Laravel Pint
composer fix

# Periksa gaya tanpa memperbaiki
vendor/bin/pint --test

# Jalankan analisis statis (jika dikonfigurasi)
composer analyse
```

### 4. Dokumentasi

```bash
# Mulai server dokumentasi
npm run docs:dev

# Bangun dokumentasi
npm run docs:build

# Pratinjau dokumentasi yang dibangun
npm run docs:preview
```

## Bekerja dengan Submodule

### Memperbarui Data Upstream

```bash
# Perbarui semua submodule ke versi terbaru
git submodule update --remote

# Perbarui submodule tertentu
git submodule update --remote workbench/submodules/wilayah

# Commit pembaruan submodule
git add workbench/submodules
git commit -m "chore: update upstream data sources"
```

### Manajemen Submodule

```bash
# Periksa status submodule
git submodule status

# Inisialisasi submodule (jika diperlukan)
git submodule init

# Perbarui ke commit tertentu
cd workbench/submodules/wilayah
git checkout specific-commit-hash
cd ../../..
git add workbench/submodules/wilayah
git commit -m "chore: pin wilayah to specific version"
```

## Pengembangan Database

### Mengakses Database

```bash
# SQLite (database distribusi)
sqlite3 database/nusa.sqlite

# MySQL (database pengembangan)
mysql -h 127.0.0.1 -u root -psecret nusantara

# Melalui Docker
composer upstream exec mysql mysql -u root -psecret nusantara
```

### Inspeksi Database

```bash
# Periksa struktur tabel
composer testbench tinker
>>> Schema::connection('nusa')->getColumnListing('provinces')

# Hitung catatan
>>> \Creasi\Nusa\Models\Province::count()

# Uji relasi
>>> \Creasi\Nusa\Models\Province::find('33')->regencies->count()
```

### Pengujian Kinerja

```bash
# Uji kinerja kueri
composer testbench tinker
>>> DB::connection('nusa')->enableQueryLog()
>>> \Creasi\Nusa\Models\Village::paginate(100)
>>> DB::connection('nusa')->getQueryLog()
```

## Debugging

### Aktifkan Mode Debug

```php
// Di workbench/.env
APP_DEBUG=true
LOG_LEVEL=debug

// Aktifkan logging kueri
DB_LOG_QUERIES=true
```

### Perintah Debug Umum

```bash
# Periksa konfigurasi
composer testbench config:show database.connections.nusa

# Uji koneksi database
composer testbench tinker
>>> DB::connection('nusa')->getPdo()

# Periksa rute
composer testbench route:list | grep nusa

# Bersihkan cache
composer testbench config:clear
composer testbench route:clear
```

## Optimasi Kinerja

### Database Pengembangan

```bash
# Gunakan database pengembangan dengan koordinat
cp database/nusa.dev.sqlite database/nusa.sqlite

# Atau buat dari sumber
composer testbench nusa:import --fresh
composer testbench nusa:dist --force
```

### Optimasi Kueri

```php
// Aktifkan logging kueri untuk analisis
DB::connection('nusa')->listen(function ($query) {
    Log::debug('Kueri Nusa', [
        'sql' => $query->sql,
        'bindings' => $query->bindings,
        'time' => $query->time
    ]);
});
```

## Pemecahan Masalah

### Masalah Umum

#### Submodule Tidak Diinisialisasi

```bash
# Error: direktori submodule kosong
git submodule update --init --recursive
```

#### Masalah Izin Docker

```bash
# Perbaiki izin Docker di Linux
sudo chown -R $USER:$USER database/
sudo chmod -R 755 database/
```

#### Error Koneksi Database

```bash
# Periksa layanan Docker
docker-compose ps

# Mulai ulang layanan
composer upstream:down
composer upstream:up

# Periksa log MySQL
composer upstream:logs mysql
```

#### Masalah Memori Selama Impor

```bash
# Tingkatkan batas memori PHP
php -d memory_limit=2G vendor/bin/testbench nusa:import -- --fresh
```

### Mendapatkan Bantuan

1. **Periksa log**: `storage/logs/laravel.log`
2. **Masalah GitHub**: [Laporkan bug](https://github.com/creasico/laravel-nusa/issues)
3. **Diskusi**: [Dukungan komunitas](https://github.com/orgs/creasico/discussions)
4. **Dokumentasi**: Periksa dokumentasi ini terlebih dahulu

## Langkah Selanjutnya

Setelah menyiapkan lingkungan pengembangan Anda:

1. **Jelajahi basis kode** - Pahami struktur proyek
2. **Jalankan pengujian** - Pastikan semuanya berfungsi dengan benar
3. **Baca panduan kontribusi** - Pelajari alur kerja pengembangan
4. **Mulai berkontribusi** - Pilih masalah atau sarankan perbaikan