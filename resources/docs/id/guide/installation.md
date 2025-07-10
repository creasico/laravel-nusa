# Instalasi

Panduan lengkap untuk menginstal dan mengkonfigurasi Laravel Nusa dalam aplikasi Laravel Anda.

## Persyaratan Sistem

Sebelum menginstal Laravel Nusa, pastikan sistem Anda memenuhi persyaratan berikut:

- **PHP**: 8.1 atau lebih tinggi
- **Laravel**: 10.0 atau lebih tinggi
- **Ekstensi PHP**: SQLite (untuk database Nusa)
- **Composer**: Versi terbaru

## Instalasi

### 1. Install Package

Install Laravel Nusa melalui Composer:

```bash
composer require creasi/laravel-nusa
```

### 2. Jalankan Setup

Jalankan perintah instalasi untuk menyiapkan database dan konfigurasi:

```bash
php artisan nusa:install
```

Perintah ini akan:
- Membuat file database SQLite untuk data Nusa
- Mempublikasikan file konfigurasi
- Menjalankan migrasi database
- Mengunduh data wilayah administratif terbaru

### 3. Verifikasi Instalasi

Verifikasi bahwa instalasi berhasil:

```bash
php artisan nusa:check
```

Perintah ini akan memeriksa:
- Koneksi database Nusa
- Kelengkapan data
- Konfigurasi yang benar

## Konfigurasi Database

### Database SQLite (Default)

Secara default, Laravel Nusa menggunakan database SQLite terpisah:

```php
// config/database.php
'nusa' => [
    'driver' => 'sqlite',
    'database' => database_path('nusa.sqlite'),
    'prefix' => '',
    'foreign_key_constraints' => true,
],
```

### Database MySQL/PostgreSQL

Jika Anda ingin menggunakan database utama aplikasi:

```php
// config/nusa.php
'database' => [
    'connection' => 'mysql', // atau 'pgsql'
    'prefix' => 'nusa_',
],
```

Kemudian jalankan migrasi:

```bash
php artisan migrate --database=mysql
```

## Konfigurasi Lanjutan

### File Konfigurasi

Publikasikan file konfigurasi untuk kustomisasi:

```bash
php artisan vendor:publish --tag=nusa-config
```

File konfigurasi akan tersedia di `config/nusa.php`:

```php
return [
    'database' => [
        'connection' => 'nusa',
        'prefix' => '',
    ],
    
    'models' => [
        'province' => \Creasi\Nusa\Models\Province::class,
        'regency' => \Creasi\Nusa\Models\Regency::class,
        'district' => \Creasi\Nusa\Models\District::class,
        'village' => \Creasi\Nusa\Models\Village::class,
        'address' => \Creasi\Nusa\Models\Address::class,
    ],
    
    'api' => [
        'enabled' => true,
        'prefix' => 'nusa',
        'middleware' => ['api'],
    ],
];
```

### Konfigurasi API

Jika Anda ingin menggunakan API endpoints:

```php
// config/nusa.php
'api' => [
    'enabled' => true,
    'prefix' => 'nusa',
    'middleware' => ['api'],
    'rate_limit' => '60,1', // 60 requests per minute
],
```

## Update Data

### Update Manual

Update data wilayah administratif:

```bash
php artisan nusa:update
```

### Update Otomatis

Atur update otomatis melalui scheduler:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('nusa:update')
        ->monthly()
        ->onFailure(function () {
            // Handle failure
        });
}
```

## Troubleshooting

### Database Connection Error

Jika terjadi error koneksi database:

```bash
# Periksa file database
ls -la database/nusa.sqlite

# Periksa permission
chmod 664 database/nusa.sqlite

# Re-install jika perlu
php artisan nusa:install --force
```

### Memory Limit Error

Jika terjadi error memory limit saat instalasi:

```bash
# Tingkatkan memory limit
php -d memory_limit=512M artisan nusa:install

# Atau set di php.ini
memory_limit = 512M
```

### Permission Error

Jika terjadi error permission:

```bash
# Set permission untuk directory database
chmod 755 database/
chmod 664 database/nusa.sqlite

# Untuk web server
chown -R www-data:www-data database/
```

## Verifikasi Instalasi

### Test Basic Functionality

```php
use Creasi\Nusa\Models\Province;

// Test di tinker
php artisan tinker

>>> Province::count()
=> 34

>>> Province::find('33')->name
=> "Jawa Tengah"

>>> Province::find('33')->regencies->count()
=> 35
```

### Test API Endpoints

Jika API diaktifkan:

```bash
# Test endpoint provinces
curl http://localhost:8000/nusa/provinces

# Test specific province
curl http://localhost:8000/nusa/provinces/33
```

## Langkah Selanjutnya

Setelah instalasi berhasil:

1. **[Configuration](/id/guide/configuration)** - Konfigurasi lanjutan
2. **[Getting Started](/id/guide/getting-started)** - Mulai menggunakan Laravel Nusa
3. **[Models](/id/guide/models)** - Memahami model dan relasi
4. **[Examples](/id/examples/basic-usage)** - Contoh implementasi praktis

---

*Laravel Nusa siap digunakan dalam aplikasi Anda!*
