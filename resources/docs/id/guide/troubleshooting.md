# Troubleshooting

Panduan lengkap untuk mengatasi masalah umum yang mungkin terjadi saat menggunakan Laravel Nusa, termasuk solusi untuk masalah instalasi, konfigurasi, performa, dan integrasi.

## Masalah Instalasi Umum

### Error Koneksi Database

**Masalah**: `SQLSTATE[HY000] [14] unable to open database file`

**Solusi**:
```bash
# Check if SQLite file exists
ls -la database/nusa.sqlite

# Create the file if missing
touch database/nusa.sqlite

# Set proper permissions
chmod 664 database/nusa.sqlite
chown www-data:www-data database/nusa.sqlite

# Re-download data
php artisan nusa:download --force
```

### Memory Limit Exceeded

**Problem**: `Fatal error: Allowed memory size exhausted`

**Solution**:
```bash
# Increase memory limit temporarily
php -d memory_limit=512M artisan nusa:install

# Or update php.ini
memory_limit = 512M

# Or set in Laravel Nusa config
// config/nusa.php
'memory_limit' => '512M',
```

### Permission Denied Errors

**Problem**: Permission denied when accessing database files

**Solution**:
```bash
# Fix directory permissions
chmod 755 database/
chmod 664 database/nusa.sqlite

# For web server
chown -R www-data:www-data database/
chown -R www-data:www-data storage/

# For development
chmod -R 775 database/
chmod -R 775 storage/
```

## Configuration Issues

### API Routes Not Working

**Problem**: API endpoints return 404 errors

**Solution**:
```bash
# Clear route cache
php artisan route:clear

# Check if API is enabled
php artisan config:show nusa.api.enabled

# Verify routes are registered
php artisan route:list | grep nusa

# Check middleware configuration
// config/nusa.php
'api' => [
    'enabled' => true,
    'middleware' => ['api'], // Remove auth if not needed
],
```

### Models Not Found

**Problem**: `Class 'Creasi\Nusa\Models\Province' not found`

**Solution**:
```bash
# Clear autoload cache
composer dump-autoload

# Check if package is installed
composer show creasi/laravel-nusa

# Reinstall if necessary
composer remove creasi/laravel-nusa
composer require creasi/laravel-nusa
```

### Configuration Cache Issues

**Problem**: Configuration changes not taking effect

**Solution**:
```bash
# Clear configuration cache
php artisan config:clear

# Clear all caches
php artisan optimize:clear

# Republish configuration
php artisan vendor:publish --tag=nusa-config --force
```

## Performance Issues

### Slow Query Performance

**Problem**: Queries taking too long to execute

**Solution**:
```php
// Use select to limit fields
$provinces = Province::select('code', 'name')->get();

// Use pagination for large datasets
$villages = Village::paginate(50);

// Add database indexes
Schema::table('your_table', function (Blueprint $table) {
    $table->index('village_code');
    $table->index(['latitude', 'longitude']);
});

// Use eager loading
$villages = Village::with(['district.regency.province'])->get();
```

### Memory Usage Issues

**Problem**: High memory consumption

**Solution**:
```php
// Use chunk processing for large datasets
Village::chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Process village
    }
});

// Use cursor for memory-efficient iteration
foreach (Village::cursor() as $village) {
    // Process village
}

// Clear model cache periodically
Model::clearBootedModels();
```

### API Rate Limiting

**Problem**: Too many API requests

**Solution**:
```php
// Increase rate limits
// config/nusa.php
'api' => [
    'rate_limit' => '120,1', // 120 requests per minute
],

// Implement caching
$provinces = Cache::remember('provinces', 3600, function () {
    return Province::all();
});

// Use batch requests
$codes = ['33', '32', '31'];
$provinces = Province::whereIn('code', $codes)->get();
```

## Data Issues

### Missing or Outdated Data

**Problem**: Some regions are missing or have outdated information

**Solution**:
```bash
# Update to latest data
php artisan nusa:update --force

# Check data integrity
php artisan nusa:check

# Re-download if corrupted
php artisan nusa:download --force

# Verify data counts
php artisan tinker
>>> Province::count() // Should be 34
>>> Regency::count()  // Should be 514+
>>> Village::count()  // Should be 83,000+
```

### Inconsistent Hierarchy

**Problem**: Village doesn't belong to selected district/regency

**Solution**:
```php
// Validate hierarchy in form requests
public function rules()
{
    return [
        'village_code' => [
            'required',
            'exists:nusa.villages,code',
            function ($attribute, $value, $fail) {
                $village = Village::find($value);
                if ($village && $village->district_code !== $this->district_code) {
                    $fail('Village does not belong to selected district.');
                }
            },
        ],
    ];
}

// Check hierarchy programmatically
$village = Village::find('33.74.01.1001');
if ($village->district_code !== $expectedDistrictCode) {
    throw new InvalidArgumentException('Invalid hierarchy');
}
```

## Integration Issues

### Trait Conflicts

**Problem**: Trait method conflicts with existing model methods

**Solution**:
```php
// Use trait aliases
class User extends Model
{
    use WithVillage {
        WithVillage::village as nusaVillage;
    }
    
    // Your custom village method
    public function village()
    {
        // Custom implementation
    }
}

// Or exclude conflicting methods
class User extends Model
{
    use WithVillage {
        village as private;
    }
    
    public function village()
    {
        // Your implementation
    }
}
```

### Relationship Issues

**Problem**: Relationships not working as expected

**Solution**:
```php
// Check foreign key configuration
public function village()
{
    return $this->belongsTo(Village::class, 'village_code', 'code');
}

// Verify database connection
// config/nusa.php
'database' => [
    'connection' => 'nusa',
],

// Check if connection exists
// config/database.php
'nusa' => [
    'driver' => 'sqlite',
    'database' => database_path('nusa.sqlite'),
],
```

## Getting Help

### Debug Information

When reporting issues, include:

```bash
# Laravel version
php artisan --version

# Laravel Nusa version
composer show creasi/laravel-nusa

# PHP version and extensions
php -v
php -m | grep -E "(sqlite|pdo)"

# Check configuration
php artisan config:show nusa

# Check data integrity
php artisan nusa:check
```

### Community Support

- **GitHub Issues**: [Report bugs and request features](https://github.com/creasico/laravel-nusa/issues)
- **Discussions**: [Community Q&A](https://github.com/creasico/laravel-nusa/discussions)
- **Documentation**: [Complete guides and API reference](/id/guide/getting-started)

## Next Steps

- **[Development Setup](/id/guide/development)** - Development environment configuration
- **[API Reference](/id/api/overview)** - Complete API documentation
- **[GitHub Issues](https://github.com/creasico/laravel-nusa/issues)** - Report problems
- **[Contributing Guide](https://github.com/creasico/laravel-nusa/blob/main/CONTRIBUTING.md)** - Help improve Laravel Nusa
