# Instalasi

Panduan ini menyediakan instruksi instalasi yang komprehensif, opsi konfigurasi, dan troubleshooting untuk Laravel Nusa.

## Persyaratan Sistem

### Persyaratan PHP

- **Versi PHP**: 8.2 atau lebih tinggi
- **Ekstensi yang Diperlukan**:
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

- **Ruang Disk**: ~15MB untuk package dan database
- **Database**: Menggunakan koneksi SQLite terpisah (tidak berdampak pada database utama Anda)
- **Memory**: Tidak ada persyaratan memory tambahan

## Instalasi

### Langkah 1: Install via Composer

```bash
composer require creasi/laravel-nusa
```

### Langkah 2: Verifikasi Instalasi

Laravel Nusa secara otomatis mengkonfigurasi dirinya sendiri. Verifikasi bahwa instalasi berhasil:

```bash
php artisan tinker
```

```php
// Di Tinker - ini harus mengembalikan 38
\Creasi\Nusa\Models\Province::count();
```

Jika berhasil, Anda akan melihat jumlah provinsi di Indonesia.

### Langkah 3: Publikasi Konfigurasi (Opsional)

Jika Anda perlu menyesuaikan konfigurasi:

```bash
php artisan vendor:publish --provider="Creasi\Nusa\NusaServiceProvider"
```

Ini akan membuat file `config/nusa.php` yang dapat Anda sesuaikan.

## Konfigurasi

### Konfigurasi Database

Laravel Nusa menggunakan database SQLite terpisah yang disimpan di `database/nusa.sqlite`. Konfigurasi default:

```php
// config/nusa.php
return [
    'database' => [
        'connection' => 'nusa',
        'path' => database_path('nusa.sqlite'),
    ],
    
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 jam
        'prefix' => 'nusa',
    ],
    
    'models' => [
        'province' => \Creasi\Nusa\Models\Province::class,
        'regency' => \Creasi\Nusa\Models\Regency::class,
        'district' => \Creasi\Nusa\Models\District::class,
        'village' => \Creasi\Nusa\Models\Village::class,
    ],
];
```

### Konfigurasi Cache

Laravel Nusa mendukung caching untuk meningkatkan performa:

```php
// config/nusa.php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // Cache selama 1 jam
    'prefix' => 'nusa',
    'store' => null, // Gunakan cache store default
],
```

### Konfigurasi Model Kustom

Anda dapat menggunakan model kustom:

```php
// config/nusa.php
'models' => [
    'province' => \App\Models\Province::class,
    'regency' => \App\Models\Regency::class,
    'district' => \App\Models\District::class,
    'village' => \App\Models\Village::class,
],
```

## Verifikasi Instalasi

### Tes Dasar

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Tes model dasar
$provinces = Province::count(); // Harus 38
$regencies = Regency::count();  // Harus 514
$districts = District::count(); // Harus 7,285
$villages = Village::count();   // Harus 83,762

echo "Provinsi: {$provinces}\n";
echo "Kabupaten/Kota: {$regencies}\n";
echo "Kecamatan: {$districts}\n";
echo "Kelurahan/Desa: {$villages}\n";
```

### Tes Pencarian

```php
// Tes pencarian
$jakarta = Province::search('jakarta')->first();
$semarang = Regency::search('semarang')->first();
$medono = Village::search('medono')->first();

if ($jakarta && $semarang && $medono) {
    echo "Pencarian berfungsi dengan baik!\n";
} else {
    echo "Ada masalah dengan pencarian.\n";
}
```

### Tes Relasi

```php
// Tes relasi
$village = Village::with(['district.regency.province'])->first();

if ($village && $village->district && $village->district->regency && $village->district->regency->province) {
    echo "Relasi berfungsi dengan baik!\n";
    echo "Alamat: {$village->name}, {$village->district->name}, {$village->district->regency->name}, {$village->district->regency->province->name}\n";
} else {
    echo "Ada masalah dengan relasi.\n";
}
```

## Troubleshooting

### Masalah Umum

#### 1. Database Tidak Ditemukan

**Error**: `Database file not found`

**Solusi**:
```bash
# Pastikan file database ada
ls -la database/nusa.sqlite

# Jika tidak ada, coba install ulang
composer require creasi/laravel-nusa --force
```

#### 2. Ekstensi SQLite Tidak Tersedia

**Error**: `SQLite extension not found`

**Solusi**:
```bash
# Ubuntu/Debian
sudo apt-get install php-sqlite3

# CentOS/RHEL
sudo yum install php-sqlite3

# macOS dengan Homebrew
brew install php@8.2 --with-sqlite3
```

#### 3. Permission Error

**Error**: `Permission denied`

**Solusi**:
```bash
# Berikan permission yang tepat
chmod 664 database/nusa.sqlite
chmod 775 database/

# Pastikan web server dapat mengakses
chown www-data:www-data database/nusa.sqlite
```

#### 4. Memory Limit

**Error**: `Memory limit exceeded`

**Solusi**:
```php
// Tingkatkan memory limit di php.ini
memory_limit = 256M

// Atau dalam kode
ini_set('memory_limit', '256M');
```

### Debugging

#### Mode Debug

Aktifkan debug mode untuk informasi lebih detail:

```php
// config/nusa.php
'debug' => env('NUSA_DEBUG', false),
```

```bash
# Set environment variable
NUSA_DEBUG=true
```

#### Log Queries

Monitor query yang dijalankan:

```php
use Illuminate\Support\Facades\DB;

DB::listen(function ($query) {
    if (str_contains($query->connectionName, 'nusa')) {
        logger()->info('Nusa Query: ' . $query->sql, $query->bindings);
    }
});
```

### Performance Optimization

#### 1. Aktifkan Cache

```php
// config/nusa.php
'cache' => [
    'enabled' => true,
    'ttl' => 7200, // 2 jam
    'prefix' => 'nusa',
],
```

#### 2. Gunakan Eager Loading

```php
// ❌ N+1 Problem
$villages = Village::all();
foreach ($villages as $village) {
    echo $village->district->name; // Query untuk setiap village
}

// ✅ Eager Loading
$villages = Village::with('district')->get();
foreach ($villages as $village) {
    echo $village->district->name; // Tidak ada query tambahan
}
```

#### 3. Gunakan Select Spesifik

```php
// ❌ Select semua kolom
$provinces = Province::all();

// ✅ Select kolom yang diperlukan saja
$provinces = Province::select(['code', 'name'])->get();
```

## Instalasi untuk Development

### Clone Repository

Untuk development atau kontribusi:

```bash
git clone https://github.com/creasico/laravel-nusa.git
cd laravel-nusa
composer install
```

### Setup Testing

```bash
# Copy environment file
cp .env.example .env

# Install dependencies
composer install

# Run tests
composer test
```

### Build Database

```bash
# Build database dari source
php artisan nusa:build

# Atau download pre-built database
php artisan nusa:download
```

## Uninstall

Jika Anda perlu menghapus Laravel Nusa:

```bash
# Hapus package
composer remove creasi/laravel-nusa

# Hapus file database (opsional)
rm database/nusa.sqlite

# Hapus file konfigurasi (opsional)
rm config/nusa.php
```

## Dukungan

Jika Anda mengalami masalah:

1. **Periksa dokumentasi** - Baca panduan troubleshooting
2. **Cek GitHub Issues** - Lihat apakah masalah sudah dilaporkan
3. **Buat Issue Baru** - Jika masalah belum ada, buat issue baru
4. **Diskusi** - Gunakan GitHub Discussions untuk pertanyaan umum

**Repository**: [https://github.com/creasico/laravel-nusa](https://github.com/creasico/laravel-nusa)

Dengan mengikuti panduan ini, Anda seharusnya dapat menginstal dan mengkonfigurasi Laravel Nusa dengan sukses dalam aplikasi Laravel Anda.
