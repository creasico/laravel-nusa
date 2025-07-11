# Pemecahan Masalah

Panduan ini membantu Anda menyelesaikan masalah umum saat menginstal, mengkonfigurasi, atau menggunakan Laravel Nusa.

## Masalah Instalasi

### Ekstensi SQLite PHP Hilang

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

**Verifikasi instalasi**:
```bash
php -m | grep sqlite
# Seharusnya menampilkan: pdo_sqlite, sqlite3
```

### Instalasi Composer Gagal

**Error**: `Package not found` atau `Version conflicts`

**Solusi**:

1. **Bersihkan *cache* Composer**:
   ```bash
   composer clear-cache
   composer install --no-cache
   ```

2. **Perbarui Composer**:
   ```bash
   composer self-update
   ```

3. **Periksa versi PHP**:
   ```bash
   php -v
   # Pastikan PHP >= 8.2
   ```

4. **Instal dengan versi tertentu**:
   ```bash
   composer require creasi/laravel-nusa:^0.1
   ```

### Kompatibilitas Versi Laravel

**Error**: `Package requires Laravel X.Y but you have Z.A`

**Solusi**: Periksa matriks kompatibilitas:

| Laravel Nusa | Versi Laravel |
|--------------|------------------|
| 0.1.x        | 9.0 - 12.x       |

Perbarui Laravel atau gunakan versi yang kompatibel:
```bash
# Perbarui Laravel
composer update laravel/framework

# Atau instal versi Nusa yang kompatibel
composer require creasi/laravel-nusa:^0.1
```

## Masalah Database

### Database SQLite Tidak Ditemukan

**Error**: `database disk image is malformed` atau `no such file`

**Solusi**:

1. **Periksa apakah file ada**:
   ```bash
   ls -la vendor/creasi/laravel-nusa/database/nusa.sqlite
   ```

2. **Instal ulang paket**:
   ```bash
   composer remove creasi/laravel-nusa
   composer require creasi/laravel-nusa
   ```

3. **Periksa izin file**:
   ```bash
   chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
   ```

### Error Koneksi Database

**Error**: `SQLSTATE[HY000] [14] unable to open database file`

**Solusi**:

1. **Periksa jalur database**:
   ```php
   // Di tinker
   config('database.connections.nusa.database')
   ```

2. **Verifikasi izin file**:
   ```bash
   # Jadikan direktori dapat ditulis
   chmod 755 vendor/creasi/laravel-nusa/database/
   
   # Jadikan file dapat dibaca
   chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
   ```

3. **Uji koneksi**:
   ```bash
   php artisan tinker
   >>> DB::connection('nusa')->getPdo()
   ```

### Error Kendala Kunci Asing

**Error**: `FOREIGN KEY constraint failed`

**Solusi**: Aktifkan kendala kunci asing:

```php
// Di config/database.php
'nusa' => [
    'driver' => 'sqlite',
    'database' => database_path('nusa.sqlite'),
    'foreign_key_constraints' => true, // Tambahkan ini
],
```

## Masalah Konfigurasi

### Rute Tidak Berfungsi

**Error**: `Route [nusa.provinces.index] not defined`

**Solusi**:

1. **Periksa apakah rute diaktifkan**:
   ```bash
   # Di .env
   CREASI_NUSA_ROUTES_ENABLE=true
   ```

2. **Bersihkan *cache* rute**:
   ```bash
   php artisan route:clear
   php artisan config:clear
   ```

3. **Verifikasi penyedia layanan dimuat**:
   ```bash
   php artisan config:show app.providers | grep Nusa
   ```

4. **Periksa registrasi rute**:
   ```bash
   php artisan route:list | grep nusa
   ```

### *Endpoint* API Mengembalikan 404

**Error**: `404 Not Found` untuk `/nusa/provinces`

**Solusi**:

1. **Periksa awalan rute**:
   ```bash
   # Di .env
   CREASI_NUSA_ROUTES_PREFIX=nusa
   ```

2. **Verifikasi konfigurasi *web server***:
   ```apache
   # Apache .htaccess
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

3. **Uji dengan URL lengkap**:
   ```bash
   curl http://your-app.test/index.php/nusa/provinces
   ```

### Masalah *Cache* Konfigurasi

**Error**: Perubahan konfigurasi tidak berlaku

**Solusi**: Bersihkan *cache* konfigurasi:

```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
```

## Masalah Model dan Kueri

### Error Model Tidak Ditemukan

**Error**: `Class 'Creasi\Nusa\Models\Province' not found`

**Solusi**:

1. **Periksa *autoloader***:
   ```bash
   composer dump-autoload
   ```

2. **Verifikasi instalasi paket**:
   ```bash
   composer show creasi/laravel-nusa
   ```

3. **Periksa impor *namespace***:
   ```php
   use Creasi\Nusa\Models\Province; // Tambahkan ini
   ```

### Hasil Kueri Kosong

**Error**: Model mengembalikan koleksi kosong

**Solusi**:

1. **Uji koneksi database**:
   ```bash
   php artisan tinker
   >>> DB::connection('nusa')->table('provinces')->count()
   ```

2. **Periksa nama tabel**:
   ```bash
   >>> Schema::connection('nusa')->getTableListing()
   ```

3. **Verifikasi data ada**:
   ```bash
   sqlite3 vendor/creasi/laravel-nusa/database/nusa.sqlite
   .tables
   SELECT COUNT(*) FROM provinces;
   ```

### Error Relasi

**Error**: `Call to undefined relationship`

**Solusi**: Periksa apakah metode relasi ada:

```php
// Penggunaan yang benar
$province = Province::find('33');
$regencies = $province->regencies; // Bukan regency()

// Relasi yang tersedia
$province->regencies;  // HasMany
$province->districts;  // HasMany  
$province->villages;   // HasMany
```

## Masalah Kinerja

### Kinerja Kueri Lambat

**Masalah**: Kueri memakan waktu terlalu lama

**Solusi**:

1. **Gunakan paginasi**:
   ```php
   // Baik
   Village::paginate(50);
   
   // Hindari
   Village::all(); // 83.762 catatan!
   ```

2. **Pilih kolom tertentu**:
   ```php
   Province::select('code', 'name')->get();
   ```

3. **Gunakan *eager loading***:
   ```php
   Province::with('regencies')->get();
   ```

4. **Periksa indeks**:
   ```sql
   EXPLAIN QUERY PLAN SELECT * FROM villages WHERE province_code = '33';
   ```

### Batas Memori Terlampaui

**Error**: `Fatal error: Allowed memory size exhausted`

**Solusi**:

1. **Tingkatkan batas memori**:
   ```bash
   php -d memory_limit=512M artisan your:command
   ```

2. **Gunakan *chunking* untuk dataset besar**:
   ```php
   Village::chunk(1000, function ($villages) {
       foreach ($villages as $village) {
           // Proses desa/kelurahan
       }
   });
   ```

3. **Optimalkan kueri**:
   ```php
   // Gunakan select() untuk membatasi kolom
   Village::select('code', 'name')->chunk(1000, $callback);
   ```

## Masalah Pengembangan

### Masalah Submodule

**Error**: `Submodule path 'workbench/submodules/wilayah': checked out 'abc123'`

**Solusi**:

1. **Inisialisasi submodule**:
   ```bash
   git submodule update --init --recursive
   ```

2. **Perbarui submodule**:
   ```bash
   git submodule update --remote
   ```

3. **Reset submodule**:
   ```bash
   git submodule deinit --all
   git submodule update --init --recursive
   ```

### Masalah Docker

**Error**: `Cannot connect to the Docker daemon`

**Solusi**:

1. **Mulai layanan Docker**:
   ```bash
   # Linux
   sudo systemctl start docker
   
   # macOS
   open -a Docker
   ```

2. **Periksa Docker Compose**:
   ```bash
   docker-compose --version
   ```

3. **Reset lingkungan Docker**:
   ```bash
   composer upstream:down
   docker system prune -f
   composer upstream:up
   ```

### Perintah Impor Gagal

**Error**: Perintah impor data gagal

**Solusi**:

1. **Periksa koneksi database**:
   ```bash
   composer testbench tinker
   >>> DB::connection()->getPdo()
   ```

2. **Verifikasi submodule**:
   ```bash
   ls -la workbench/submodules/
   ```

3. **Jalankan dengan *output* *verbose***:
   ```bash
   composer testbench nusa:import -- --fresh -v
   ```

4. **Periksa ruang disk**:
   ```bash
   df -h
   ```

## Masalah API

### Error CORS

**Error**: `Access to fetch at 'http://localhost/nusa/provinces' from origin 'http://localhost:3000' has been blocked by CORS policy`

**Solusi**: Konfigurasi CORS di Laravel:

```php
// config/cors.php
'paths' => ['api/*', 'nusa/*'],
'allowed_methods' => ['GET'],
'allowed_origins' => ['*'], // Atau domain tertentu
'allowed_headers' => ['*'],
```

### Masalah Pembatasan Laju

**Error**: `429 Too Many Requests`

**Solusi**:

1. **Periksa batas laju**:
   ```php
   // Di rute atau middleware
   Route::middleware(['throttle:60,1'])->group(function () {
       // Rute Anda
   });
   ```

2. **Tingkatkan batas**:
   ```php
   Route::middleware(['throttle:1000,1'])->group(function () {
       // Rute batas yang lebih tinggi
   });
   ```

### Masalah Respon JSON

**Error**: Respon JSON tidak valid

**Solusi**:

1. **Periksa *header* Accept**:
   ```bash
   curl -H "Accept: application/json" http://your-app.test/nusa/provinces
   ```

2. **Verifikasi rute API**:
   ```bash
   php artisan route:list | grep nusa
   ```

## Mendapatkan Bantuan

### Informasi Debug

Saat melaporkan masalah, sertakan:

```bash
# Informasi sistem
php -v
composer --version
laravel --version

# Informasi paket
composer show creasi/laravel-nusa

# Konfigurasi Laravel
php artisan about

# Uji koneksi database
php artisan tinker
>>> DB::connection('nusa')->getPdo()
>>> \Creasi\Nusa\Models\Province::count()
```

### File Log

Periksa file log ini untuk error:

```bash
# Log Laravel
tail -f storage/logs/laravel.log

# Log web server
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# Log PHP
tail -f /var/log/php_errors.log
```

### Saluran Dukungan

1. **Dokumentasi** - Periksa dokumentasi ini terlebih dahulu
2. **Masalah GitHub** - [Laporkan bug](https://github.com/creasico/laravel-nusa/issues)
3. **Diskusi GitHub** - [Dukungan komunitas](https://github.com/orgs/creasico/discussions)
4. **Stack Overflow** - Beri tag dengan `laravel-nusa`

### Membuat Laporan Bug

Sertakan informasi ini:

- **Lingkungan**: Versi PHP, versi Laravel, OS
- **Versi paket**: `composer show creasi/laravel-nusa`
- **Pesan error**: Error lengkap dengan *stack trace*
- **Langkah-langkah untuk mereproduksi**: Contoh kode minimal
- **Perilaku yang diharapkan**: Apa yang seharusnya terjadi
- **Perilaku aktual**: Apa yang sebenarnya terjadi

Panduan pemecahan masalah ini mencakup masalah paling umum. Jika Anda menemukan masalah yang tidak tercantum di sini, silakan periksa masalah GitHub atau buat yang baru dengan informasi terperinci.