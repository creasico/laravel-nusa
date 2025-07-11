# Setup Pengembangan

Panduan ini mencakup pengaturan Laravel Nusa untuk pengembangan, kontribusi, dan bekerja dengan sumber data upstream.

## Prasyarat

### Software yang Diperlukan

- **PHP** ≥ 8.2 dengan ekstensi:
  - `ext-sqlite3` - Untuk dukungan database SQLite
  - `ext-json` - Untuk penanganan JSON
  - `ext-mbstring` - Untuk manipulasi string
- **Node.js** ≥ 20 dengan package manager pnpm
- **Git** dengan dukungan submodule
- **Docker** (direkomendasikan) atau server MySQL lokal
- **SQLite CLI Tool** dengan `sqldiff` untuk manajemen database SQLite

### Tools Pengembangan

- **Composer** untuk manajemen dependensi PHP
- **pnpm** untuk dependensi Node.js (lebih cepat dari npm)
- **Docker Compose** untuk environment pengembangan dalam container

## Quick Start

### 1. Clone Repository

```bash
# Clone dengan submodules (penting!)
git clone --recurse-submodules https://github.com/creasico/laravel-nusa.git
cd laravel-nusa

# Jika Anda lupa --recurse-submodules
git submodule update --init --recursive
```

### 2. Install Dependencies

```bash
# Install dependensi PHP
composer install

# Install dependensi Node.js
pnpm install
```

### 3. Environment Setup

```bash
# Copy file environment
cp workbench/.env.example workbench/.env

# Edit konfigurasi sesuai kebutuhan
nano workbench/.env
```

### 4. Database Setup

**Opsi A: Docker (Direkomendasikan)**

Laravel Nusa menyediakan setup Docker lengkap untuk pengembangan:

```bash
# Start layanan Docker
composer upstream:up

# Data otomatis diimpor oleh upstream:up
# Untuk impor manual: composer testbench nusa:import

# Generate database distribusi
composer testbench nusa:dist
```

**Opsi B: MySQL Lokal**

```bash
# Buat database yang diperlukan
mysql -e 'CREATE DATABASE testing;'
mysql -e 'CREATE DATABASE nusantara;'

# Impor data
composer testbench nusa:import --fresh
```

## Environment Pengembangan Docker

### Perintah yang Tersedia

Laravel Nusa menyertakan script Composer yang nyaman untuk manajemen Docker:

```bash
# Start layanan (MySQL + phpMyAdmin)
composer upstream:up

# Stop layanan
composer upstream:down

# Impor data fresh dari upstream
composer testbench nusa:import --fresh

# Buat database distribusi
composer testbench nusa:dist

# Generate statistik
composer testbench nusa:stat

# Lihat logs (menggunakan docker compose)
composer upstream logs

# Akses MySQL CLI (menggunakan docker compose)
composer upstream exec mysql mysql -u root -psecret nusantara

# Generate file statis
composer testbench nusa:generate-static
```

### Layanan Docker

Environment pengembangan mencakup:

- **MySQL 8.0** - Server database utama
- **phpMyAdmin** - Administrasi database berbasis web
- **Volumes** - Penyimpanan data persisten

Akses phpMyAdmin di: `http://localhost:8080`
- Username: `root`
- Password: `secret`

### Konfigurasi Docker

Setup Docker didefinisikan dalam `docker-compose.yml`:

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

### Script Composer

Laravel Nusa menyediakan beberapa script composer untuk pengembangan:

```bash
# Environment pengembangan
composer upstream:up          # Start layanan Docker + impor data
composer upstream:down        # Stop layanan Docker + cleanup
composer upstream [args]      # Pass argumen ke docker-compose

# Testing dan quality
composer test                 # Jalankan test suite
composer fix                  # Fix code style dengan Laravel Pint
composer testbench [args]     # Jalankan perintah testbench
composer testbench:purge      # Purge workbench skeleton
composer tinker               # Start sesi tinker
```

### Perintah Nusa

Tersedia melalui `composer testbench nusa:*`:

#### `nusa:import`
Impor data dari sumber upstream:
```bash
composer testbench nusa:import           # Impor data dari upstream
composer testbench nusa:import --fresh   # Recreate database + impor
composer testbench nusa:import --dist    # Impor + buat database distribusi
```

#### `nusa:dist`
Buat database distribusi (hapus koordinat untuk privasi):
```bash
composer testbench nusa:dist             # Buat database distribusi
composer testbench nusa:dist --force     # Force overwrite database distribusi yang ada
```

#### `nusa:stat`
Generate statistik database dan tampilkan perubahan:
```bash
composer testbench nusa:stat             # Tampilkan statistik database dan perubahan
```

#### `nusa:generate-static`
Generate file statis (format CSV, JSON):
```bash
composer testbench nusa:generate-static  # Generate file data statis
```

## Manajemen Data

### Memahami Sumber Data

Laravel Nusa mengintegrasikan data dari beberapa repository upstream:

```
workbench/submodules/
├── wilayah/              # Data administratif inti
├── wilayah_kodepos/      # Pemetaan kode pos  
└── wilayah_boundaries/   # Batas geografis
```

### Proses Impor

Proses impor menarik data dari Git submodule upstream dan memprosesnya:

```bash
# Proses impor lengkap (direkomendasikan)
composer testbench nusa:import --fresh

# Impor tanpa recreate database
composer testbench nusa:import

# Impor dan buat database distribusi
composer testbench nusa:import --dist
```

::: tip Opsi Impor
Perintah impor hanya mendukung opsi `--fresh` dan `--dist`. Secara otomatis mengimpor semua tingkat administratif (provinsi, kabupaten, kecamatan, desa) dari sumber upstream.
:::

### Database Distribusi

Buat database distribusi yang sesuai privasi (hapus data koordinat):

```bash
# Generate database distribusi
composer testbench nusa:dist

# Force overwrite database distribusi yang ada
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

## Workflow Pengembangan

### 1. Membuat Perubahan

```bash
# Buat feature branch
git checkout -b feature/your-feature

# Buat perubahan Anda
# ... edit files ...

# Jalankan tests
composer test

# Fix code style
composer fix
```

### 2. Testing

```bash
# Jalankan test suite lengkap
composer test

# Jalankan test spesifik
vendor/bin/phpunit tests/Models/ProvinceTest.php

# Jalankan dengan coverage
composer test -- --coverage-html tests/reports/html

# Test fitur spesifik
vendor/bin/phpunit --filter testProvinceRelationships
```

### 3. Code Quality

```bash
# Fix code style dengan Laravel Pint
composer fix

# Check style tanpa fixing
vendor/bin/pint --test

# Jalankan static analysis (jika dikonfigurasi)
composer analyse
```

### 4. Dokumentasi

```bash
# Start server dokumentasi
npm run docs:dev

# Build dokumentasi
npm run docs:build

# Preview dokumentasi yang sudah di-build
npm run docs:preview
```

## Bekerja dengan Submodules

### Update Data Upstream

```bash
# Update semua submodule ke versi terbaru
git submodule update --remote

# Update submodule spesifik
git submodule update --remote workbench/submodules/wilayah

# Commit update submodule
git add workbench/submodules
git commit -m "chore: update upstream data sources"
```

### Manajemen Submodule

```bash
# Check status submodule
git submodule status

# Initialize submodules (jika diperlukan)
git submodule init

# Update ke commit spesifik
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

# Via Docker
composer upstream exec mysql mysql -u root -psecret nusantara
```

### Inspeksi Database

```bash
# Check struktur tabel
composer testbench tinker
>>> Schema::connection('nusa')->getColumnListing('provinces')

# Count records
>>> \Creasi\Nusa\Models\Province::count()

# Test relationships
>>> \Creasi\Nusa\Models\Province::find('33')->regencies->count()
```

### Performance Testing

```bash
# Test performa query
composer testbench tinker
>>> DB::connection('nusa')->enableQueryLog()
>>> \Creasi\Nusa\Models\Village::paginate(100)
>>> DB::connection('nusa')->getQueryLog()
```

## Debugging

### Enable Debug Mode

```php
// Di workbench/.env
APP_DEBUG=true
LOG_LEVEL=debug

// Enable query logging
DB_LOG_QUERIES=true
```

### Perintah Debug Umum

```bash
# Check konfigurasi
composer testbench config:show database.connections.nusa

# Test koneksi database
composer testbench tinker
>>> DB::connection('nusa')->getPdo()

# Check routes
composer testbench route:list | grep nusa

# Clear caches
composer testbench config:clear
composer testbench route:clear
```

## Optimisasi Performa

### Database Pengembangan

```bash
# Gunakan database pengembangan dengan koordinat
cp database/nusa.dev.sqlite database/nusa.sqlite

# Atau buat dari source
composer testbench nusa:import --fresh
composer testbench nusa:dist --force
```

### Optimisasi Query

```php
// Enable query logging untuk analisis
DB::connection('nusa')->listen(function ($query) {
    Log::debug('Nusa Query', [
        'sql' => $query->sql,
        'bindings' => $query->bindings,
        'time' => $query->time
    ]);
});
```

## Troubleshooting

### Masalah Umum

#### Submodules Tidak Diinisialisasi

```bash
# Error: direktori submodule kosong
git submodule update --init --recursive
```

#### Masalah Permission Docker

```bash
# Fix permission Docker di Linux
sudo chown -R $USER:$USER database/
sudo chmod -R 755 database/
```

#### Error Koneksi Database

```bash
# Check layanan Docker
docker-compose ps

# Restart layanan
composer upstream:down
composer upstream:up

# Check logs MySQL
composer upstream:logs mysql
```

#### Masalah Memory Saat Impor

```bash
# Tingkatkan PHP memory limit
php -d memory_limit=2G vendor/bin/testbench nusa:import -- --fresh
```

### Mendapatkan Bantuan

1. **Check logs**: `storage/logs/laravel.log`
2. **GitHub Issues**: [Laporkan bug](https://github.com/creasico/laravel-nusa/issues)
3. **Discussions**: [Dukungan komunitas](https://github.com/orgs/creasico/discussions)
4. **Dokumentasi**: Check dokumentasi ini terlebih dahulu

## Langkah Selanjutnya

Setelah menyiapkan environment pengembangan Anda:

1. **Jelajahi codebase** - Pahami struktur proyek
2. **Jalankan tests** - Pastikan semuanya bekerja dengan benar
3. **Baca panduan kontribusi** - Pelajari workflow pengembangan
4. **Mulai berkontribusi** - Pilih issue atau sarankan perbaikan
