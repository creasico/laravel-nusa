# Configuration

Laravel Nusa is designed to work out of the box with sensible defaults, but it provides several configuration options to customize its behavior according to your application's needs.

## Publishing Configuration

To customize Laravel Nusa's configuration, first publish the config file:

```bash
php artisan vendor:publish --tag=creasi-nusa-config
```

This creates `config/creasi/nusa.php` with all available configuration options.

## Configuration Options

### Database Connection

```php
'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),
```

**Default**: `nusa`

Specifies the database connection name for Laravel Nusa data. The package automatically configures a SQLite connection, but you can customize it.

#### Custom Database Connection

To use a different database connection, add it to `config/database.php`:

```php
'connections' => [
    // Your existing connections...
    
    'indonesia' => [
        'driver' => 'sqlite',
        'database' => database_path('indonesia.sqlite'),
        'prefix' => '',
        'foreign_key_constraints' => true,
    ],
    
    // Or use MySQL/PostgreSQL
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

Then update your environment:

```dotenv
CREASI_NUSA_CONNECTION=indonesia
```

### Table Names

```php
'table_names' => [
    'provinces' => 'provinces',
    'regencies' => 'regencies',
    'districts' => 'districts',
    'villages' => 'villages',
],
```

Customize table names if you need different naming conventions:

```php
'table_names' => [
    'provinces' => 'provinsi',
    'regencies' => 'kabupaten_kota',
    'districts' => 'kecamatan',
    'villages' => 'kelurahan_desa',
],
```

### Address Model

```php
'addressable' => \Creasi\Nusa\Models\Address::class,
```

Specify which model to use for address management. You can create your own implementation:

```php
// Create your custom address model
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

// Update configuration
'addressable' => \App\Models\CustomAddress::class,
```

### API Routes

```php
'routes_enable' => env('CREASI_NUSA_ROUTES_ENABLE', true),
'routes_prefix' => env('CREASI_NUSA_ROUTES_PREFIX', 'nusa'),
```

Control API route registration and customize the route prefix.

## Environment Variables

Add these variables to your `.env` file for easy configuration:

```dotenv
# Database connection
CREASI_NUSA_CONNECTION=nusa

# API routes
CREASI_NUSA_ROUTES_ENABLE=true
CREASI_NUSA_ROUTES_PREFIX=nusa

# Custom database settings (if using custom connection)
INDONESIA_DB_HOST=127.0.0.1
INDONESIA_DB_DATABASE=indonesia_data
INDONESIA_DB_USERNAME=indonesia_user
INDONESIA_DB_PASSWORD=secret_password
```

## Advanced Configuration

### Custom Service Provider

For advanced customization, you can create your own service provider:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Creasi\Nusa\Contracts;
use App\Models\CustomAddress;

class NusaServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Override default address model
        $this->app->bind(Contracts\Address::class, CustomAddress::class);
        
        // Add custom database connection
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
        // Custom route registration
        if (config('app.env') === 'production') {
            // Disable routes in production
            config(['creasi.nusa.routes_enable' => false]);
        }
    }
}
```

Register your service provider in `config/app.php`:

```php
'providers' => [
    // Other providers...
    App\Providers\NusaServiceProvider::class,
],
```

### Middleware Configuration

Apply middleware to API routes:

```php
// In your RouteServiceProvider or custom service provider
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'throttle:60,1'])
    ->prefix('api/indonesia')
    ->name('indonesia.')
    ->group(base_path('vendor/creasi/laravel-nusa/routes/nusa.php'));
```

### Custom Route Registration

Disable default routes and register your own:

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
    // Add more routes as needed
});
```

## Performance Configuration

### Database Optimization

For high-traffic applications, consider these optimizations:

```php
// config/database.php - SQLite optimizations
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

// For MySQL/PostgreSQL
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

### Caching Configuration

Implement caching for frequently accessed data:

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
        'database' => 2, // Use different database for Nusa cache
    ],
],
```

Create a caching service:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class NusaCacheService
{
    protected $cacheStore = 'nusa';
    protected $ttl = 3600; // 1 hour
    
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

## Security Configuration

### API Security

Protect API endpoints in production:

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('nusa/provinces', [ProvinceController::class, 'index']);
    // Other protected routes...
});

// Or use API keys
Route::middleware(['api.key', 'throttle:100,1'])->group(function () {
    // Protected routes
});
```

### CORS Configuration

Configure CORS for frontend applications:

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

## Testing Configuration

For testing environments:

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

## Configuration Validation

Create a command to validate your configuration:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Creasi\Nusa\Models\Province;

class ValidateNusaConfig extends Command
{
    protected $signature = 'nusa:validate-config';
    protected $description = 'Validate Laravel Nusa configuration';
    
    public function handle()
    {
        $this->info('Validating Laravel Nusa configuration...');
        
        // Test database connection
        try {
            $count = Province::count();
            $this->info("✓ Database connection working. Found {$count} provinces.");
        } catch (\Exception $e) {
            $this->error("✗ Database connection failed: {$e->getMessage()}");
            return 1;
        }
        
        // Test configuration values
        $connection = config('creasi.nusa.connection');
        $this->info("✓ Using connection: {$connection}");
        
        $routesEnabled = config('creasi.nusa.routes_enable');
        $this->info("✓ API routes " . ($routesEnabled ? 'enabled' : 'disabled'));
        
        $prefix = config('creasi.nusa.routes_prefix');
        $this->info("✓ Route prefix: {$prefix}");
        
        $this->info('Configuration validation completed successfully!');
        return 0;
    }
}
```

## Troubleshooting Configuration

### Common Issues

1. **Database Connection Errors**
   ```bash
   # Check if SQLite file exists and is readable
   ls -la vendor/creasi/laravel-nusa/database/nusa.sqlite
   
   # Test connection
   php artisan tinker
   >>> \Creasi\Nusa\Models\Province::count()
   ```

2. **Route Conflicts**
   ```bash
   # Check registered routes
   php artisan route:list | grep nusa
   
   # Clear route cache
   php artisan route:clear
   ```

3. **Configuration Cache Issues**
   ```bash
   # Clear configuration cache
   php artisan config:clear
   
   # Rebuild cache
   php artisan config:cache
   ```

### Debug Mode

Enable debug mode for troubleshooting:

```php
// In your service provider or configuration
if (config('app.debug')) {
    // Enable query logging for Nusa connection
    DB::connection('nusa')->enableQueryLog();
    
    // Log all Nusa queries
    DB::connection('nusa')->listen(function ($query) {
        Log::debug('Nusa Query', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time
        ]);
    });
}
```

This comprehensive configuration guide covers all aspects of customizing Laravel Nusa for your specific needs, from basic settings to advanced performance and security configurations.
