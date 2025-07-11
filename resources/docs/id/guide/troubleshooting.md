# Troubleshooting

Panduan ini membantu Anda mengatasi masalah umum saat menginstal, mengkonfigurasi, atau menggunakan Laravel Nusa.

## Masalah Instalasi

### Ekstensi PHP SQLite Hilang

**Error**: `could not find driver` atau `PDO SQLite driver not found`

**Solusi**: Install ekstensi PHP SQLite:

```bash
# Ubuntu/Debian
sudo apt-get install php8.2-sqlite3

# CentOS/RHEL/Fedora
sudo yum install php-sqlite3
# atau
sudo dnf install php-sqlite3

# macOS dengan Homebrew
brew install php@8.2

# Windows (uncomment di php.ini)
extension=pdo_sqlite
extension=sqlite3
```

**Verifikasi instalasi**:
```bash
php -m | grep sqlite
# Harus menampilkan: pdo_sqlite, sqlite3
```

### Instalasi Composer Gagal

**Error**: `Package not found` atau `Version conflicts`

**Solusi**:

1. **Clear cache Composer**:
   ```bash
   composer clear-cache
   composer install --no-cache
   ```

2. **Update Composer**:
   ```bash
   composer self-update
   composer install
   ```

3. **Check versi PHP**:
   ```bash
   php -v
   # Laravel Nusa memerlukan PHP ≥ 8.2
   ```

4. **Install dengan versi spesifik**:
   ```bash
   composer require creasi/laravel-nusa:^1.0
   ```

### Database File Tidak Ditemukan

**Error**: `Database file not found` atau `SQLSTATE[HY000] [14]`

**Solusi**:

```bash
# Check apakah file database ada
ls -la database/nusa.sqlite

# Jika tidak ada, package akan membuat otomatis
# Pastikan direktori database dapat ditulis
chmod 755 database/
chmod 664 database/nusa.sqlite

# Restart aplikasi
php artisan config:clear
php artisan cache:clear
```

### Permission Denied

**Error**: `Permission denied` saat mengakses database

**Solusi**:

```bash
# Fix permission direktori database
sudo chown -R www-data:www-data database/
sudo chmod -R 755 database/

# Untuk development lokal
sudo chown -R $USER:$USER database/
chmod 664 database/nusa.sqlite
```

## Masalah Database

### Koneksi Database Gagal

**Error**: `SQLSTATE[HY000] [2002] Connection refused`

**Solusi**:

1. **Check konfigurasi koneksi**:
   ```php
   // config/database.php
   'nusa' => [
       'driver' => 'sqlite',
       'database' => database_path('nusa.sqlite'),
       'prefix' => '',
       'foreign_key_constraints' => true,
   ],
   ```

2. **Test koneksi**:
   ```bash
   php artisan tinker
   >>> DB::connection('nusa')->getPdo()
   ```

3. **Check file database**:
   ```bash
   file database/nusa.sqlite
   # Harus menampilkan: SQLite 3.x database
   ```

### Database Corrupt

**Error**: `Database disk image is malformed`

**Solusi**:

```bash
# Backup database lama
cp database/nusa.sqlite database/nusa.sqlite.backup

# Download database fresh
rm database/nusa.sqlite
php artisan vendor:publish --tag=creasi-nusa-database --force

# Atau gunakan SQLite recovery
sqlite3 database/nusa.sqlite.backup ".recover" | sqlite3 database/nusa.sqlite
```

### Query Timeout

**Error**: `Maximum execution time exceeded`

**Solusi**:

```php
// Increase timeout untuk query besar
DB::connection('nusa')->getPdo()->setAttribute(PDO::ATTR_TIMEOUT, 60);

// Atau gunakan pagination
$villages = Village::paginate(100); // Bukan Village::all()
```

### Foreign Key Constraint Errors

**Error**: `FOREIGN KEY constraint failed`

**Solusi**:

```php
// Disable foreign key checks sementara
DB::connection('nusa')->statement('PRAGMA foreign_keys = OFF');
// Lakukan operasi
DB::connection('nusa')->statement('PRAGMA foreign_keys = ON');

// Atau check integritas data
DB::connection('nusa')->statement('PRAGMA integrity_check');
```

## Masalah Konfigurasi

### Config Cache Issues

**Error**: Konfigurasi tidak ter-update setelah perubahan

**Solusi**:

```bash
# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
```

### Environment Variables Tidak Terbaca

**Error**: Environment variables tidak ter-load

**Solusi**:

```bash
# Check file .env
cat .env | grep CREASI_NUSA

# Pastikan tidak ada spasi di sekitar =
CREASI_NUSA_CONNECTION=nusa  # ✓ Benar
CREASI_NUSA_CONNECTION = nusa  # ✗ Salah

# Restart server development
php artisan serve
```

### Service Provider Tidak Terdaftar

**Error**: `Class 'Creasi\Nusa\NusaServiceProvider' not found`

**Solusi**:

```bash
# Check apakah package ter-install
composer show creasi/laravel-nusa

# Re-install jika perlu
composer remove creasi/laravel-nusa
composer require creasi/laravel-nusa

# Clear autoload
composer dump-autoload
```

### Middleware Conflicts

**Error**: Middleware conflicts dengan package lain

**Solusi**:

```php
// config/creasi/nusa.php
'api' => [
    'middleware' => ['api'], // Remove conflicting middleware
],

// Atau disable API jika tidak digunakan
'api' => [
    'enabled' => false,
],
```

## Masalah Model dan Query

### Model Tidak Ditemukan

**Error**: `Class 'Creasi\Nusa\Models\Province' not found`

**Solusi**:

```bash
# Check autoload
composer dump-autoload

# Check namespace import
use Creasi\Nusa\Models\Province;

# Test di tinker
php artisan tinker
>>> \Creasi\Nusa\Models\Province::count()
```

### Relationship Tidak Bekerja

**Error**: Relationship mengembalikan null atau empty

**Solusi**:

```php
// Check foreign key constraints
$province = Province::find('33');
$regencies = $province->regencies; // Pastikan ada data

// Debug relationship
$province = Province::with('regencies')->find('33');
dd($province->regencies);

// Check database integrity
DB::connection('nusa')->select('PRAGMA foreign_key_check');
```

### Memory Issues dengan Large Datasets

**Error**: `Fatal error: Allowed memory size exhausted`

**Solusi**:

```php
// Gunakan pagination
$villages = Village::paginate(100); // Bukan Village::all()

// Gunakan chunking untuk processing
Village::chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Process village
    }
});

// Increase memory limit
ini_set('memory_limit', '512M');
```

### Slow Query Performance

**Error**: Query lambat atau timeout

**Solusi**:

```php
// Gunakan eager loading
$provinces = Province::with(['regencies.districts'])->get();

// Limit kolom yang di-select
$provinces = Province::select(['code', 'name'])->get();

// Gunakan indexing
DB::connection('nusa')->statement('CREATE INDEX idx_village_regency ON villages(regency_code)');

// Enable query logging untuk debug
DB::connection('nusa')->enableQueryLog();
// ... jalankan query
dd(DB::connection('nusa')->getQueryLog());
```

## Masalah Performa

### Aplikasi Lambat

**Gejala**: Response time tinggi saat mengakses data wilayah

**Solusi**:

```php
// 1. Enable caching
// config/creasi/nusa.php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 jam
],

// 2. Gunakan eager loading
$provinces = Province::with('regencies')->get();

// 3. Limit data yang dimuat
$provinces = Province::select(['code', 'name'])->get();

// 4. Gunakan pagination
$villages = Village::paginate(50);
```

### Memory Usage Tinggi

**Gejala**: Aplikasi menggunakan memory berlebihan

**Solusi**:

```php
// Hindari loading semua data sekaligus
// ❌ Jangan lakukan ini
$allVillages = Village::all(); // 83,762 records!

// ✅ Gunakan pagination atau chunking
Village::chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Process village
    }
});

// ✅ Atau gunakan lazy loading
foreach (Village::lazy() as $village) {
    // Process village satu per satu
}
```

### Database Lock Issues

**Error**: `Database is locked`

**Solusi**:

```bash
# Check proses yang menggunakan database
lsof database/nusa.sqlite

# Kill proses jika perlu
kill -9 <process_id>

# Atau restart web server
sudo systemctl restart apache2
# atau
sudo systemctl restart nginx
```

## Masalah Development

### Hot Reload Tidak Bekerja

**Gejala**: Perubahan kode tidak ter-reload otomatis

**Solusi**:

```bash
# Clear semua cache
php artisan optimize:clear

# Restart development server
php artisan serve

# Check file watcher
npm run dev
```

### Testing Issues

**Error**: Test gagal dengan database connection

**Solusi**:

```php
// tests/TestCase.php
protected function setUp(): void
{
    parent::setUp();

    // Gunakan in-memory database untuk testing
    config(['database.connections.nusa.database' => ':memory:']);

    // Atau copy database untuk testing
    copy(database_path('nusa.sqlite'), database_path('nusa_test.sqlite'));
    config(['database.connections.nusa.database' => database_path('nusa_test.sqlite')]);
}
```

### Submodule Issues

**Error**: Git submodule tidak ter-update

**Solusi**:

```bash
# Update submodules
git submodule update --init --recursive

# Force update
git submodule update --remote --force

# Check submodule status
git submodule status
```

## Masalah API

### CORS Errors

**Error**: `Access-Control-Allow-Origin` error di browser

**Solusi**:

```php
// config/cors.php
'paths' => ['api/*', 'nusa/*'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'allowed_origins' => ['*'], // Atau domain spesifik
'allowed_headers' => ['*'],
```

### Rate Limiting

**Error**: `Too Many Requests` (429)

**Solusi**:

```php
// config/creasi/nusa.php
'api' => [
    'middleware' => ['api', 'throttle:120,1'], // Increase limit
],

// Atau disable rate limiting untuk development
'api' => [
    'middleware' => ['api'],
],
```

### JSON Response Issues

**Error**: Response tidak dalam format JSON yang benar

**Solusi**:

```php
// Check Accept header
$response = Http::withHeaders([
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
])->get('/nusa/provinces');

// Check response format
if ($response->successful()) {
    $data = $response->json();
} else {
    Log::error('API Error', ['response' => $response->body()]);
}
```

## Mendapatkan Bantuan

### Debug Information

Sebelum melaporkan masalah, kumpulkan informasi debug:

```bash
# Versi PHP
php -v

# Versi Laravel
php artisan --version

# Versi Laravel Nusa
composer show creasi/laravel-nusa

# Check ekstensi PHP
php -m | grep -E "(sqlite|pdo)"

# Check konfigurasi database
php artisan tinker
>>> config('database.connections.nusa')
```

### Log Files

Check log files untuk error details:

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# PHP logs
tail -f /var/log/php8.2-fpm.log
```

### Reporting Issues

Saat melaporkan masalah:

1. **Sertakan informasi environment**:
   - Versi PHP, Laravel, dan Laravel Nusa
   - Operating system
   - Web server (Apache/Nginx)

2. **Sertakan error message lengkap**:
   - Stack trace
   - Log entries
   - Steps to reproduce

3. **Sertakan konfigurasi**:
   - Database configuration
   - Laravel Nusa configuration
   - Environment variables

### Community Support

- **GitHub Issues**: [Laporkan bug](https://github.com/creasico/laravel-nusa/issues)
- **Discussions**: [Diskusi komunitas](https://github.com/orgs/creasico/discussions)
- **Documentation**: [Dokumentasi lengkap](https://laravel-nusa.creasico.dev)

### Professional Support

Untuk dukungan prioritas atau konsultasi:
- Email: support@creasico.dev
- Website: [https://creasico.dev](https://creasico.dev)

Panduan troubleshooting ini mencakup masalah paling umum yang mungkin Anda temui. Jika masalah Anda tidak tercakup di sini, jangan ragu untuk menghubungi komunitas atau tim support.
