# Konfigurasi

Laravel Nusa dirancang untuk berfungsi secara *out of the box* dengan *default* yang masuk akal, tetapi ia menyediakan beberapa opsi konfigurasi untuk menyesuaikan perilakunya sesuai dengan kebutuhan aplikasi Anda.

## Publikasi Konfigurasi

Untuk menyesuaikan konfigurasi Laravel Nusa, pertama publikasikan file konfigurasi:

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

Menentukan nama koneksi database untuk data Laravel Nusa. Paket ini secara otomatis mengkonfigurasi koneksi SQLite, tetapi Anda dapat menyesuaikannya.

#### Koneksi Database Kustom

Untuk menggunakan koneksi database yang berbeda, tambahkan ke `config/database.php`:

```php
'connections' => [
    // Koneksi Anda yang sudah ada...
    
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
    ],
],
```

Kemudian perbarui lingkungan Anda:

```dotenv
CREASI_NUSA_CONNECTION=indonesia
```

### Nama Tabel

```php
'table_names' => [
    'provinces' => 'provinces',
    'regencies' => 'regencies',
    'districts' => 'districts',
    'villages' => 'villages',
],
```

Sesuaikan nama tabel jika Anda membutuhkan konvensi penamaan yang berbeda:

```php
'table_names' => [
    'provinces' => 'provinsi',
    'regencies' => 'kabupaten_kota',
    'districts' => 'kecamatan',
    'villages' => 'kelurahan_desa',
],
```

### Model Alamat

```php
'addressable' => \Creasi\Nusa\Models\Address::class,
```

Tentukan model mana yang akan digunakan untuk manajemen alamat. Anda dapat membuat implementasi Anda sendiri:

```php
// Buat model alamat kustom Anda
class CustomAddress extends \Creasi\Nusa\Models\Address
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'address_line',
        'postal_code',
        'is_default',
        'notes'
    ];
    
    protected $casts = [
        'is_default' => 'boolean',
    ];
}

// Perbarui konfigurasi
'addressable' => \App\Models\CustomAddress::class,
```

### Rute API

```php
'routes_enable' => env('CREASI_NUSA_ROUTES_ENABLE', true),
'routes_prefix' => env('CREASI_NUSA_ROUTES_PREFIX', 'nusa'),
```

Kontrol registrasi rute API dan sesuaikan awalan rute.

## Variabel Lingkungan

Tambahkan variabel-variabel ini ke file `.env` Anda untuk konfigurasi yang mudah:

```dotenv
# Koneksi database
CREASI_NUSA_CONNECTION=nusa

# Rute API
CREASI_NUSA_ROUTES_ENABLE=true
CREASI_NUSA_ROUTES_PREFIX=nusa

# Pengaturan database kustom (jika menggunakan koneksi kustom)
INDONESIA_DB_HOST=127.0.0.1
INDONESIA_DB_DATABASE=indonesia_data
INDONESIA_DB_USERNAME=indonesia_user
INDONESIA_DB_PASSWORD=secret_password
```

## Konfigurasi Lanjutan

### Penyedia Layanan Kustom

Untuk kustomisasi lanjutan, Anda dapat membuat penyedia layanan Anda sendiri:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Creasi\Nusa\Contracts;
use App\Models\CustomAddress;

class NusaServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Timpa model alamat default
        $this->app->bind(Contracts\Address::class, CustomAddress::class);
        
        // Tambahkan koneksi database kustom
        config([
            'database.connections.indonesia' => [
                'driver' => 'mysql',
                'host' => env('INDONESIA_DB_HOST', '127.0.0.1'),
                'database' => env('INDONESIA_DB_DATABASE', 'indonesia'),
                'username' => env('INDONESIA_DB_USERNAME', 'root'),
                'password' => env('INDONESIA_DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]
        ]);
    }
    
    public function boot()
    {
        // Registrasi rute kustom
        if (config('app.env') === 'production') {
            // Nonaktifkan rute dalam produksi
            config(['creasi.nusa.routes_enable' => false]);
        }
    }
}
```

Daftarkan penyedia layanan Anda di `config/app.php`:

```php
'providers' => [
    // Penyedia lain...
    App\Providers\NusaServiceProvider::class,
],
```

### Konfigurasi Middleware

Terapkan *middleware* ke rute API:

```php
// Di RouteServiceProvider Anda atau penyedia layanan kustom
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'throttle:60,1'])
    ->prefix('api/indonesia')
    ->name('indonesia.')
    ->group(base_path('vendor/creasi/laravel-nusa/routes/nusa.php'));
```

### Registrasi Rute Kustom

Nonaktifkan rute *default* dan daftarkan rute Anda sendiri:

```dotenv
CREASI_NUSA_ROUTES_ENABLE=false
```

```php
// routes/api.php
use Creasi\Nusa\Http\Controllers\{ProvinceController, RegencyController};

Route::middleware(['auth:api', 'throttle:100,1'])->prefix('v1/indonesia')->group(function () {
    Route::get('provinces', [ProvinceController::class, 'index']);
    Route::get('provinces/{province}', [ProvinceController::class, 'show']);
    Route::get('provinces/{province}/regencies', [ProvinceController::class, 'regencies']);
    
    Route::get('regencies', [RegencyController::class, 'index']);
    Route::get('regencies/{regency}', [RegencyController::class, 'show']);
    // Tambahkan rute lain sesuai kebutuhan
});
```

## Konfigurasi Kinerja

### Optimasi Database

Untuk aplikasi dengan lalu lintas tinggi, pertimbangkan optimasi ini:

```php
// config/database.php - Optimasi SQLite
'nusa' => [
    'driver' => 'sqlite',
    'database' => env('NUSA_DATABASE_PATH', database_path('nusa.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => true,
    'options' => [
        PDO::ATTR_TIMEOUT => 30,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
],

// Untuk MySQL/PostgreSQL
'nusa_mysql' => [
    'driver' => 'mysql',
    'options' => [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ],
    'strict' => true,
    'engine' => 'InnoDB',
],
```

### Konfigurasi Caching

Implementasikan *caching* untuk data yang sering diakses:

```php
// config/cache.php
'stores' => [
    'nusa' => [
        'driver' => 'redis',
        'connection' => 'nusa',
        'prefix' => 'nusa_cache',
    ],
],

'connections' => [
    'nusa' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => 2, // Gunakan database yang berbeda untuk cache Nusa
    ],
],
```

Buat layanan *caching*:

```php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

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

Lindungi *endpoint* API dalam produksi:

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('nusa/provinces', [ProvinceController::class, 'index']);
    // Rute terlindungi lainnya...
});

// Atau gunakan kunci API
Route::middleware(['api.key', 'throttle:100,1'])->group(function () {
    // Rute terlindungi
});
```

### Konfigurasi CORS

Konfigurasi CORS untuk aplikasi *frontend*:

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

## Konfigurasi Pengujian

Untuk lingkungan pengujian:

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

Buat perintah untuk memvalidasi konfigurasi Anda:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Creasi\Nusa\Models\Province;

class ValidateNusaConfig extends Command
{
    protected $signature = 'nusa:validate-config';
    protected $description = 'Validasi konfigurasi Laravel Nusa';
    
    public function handle()
    {
        $this->info('Memvalidasi konfigurasi Laravel Nusa...');
        
        // Uji koneksi database
        try {
            $count = Province::count();
            $this->info("✓ Koneksi database berfungsi. Ditemukan {$count} provinsi.");
        } catch (\Exception $e) {
            $this->error("✗ Koneksi database gagal: {$e->getMessage()}");
            return 1;
        }
        
        // Uji nilai konfigurasi
        $connection = config('creasi.nusa.connection');
        $this->info("✓ Menggunakan koneksi: {$connection}");
        
        $routesEnabled = config('creasi.nusa.routes_enable');
        $this->info("✓ Rute API " . ($routesEnabled ? 'diaktifkan' : 'dinonaktifkan'));
        
        $prefix = config('creasi.nusa.routes_prefix');
        $this->info("✓ Awalan rute: {$prefix}");
        
        $this->info('Validasi konfigurasi berhasil diselesaikan!');
        return 0;
    }
}
```

## Pemecahan Masalah Konfigurasi

### Masalah Umum

1. **Error Koneksi Database**
   ```bash
   # Periksa apakah file SQLite ada dan dapat dibaca
   ls -la vendor/creasi/laravel-nusa/database/nusa.sqlite
   
   # Uji koneksi
   php artisan tinker
   >>> \Creasi\Nusa\Models\Province::count()
   ```

2. **Konflik Rute**
   ```bash
   # Periksa rute yang terdaftar
   php artisan route:list | grep nusa
   
   # Bersihkan cache rute
   php artisan route:clear
   ```

3. **Masalah Cache Konfigurasi**
   ```bash
   # Bersihkan cache konfigurasi
   php artisan config:clear
   
   # Bangun ulang cache
   php artisan config:cache
   ```

### Mode Debug

Aktifkan mode debug untuk pemecahan masalah:

```php
// Di penyedia layanan atau konfigurasi Anda
if (config('app.debug')) {
    // Aktifkan logging kueri untuk koneksi Nusa
    DB::connection('nusa')->enableQueryLog();
    
    // Log semua kueri Nusa
    DB::connection('nusa')->listen(function ($query) {
        Log::debug('Kueri Nusa', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time
        ]);
    });
}
```

Panduan konfigurasi yang komprehensif ini mencakup semua aspek penyesuaian Laravel Nusa untuk kebutuhan spesifik Anda, mulai dari pengaturan dasar hingga konfigurasi kinerja dan keamanan lanjutan.