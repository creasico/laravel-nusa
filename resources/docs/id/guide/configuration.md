# Konfigurasi

Panduan lengkap untuk mengkonfigurasi Laravel Nusa sesuai dengan kebutuhan aplikasi Anda, termasuk pengaturan database, API, dan fitur-fitur lanjutan.

## File Konfigurasi

Setelah instalasi, Laravel Nusa mempublikasikan file konfigurasi di `config/nusa.php`. File ini berisi semua pengaturan yang dapat Anda kustomisasi:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection to use for Nusa data. By default, this uses
    | a separate SQLite database included with the package.
    |
    */
    'database' => [
        'connection' => 'nusa',
        'path' => database_path('nusa.sqlite'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the RESTful API endpoints.
    |
    */
    'api' => [
        'enabled' => true,
        'prefix' => 'nusa',
        'middleware' => ['api'],
        'rate_limit' => '60,1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Custom model classes if you want to extend the default models.
    |
    */
    'models' => [
        'province' => \Creasi\Nusa\Models\Province::class,
        'regency' => \Creasi\Nusa\Models\Regency::class,
        'district' => \Creasi\Nusa\Models\District::class,
        'village' => \Creasi\Nusa\Models\Village::class,
        'address' => \Creasi\Nusa\Models\Address::class,
    ],
];
```

## Konfigurasi Database

### Database SQLite (Default)

Secara default, Laravel Nusa menggunakan database SQLite terpisah untuk performa optimal dan isolasi data:

```php
// config/database.php
'nusa' => [
    'driver' => 'sqlite',
    'database' => database_path('nusa.sqlite'),
    'prefix' => '',
    'foreign_key_constraints' => true,
],
```

### Menggunakan Database Utama

Untuk menggunakan database utama aplikasi Anda:

```php
// config/nusa.php
'database' => [
    'connection' => 'mysql', // atau 'pgsql'
    'prefix' => 'nusa_',
],
```

Kemudian jalankan migrasi pada database utama:

```bash
php artisan migrate --database=mysql
```

### Path Database Kustom

Untuk menggunakan lokasi database SQLite kustom:

```php
// config/nusa.php
'database' => [
    'connection' => 'nusa',
    'path' => storage_path('app/custom-nusa.sqlite'),
],
```

## Konfigurasi API

### Mengaktifkan/Menonaktifkan API

```php
// config/nusa.php
'api' => [
    'enabled' => true, // Set false untuk menonaktifkan API
    'prefix' => 'nusa',
    'middleware' => ['api'],
],
```

### Prefix API Kustom

```php
'api' => [
    'enabled' => true,
    'prefix' => 'indonesia', // Prefix kustom
    'middleware' => ['api'],
],
```

Ini akan membuat endpoint tersedia di `/indonesia/provinces` alih-alih `/nusa/provinces`.

### Middleware API

Tambahkan autentikasi atau middleware kustom:

```php
'api' => [
    'enabled' => true,
    'prefix' => 'nusa',
    'middleware' => ['api', 'auth:sanctum'], // Tambahkan autentikasi
],
```

### Rate Limiting

Konfigurasi batas rate API:

```php
'api' => [
    'enabled' => true,
    'prefix' => 'nusa',
    'middleware' => ['api'],
    'rate_limit' => '100,1', // 100 request per menit
],
```

## Konfigurasi Model

### Menggunakan Model Kustom

Extend model default dengan fungsionalitas Anda sendiri:

```php
// app/Models/CustomProvince.php
namespace App\Models;

use Creasi\Nusa\Models\Province as BaseProvince;

class CustomProvince extends BaseProvince
{
    protected $appends = ['display_name'];

    public function getDisplayNameAttribute()
    {
        return "Provinsi {$this->name}";
    }

    public function businesses()
    {
        return $this->hasMany(Business::class, 'province_code', 'code');
    }
}
```

Kemudian update konfigurasi:

```php
// config/nusa.php
'models' => [
    'province' => \App\Models\CustomProvince::class,
    'regency' => \Creasi\Nusa\Models\Regency::class,
    'district' => \Creasi\Nusa\Models\District::class,
    'village' => \Creasi\Nusa\Models\Village::class,
    'address' => \Creasi\Nusa\Models\Address::class,
],
```

## Konfigurasi Performa

### Caching

Aktifkan caching untuk data yang sering diakses:

```php
// config/nusa.php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 jam
    'prefix' => 'nusa',
],
```

### Optimasi Memory

Untuk dataset besar, konfigurasi batas memory:

```php
// config/nusa.php
'memory' => [
    'limit' => '512M',
    'chunk_size' => 1000,
],
```

## Konfigurasi Spesifik Environment

### Environment Development

```php
// config/nusa.php
if (app()->environment('local')) {
    return [
        'database' => [
            'connection' => 'nusa',
            'path' => database_path('nusa-dev.sqlite'),
        ],
        'api' => [
            'enabled' => true,
            'prefix' => 'nusa',
            'middleware' => ['api'],
            'rate_limit' => '1000,1', // Batas lebih tinggi untuk development
        ],
    ];
}
```

### Environment Production

```php
// config/nusa.php
if (app()->environment('production')) {
    return [
        'database' => [
            'connection' => 'mysql',
            'prefix' => 'nusa_',
        ],
        'api' => [
            'enabled' => true,
            'prefix' => 'nusa',
            'middleware' => ['api', 'auth:sanctum', 'throttle:60,1'],
        ],
        'cache' => [
            'enabled' => true,
            'ttl' => 7200, // 2 jam
        ],
    ];
}
```

## Konfigurasi Lanjutan

### Service Provider Kustom

Buat service provider kustom untuk konfigurasi lanjutan:

```php
// app/Providers/NusaServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Creasi\Nusa\Models\Province;

class NusaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Binding model kustom
        $this->app->bind(
            \Creasi\Nusa\Models\Province::class,
            \App\Models\CustomProvince::class
        );

        // Rule validasi kustom
        Validator::extend('valid_village_code', function ($attribute, $value, $parameters, $validator) {
            return Village::where('code', $value)->exists();
        });
    }
}
```

### Rule Validasi Kustom

Tambahkan rule validasi kustom untuk data lokasi:

```php
// app/Rules/ValidLocationHierarchy.php
class ValidLocationHierarchy implements Rule
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function passes($attribute, $value)
    {
        $village = Village::find($value);

        if (!$village) {
            return false;
        }

        // Validasi konsistensi hierarki
        if (isset($this->data['district_code'])) {
            return $village->district_code === $this->data['district_code'];
        }

        return true;
    }

    public function message()
    {
        return 'Hierarki lokasi tidak konsisten.';
    }
}
```

## Validasi Konfigurasi

### Validasi Konfigurasi

Buat command untuk memvalidasi konfigurasi Anda:

```bash
php artisan nusa:config:validate
```

### Health Check Konfigurasi

```php
// app/Console/Commands/NusaHealthCheck.php
class NusaHealthCheck extends Command
{
    protected $signature = 'nusa:health';

    public function handle()
    {
        $this->info('Memeriksa konfigurasi Laravel Nusa...');

        // Periksa koneksi database
        try {
            Province::count();
            $this->info('✓ Koneksi database berfungsi');
        } catch (Exception $e) {
            $this->error('✗ Koneksi database gagal: ' . $e->getMessage());
        }

        // Periksa endpoint API
        if (config('nusa.api.enabled')) {
            $this->info('✓ Endpoint API diaktifkan');
        } else {
            $this->warn('! Endpoint API dinonaktifkan');
        }

        // Periksa integritas data
        $provinceCount = Province::count();
        if ($provinceCount === 34) {
            $this->info("✓ Semua {$provinceCount} provinsi dimuat");
        } else {
            $this->error("✗ Diharapkan 34 provinsi, ditemukan {$provinceCount}");
        }
    }
}
```

## Troubleshooting Konfigurasi

### Masalah Umum

1. **Error koneksi database**
   ```bash
   php artisan config:clear
   php artisan nusa:install --force
   ```

2. **Endpoint API tidak berfungsi**
   ```bash
   php artisan route:clear
   php artisan config:clear
   ```

3. **Model kustom tidak dimuat**
   ```bash
   composer dump-autoload
   php artisan config:clear
   ```

## Langkah Selanjutnya

Setelah mengkonfigurasi Laravel Nusa:

1. **[Models](/id/guide/models)** - Memahami model data
2. **[Alamat](/id/guide/addresses)** - Manajemen alamat
3. **[API](/id/guide/api)** - Menggunakan RESTful API
4. **[Kustomisasi](/id/guide/customization)** - Kustomisasi lanjutan

---

*Konfigurasi Laravel Nusa agar sesuai sempurna dengan kebutuhan aplikasi Anda.*
