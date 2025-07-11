# Instalasi

Panduan ini menyediakan instruksi instalasi yang komprehensif, opsi konfigurasi, dan pemecahan masalah untuk Laravel Nusa.

## Persyaratan Sistem

### Persyaratan PHP

- **Versi PHP**: 8.2 atau lebih tinggi
- **Ekstensi yang Dibutuhkan**:
  - `ext-sqlite3` - Untuk dukungan database SQLite
  - `ext-json` - Untuk penanganan JSON (biasanya sudah termasuk)
  - `ext-mbstring` - Untuk manipulasi string (biasanya sudah termasuk)

### Persyaratan Laravel

Laravel Nusa mendukung beberapa versi Laravel:

- **Laravel 9.x** - Versi minimum 9.0
- **Laravel 10.x** - Dukungan penuh
- **Laravel 11.x** - Dukungan penuh
- **Laravel 12.x** - Dukungan penuh

### Persyaratan Server

- **Ruang Disk**: ~15MB untuk paket dan database
- **Database**: Menggunakan koneksi SQLite terpisah (tidak berdampak pada database utama Anda)
- **Memori**: Tidak ada persyaratan memori tambahan

## Instalasi

### Langkah 1: Instal melalui Composer

```bash
composer require creasi/laravel-nusa
```

### Langkah 2: Verifikasi Instalasi

Laravel Nusa secara otomatis mengkonfigurasi dirinya sendiri. Verifikasi apakah berfungsi:

```bash
php artisan tinker
```

```php
// Di Tinker - ini seharusnya mengembalikan 34
\Creasi\Nusa\Models\Province::count();
```

Jika Anda melihat `34`, instalasi berhasil!

### Langkah 3: Uji Rute API (Opsional)

Jika Anda berencana menggunakan API, uji *endpoint*:

```bash
# Uji di browser Anda atau dengan curl
curl http://your-app.test/nusa/provinces
```

## Apa yang Terinstal

Laravel Nusa secara otomatis mengatur:

1. **Registrasi Penyedia Layanan** - Ditemukan secara otomatis oleh Laravel
2. **Koneksi Database** - Menambahkan koneksi `nusa` ke konfigurasi database Anda
3. **Database SQLite** - Database yang sudah dibuat sebelumnya dengan semua data administratif Indonesia
4. **Rute API** - *Endpoint* RESTful (dapat dinonaktifkan)
5. **Model Eloquent** - Model siap pakai dengan relasi

## Konfigurasi

Laravel Nusa berfungsi di luar kotak dengan *default* yang masuk akal, tetapi Anda dapat menyesuaikannya untuk kebutuhan spesifik Anda.

### Konfigurasi Dasar

Opsi konfigurasi yang paling umum dapat diatur melalui variabel lingkungan:

```dotenv
# Aktifkan/nonaktifkan rute API (default: true)
CREASI_NUSA_ROUTES_ENABLE=true

# Ubah awalan rute API (default: nusa)
CREASI_NUSA_ROUTES_PREFIX=api/indonesia

# Gunakan koneksi database kustom (default: nusa)
CREASI_NUSA_CONNECTION=custom_nusa
```

### Konfigurasi Lanjutan

Untuk kustomisasi yang lebih canggih, publikasikan file konfigurasi:

```bash
php artisan vendor:publish --tag=creasi-nusa-config
```

Ini membuat `config/creasi/nusa.php`:

```php
return [
    // Nama koneksi database untuk data Nusa
    'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),

    // Nama tabel (sesuaikan jika diperlukan)
    'table_names' => [
        'provinces' => 'provinces',
        'regencies' => 'regencies',
        'districts' => 'districts',
        'villages' => 'villages',
    ],

    // Implementasi model alamat
    'addressable' => \Creasi\Nusa\Models\Address::class,

    // Konfigurasi rute API
    'routes_enable' => env('CREASI_NUSA_ROUTES_ENABLE', true),
    'routes_prefix' => env('CREASI_NUSA_ROUTES_PREFIX', 'nusa'),
];
```

### Koneksi Database Kustom

Jika Anda perlu menggunakan koneksi database yang berbeda, tambahkan ke `config/database.php`:

```php
'connections' => [
    // Koneksi Anda yang sudah ada...

    'indonesia' => [
        'driver' => 'sqlite',
        'database' => database_path('indonesia.sqlite'),
        'prefix' => '',
        'foreign_key_constraints' => true,
    ],
],
```

Kemudian perbarui lingkungan Anda:

```dotenv
CREASI_NUSA_CONNECTION=indonesia
```

## Pengaturan Fitur Opsional

### Manajemen Alamat

Jika Anda berencana menggunakan fitur manajemen alamat untuk menyimpan alamat pengguna:

#### 1. Publikasikan Migrasi

```bash
php artisan vendor:publish --tag=creasi-migrations
```

#### 2. Jalankan Migrasi

```bash
php artisan migrate
```

Ini membuat tabel `addresses` di database utama Anda untuk menyimpan alamat pengguna dengan referensi ke wilayah administratif.

### Rute API

Laravel Nusa menyediakan *endpoint* API RESTful secara *default*:

#### Rute yang Tersedia

```http
GET /nusa/provinces
GET /nusa/provinces/{province}
GET /nusa/provinces/{province}/regencies
GET /nusa/provinces/{province}/districts
GET /nusa/provinces/{province}/villages

GET /nusa/regencies
GET /nusa/regencies/{regency}
GET /nusa/regencies/{regency}/districts
GET /nusa/regencies/{regency}/villages

GET /nusa/districts
GET /nusa/districts/{district}
GET /nusa/districts/{district}/villages

GET /nusa/villages
GET /nusa/villages/{village}
```

#### Nonaktifkan Rute API

Jika Anda tidak membutuhkan *endpoint* API:

```dotenv
CREASI_NUSA_ROUTES_ENABLE=false
```

#### Awalan Rute Kustom

Untuk mengubah awalan rute dari `/nusa` ke yang lain:

```dotenv
CREASI_NUSA_ROUTES_PREFIX=api/indonesia
```

Rute kemudian akan tersedia di `/api/indonesia/provinces`, dll.

## Pemecahan Masalah

### Masalah Instalasi Umum

#### Ekstensi SQLite PHP Tidak Ditemukan

**Error**: `could not find driver` atau `PDO SQLite driver not found`

**Solusi**: Instal ekstensi SQLite PHP:

```bash
# Ubuntu/Debian
sudo apt-get install php8.2-sqlite3

# CentOS/RHEL/Fedora
sudo yum install php-sqlite3
# atau
sudo dnf install php-sqlite3

# macOS dengan Homebrew
brew install php@8.2

# Windows (batalkan komentar di php.ini)
extension=pdo_sqlite
extension=sqlite3
```

Setelah instalasi, mulai ulang *web server* dan PHP-FPM jika berlaku.

#### Masalah File Database

**Error**: `database disk image is malformed` atau `database locked`

**Solusi**:

1. Bersihkan *cache* Composer dan instal ulang:
```bash
composer clear-cache
rm -rf vendor/creasi/laravel-nusa
composer install
```

2. Periksa izin file:
```bash
# Periksa apakah file ada dan dapat dibaca
ls -la vendor/creasi/laravel-nusa/database/nusa.sqlite

# Perbaiki izin jika diperlukan
chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
```

3. Verifikasi ruang disk:
```bash
df -h # Periksa ruang disk yang tersedia
```

#### Masalah Registrasi Rute

**Error**: `Route [nusa.provinces.index] not defined`

**Solusi**:

1. Bersihkan *cache* rute:
```bash
php artisan route:clear
php artisan config:clear
```

2. Verifikasi rute diaktifkan:
```bash
php artisan route:list | grep nusa
```

3. Periksa registrasi penyedia layanan:
```bash
php artisan config:show app.providers | grep Nusa
```

#### Masalah Memori atau Kinerja

**Error**: `Maximum execution time exceeded` atau `Memory limit exceeded`

**Solusi**:

1. Tingkatkan batas PHP di `php.ini`:
```ini
memory_limit = 256M
max_execution_time = 300
```

2. Gunakan paginasi untuk kueri besar:
```php
// Daripada
$villages = Village::all(); // 83.762 catatan!

// Gunakan
$villages = Village::paginate(50);
```

### Penerapan Produksi

#### Izin File

Pastikan izin file yang tepat dalam produksi:

```bash
# Jadikan database dapat dibaca oleh web server
chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
chown www-data:www-data vendor/creasi/laravel-nusa/database/nusa.sqlite
```

#### Caching

Aktifkan *caching* untuk kinerja yang lebih baik:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Keamanan

Pertimbangkan langkah-langkah keamanan ini:

1. Nonaktifkan rute API jika tidak diperlukan:
```dotenv
CREASI_NUSA_ROUTES_ENABLE=false
```

2. Tambahkan pembatasan laju ke rute API:
```php
// Di RouteServiceProvider atau middleware kustom
Route::middleware(['throttle:100,1'])->group(function () {
    // Rute API Anda
});
```

### Mendapatkan Bantuan

Jika Anda masih mengalami masalah:

1. **Periksa log Laravel**: `storage/logs/laravel.log`
2. **Aktifkan mode debug**: Atur `APP_DEBUG=true` di `.env` (hanya pengembangan)
3. **Masalah GitHub**: [Laporkan bug](https://github.com/creasico/laravel-nusa/issues)
4. **Dukungan Komunitas**: [Diskusi GitHub](https://github.com/orgs/creasico/discussions)

Saat melaporkan masalah, harap sertakan:
- Versi PHP (`php -v`)
- Versi Laravel
- Pesan error dari log
- Langkah-langkah untuk mereproduksi masalah

## Langkah Selanjutnya

Setelah instalasi berhasil:

- **[Memulai](/id/guide/getting-started)** - Panduan memulai cepat dan penggunaan dasar
- **[Konfigurasi](/id/guide/configuration)** - Opsi konfigurasi terperinci
- **[Model & Relasi](/id/guide/models)** - Memahami struktur data