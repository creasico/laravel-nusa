# Konfigurasi

Laravel Nusa dirancang untuk bekerja langsung dengan pengaturan default yang masuk akal, tetapi menyediakan beberapa opsi konfigurasi untuk menyesuaikan perilakunya sesuai dengan kebutuhan aplikasi Anda.

## Mempublikasikan Konfigurasi

Untuk menyesuaikan konfigurasi Laravel Nusa, pertama publikasikan file config:

```bash
php artisan vendor:publish --tag=creasi-nusa-config
```

Ini membuat `config/creasi/nusa.php` dengan semua opsi konfigurasi yang tersedia.

## Opsi Konfigurasi

### Koneksi Database

```php
'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),
```

**Default**: `nusa`

Menentukan nama koneksi database untuk data Laravel Nusa. Paket secara otomatis mengkonfigurasi koneksi SQLite, tetapi Anda dapat menyesuaikannya.

#### Koneksi Database Kustom

Untuk menggunakan koneksi database yang berbeda, tambahkan ke `config/database.php`:

```php
'connections' => [
    // Koneksi yang sudah ada...
    
    'indonesia' => [
        'driver' => 'sqlite',
        'database' => database_path('indonesia.sqlite'),
        'prefix' => '',
        'foreign_key_constraints' => true,
    ],
    
    // Atau gunakan MySQL/PostgreSQL
    'indonesia_mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'indonesia_data',
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],
],
```

Kemudian update konfigurasi Laravel Nusa:

```php
// config/creasi/nusa.php
'connection' => 'indonesia', // atau 'indonesia_mysql'
```

### Database Path

```php
'database_path' => database_path('nusa.sqlite'),
```

**Default**: `database/nusa.sqlite`

Path ke file database SQLite. Hanya digunakan jika menggunakan koneksi SQLite default.

### API Configuration

#### Enable/Disable API

```php
'api' => [
    'enabled' => env('CREASI_NUSA_API_ENABLED', true),
],
```

**Default**: `true`

Mengontrol apakah endpoint API REST diaktifkan. Set ke `false` untuk menonaktifkan semua route API.

#### API Prefix

```php
'api' => [
    'prefix' => env('CREASI_NUSA_API_PREFIX', 'nusa'),
],
```

**Default**: `nusa`

Prefix untuk semua route API. Dengan prefix default, endpoint akan tersedia di `/nusa/provinces`, `/nusa/regencies`, dll.

#### API Middleware

```php
'api' => [
    'middleware' => ['api', 'throttle:60,1'],
],
```

**Default**: `['api', 'throttle:60,1']`

Middleware yang diterapkan ke semua route API. Secara default termasuk rate limiting 60 request per menit.

#### Custom API Middleware

```php
'api' => [
    'middleware' => [
        'api',
        'auth:sanctum',  // Require authentication
        'throttle:100,1', // Custom rate limit
        'cors',          // Custom CORS handling
    ],
],
```

### Model Configuration

#### Custom Model Classes

```php
'models' => [
    'province' => \App\Models\CustomProvince::class,
    'regency' => \App\Models\CustomRegency::class,
    'district' => \App\Models\CustomDistrict::class,
    'village' => \App\Models\CustomVillage::class,
],
```

**Default**: Menggunakan model bawaan Laravel Nusa

Memungkinkan Anda untuk menggunakan model kustom yang meng-extend model bawaan Laravel Nusa.

#### Model Caching

```php
'cache' => [
    'enabled' => env('CREASI_NUSA_CACHE_ENABLED', true),
    'ttl' => env('CREASI_NUSA_CACHE_TTL', 3600), // 1 hour
    'prefix' => 'nusa',
],
```

**Default**: Enabled dengan TTL 1 jam

Mengkonfigurasi caching untuk query model. Sangat direkomendasikan untuk aplikasi production.

### Search Configuration

#### Search Driver

```php
'search' => [
    'driver' => env('CREASI_NUSA_SEARCH_DRIVER', 'database'),
    'min_length' => 2,
],
```

**Default**: `database`

Driver pencarian yang digunakan. Opsi yang tersedia:
- `database` - Pencarian database sederhana
- `scout` - Laravel Scout (memerlukan konfigurasi tambahan)

#### Search Minimum Length

```php
'search' => [
    'min_length' => 3,
],
```

**Default**: `2`

Panjang minimum query pencarian yang diterima.

### Pagination Configuration

```php
'pagination' => [
    'per_page' => env('CREASI_NUSA_PER_PAGE', 15),
    'max_per_page' => env('CREASI_NUSA_MAX_PER_PAGE', 100),
],
```

**Default**: 15 per halaman, maksimum 100

Mengkonfigurasi pengaturan pagination default untuk API endpoints.

## Environment Variables

Anda dapat menggunakan environment variables untuk mengkonfigurasi Laravel Nusa tanpa mempublikasikan file config:

```dotenv
# Database
CREASI_NUSA_CONNECTION=nusa
CREASI_NUSA_DATABASE_PATH=/path/to/custom/nusa.sqlite

# API
CREASI_NUSA_API_ENABLED=true
CREASI_NUSA_API_PREFIX=indonesia
CREASI_NUSA_PER_PAGE=20
CREASI_NUSA_MAX_PER_PAGE=50

# Cache
CREASI_NUSA_CACHE_ENABLED=true
CREASI_NUSA_CACHE_TTL=7200

# Search
CREASI_NUSA_SEARCH_DRIVER=scout
```

## Advanced Configuration

### Custom Database Connection

Untuk setup database yang lebih kompleks:

```php
// config/database.php
'connections' => [
    'nusa_read' => [
        'driver' => 'mysql',
        'read' => [
            'host' => ['192.168.1.1', '192.168.1.2'],
        ],
        'write' => [
            'host' => ['192.168.1.3'],
        ],
        'database' => 'indonesia_data',
        // ... konfigurasi lainnya
    ],
],
```

```php
// config/creasi/nusa.php
'connection' => 'nusa_read',
```

### Performance Optimization

#### Database Optimization

```php
'database' => [
    'connection' => 'nusa',
    'options' => [
        'enable_foreign_keys' => true,
        'journal_mode' => 'WAL',
        'synchronous' => 'NORMAL',
        'cache_size' => 64000,
        'temp_store' => 'MEMORY',
    ],
],
```

#### Query Optimization

```php
'query' => [
    'chunk_size' => 1000,
    'eager_load' => ['regencies', 'districts'],
    'select_columns' => ['code', 'name'], // Limit selected columns
],
```

### Security Configuration

#### API Security

```php
'api' => [
    'middleware' => [
        'api',
        'auth:sanctum',
        'throttle:api',
        'signed', // Require signed URLs
    ],
    'cors' => [
        'allowed_origins' => ['https://yourdomain.com'],
        'allowed_methods' => ['GET'],
    ],
],
```

#### Data Privacy

```php
'privacy' => [
    'exclude_coordinates' => true,
    'mask_sensitive_data' => true,
    'audit_access' => true,
],
```

### Custom Cache Store

Untuk aplikasi dengan kebutuhan caching khusus:

```php
// config/cache.php
'stores' => [
    'nusa' => [
        'driver' => 'redis',
        'connection' => 'nusa',
        'prefix' => 'nusa_cache',
    ],
],

// config/database.php
'redis' => [
    'nusa' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => 2, // Dedicated database for Nusa
    ],
],
```

### Custom Cache Service

Implementasi layanan cache kustom:

```php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;

class NusaCacheService
{
    protected $cacheStore = 'nusa';
    protected $ttl = 3600; // 1 jam

    public function getProvinces()
    {
        return Cache::store($this->cacheStore)->remember('provinces', $this->ttl, function () {
            return Province::orderBy('name')->get(['code', 'name']);
        });
    }

    public function getRegenciesByProvince(string $provinceCode)
    {
        $key = "regencies.{$provinceCode}";

        return Cache::store($this->cacheStore)->remember($key, $this->ttl, function () use ($provinceCode) {
            return Regency::where('province_code', $provinceCode)
                ->orderBy('name')
                ->get(['code', 'name']);
        });
    }

    public function clearCache()
    {
        Cache::store($this->cacheStore)->flush();
    }
}
```

## Konfigurasi Keamanan

### Keamanan API

Lindungi endpoint API di production:

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('nusa/provinces', [ProvinceController::class, 'index']);
    // Route terlindungi lainnya...
});

// Atau gunakan API keys
Route::middleware(['api.key', 'throttle:100,1'])->group(function () {
    // Route terlindungi
});
```

### Konfigurasi CORS

Konfigurasi CORS untuk aplikasi frontend:

```php
// config/cors.php
'paths' => ['api/*', 'nusa/*'],
'allowed_methods' => ['GET'],
'allowed_origins' => [
    'https://yourdomain.com',
    'https://app.yourdomain.com',
],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
```

## Konfigurasi Testing

Untuk environment testing:

```php
// config/nusa.php
'connection' => env('CREASI_NUSA_CONNECTION',
    app()->environment('testing') ? 'nusa_testing' : 'nusa'
),

// config/database.php
'nusa_testing' => [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
    'foreign_key_constraints' => true,
],
```

## Validasi Konfigurasi

Buat command untuk memvalidasi konfigurasi Anda:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Creasi\Nusa\Models\Province;

class ValidateNusaConfig extends Command
{
    protected $signature = 'nusa:validate-config';
    protected $description = 'Validate konfigurasi Laravel Nusa';

    public function handle()
    {
        $this->info('Memvalidasi konfigurasi Laravel Nusa...');

        // Test koneksi database
        try {
            $provinceCount = Province::count();
            $this->info("✓ Koneksi database berhasil. Ditemukan {$provinceCount} provinsi");

            if ($provinceCount !== 38) {
                $this->error("✗ Diharapkan 38 provinsi, ditemukan {$provinceCount}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("✗ Koneksi database gagal: {$e->getMessage()}");
            return 1;
        }

        // Test cache
        if (config('creasi.nusa.cache.enabled')) {
            try {
                Cache::put('nusa_test', 'test', 60);
                $value = Cache::get('nusa_test');

                if ($value === 'test') {
                    $this->info('✓ Cache berfungsi dengan baik');
                    Cache::forget('nusa_test');
                } else {
                    $this->error('✗ Cache tidak berfungsi dengan baik');
                }
            } catch (\Exception $e) {
                $this->error("✗ Error cache: {$e->getMessage()}");
            }
        }

        $this->info('Validasi konfigurasi selesai!');
        return 0;
    }
}
```

Panduan konfigurasi komprehensif ini mencakup semua aspek penyesuaian Laravel Nusa untuk kebutuhan spesifik Anda, dari pengaturan dasar hingga konfigurasi performa dan keamanan lanjutan.
