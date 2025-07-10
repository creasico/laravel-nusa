# Installation

This guide provides comprehensive installation instructions, configuration options, and troubleshooting for Laravel Nusa.

## System Requirements

### PHP Requirements

- **PHP Version**: 8.2 or higher
- **Required Extensions**:
  - `ext-sqlite3` - For SQLite database support
  - `ext-json` - For JSON handling (usually included)
  - `ext-mbstring` - For string manipulation (usually included)

### Laravel Requirements

Laravel Nusa supports multiple Laravel versions:

- **Laravel 9.x** - Minimum version 9.0
- **Laravel 10.x** - Full support
- **Laravel 11.x** - Full support
- **Laravel 12.x** - Full support

### Server Requirements

- **Disk Space**: ~15MB for the package and database
- **Database**: Uses separate SQLite connection (no impact on your main database)
- **Memory**: No additional memory requirements

## Installation

### Step 1: Install via Composer

```bash
composer require creasi/laravel-nusa
```

### Step 2: Verify Installation

Laravel Nusa automatically configures itself. Verify it's working:

```bash
php artisan tinker
```

```php
// In Tinker - this should return 34
\Creasi\Nusa\Models\Province::count();
```

If you see `34`, the installation was successful!

### Step 3: Test API Routes (Optional)

If you plan to use the API, test the endpoints:

```bash
# Test in your browser or with curl
curl http://your-app.test/nusa/provinces
```

## What Gets Installed

Laravel Nusa automatically sets up:

1. **Service Provider Registration** - Auto-discovered by Laravel
2. **Database Connection** - Adds `nusa` connection to your database config
3. **SQLite Database** - Pre-built database with all Indonesian administrative data
4. **API Routes** - RESTful endpoints (can be disabled)
5. **Eloquent Models** - Ready-to-use models with relationships

## Configuration

Laravel Nusa works out of the box with sensible defaults, but you can customize it for your specific needs.

### Basic Configuration

The most common configuration options can be set via environment variables:

```dotenv
# Enable/disable API routes (default: true)
CREASI_NUSA_ROUTES_ENABLE=true

# Change API route prefix (default: nusa)
CREASI_NUSA_ROUTES_PREFIX=api/indonesia

# Use custom database connection (default: nusa)
CREASI_NUSA_CONNECTION=custom_nusa
```

### Advanced Configuration

For more advanced customization, publish the configuration file:

```bash
php artisan vendor:publish --tag=creasi-nusa-config
```

This creates `config/creasi/nusa.php`:

```php
<?php

return [
    // Database connection name for Nusa data
    'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),

    // Table names (customize if needed)
    'table_names' => [
        'provinces' => 'provinces',
        'regencies' => 'regencies',
        'districts' => 'districts',
        'villages' => 'villages',
    ],

    // Address model implementation
    'addressable' => \Creasi\Nusa\Models\Address::class,

    // API routes configuration
    'routes_enable' => env('CREASI_NUSA_ROUTES_ENABLE', true),
    'routes_prefix' => env('CREASI_NUSA_ROUTES_PREFIX', 'nusa'),
];
```

### Custom Database Connection

If you need to use a different database connection, add it to `config/database.php`:

```php
'connections' => [
    // Your existing connections...

    'indonesia' => [
        'driver' => 'sqlite',
        'database' => database_path('indonesia.sqlite'),
        'prefix' => '',
        'foreign_key_constraints' => true,
    ],
],
```

Then update your environment:

```dotenv
CREASI_NUSA_CONNECTION=indonesia
```

## Optional Features Setup

### Address Management

If you plan to use the address management features for storing user addresses:

#### 1. Publish Migrations

```bash
php artisan vendor:publish --tag=creasi-migrations
```

#### 2. Run Migrations

```bash
php artisan migrate
```

This creates the `addresses` table in your main database for storing user addresses with references to administrative regions.

### API Routes

Laravel Nusa provides RESTful API endpoints by default:

#### Available Routes

```
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

#### Disable API Routes

If you don't need the API endpoints:

```dotenv
CREASI_NUSA_ROUTES_ENABLE=false
```

#### Custom Route Prefix

To change the route prefix from `/nusa` to something else:

```dotenv
CREASI_NUSA_ROUTES_PREFIX=api/indonesia
```

Routes will then be available at `/api/indonesia/provinces`, etc.

## Troubleshooting

### Common Installation Issues

#### SQLite Extension Not Found

**Error**: `could not find driver` or `PDO SQLite driver not found`

**Solution**: Install the PHP SQLite extension:

```bash
# Ubuntu/Debian
sudo apt-get install php8.2-sqlite3

# CentOS/RHEL/Fedora
sudo yum install php-sqlite3
# or
sudo dnf install php-sqlite3

# macOS with Homebrew
brew install php@8.2

# Windows (uncomment in php.ini)
extension=pdo_sqlite
extension=sqlite3
```

After installation, restart your web server and PHP-FPM if applicable.

#### Database File Issues

**Error**: `database disk image is malformed` or `database locked`

**Solutions**:

1. Clear Composer cache and reinstall:
```bash
composer clear-cache
rm -rf vendor/creasi/laravel-nusa
composer install
```

2. Check file permissions:
```bash
# Check if file exists and is readable
ls -la vendor/creasi/laravel-nusa/database/nusa.sqlite

# Fix permissions if needed
chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
```

3. Verify disk space:
```bash
df -h # Check available disk space
```

#### Route Registration Issues

**Error**: `Route [nusa.provinces.index] not defined`

**Solutions**:

1. Clear route cache:
```bash
php artisan route:clear
php artisan config:clear
```

2. Verify routes are enabled:
```bash
php artisan route:list | grep nusa
```

3. Check service provider registration:
```bash
php artisan config:show app.providers | grep Nusa
```

#### Memory or Performance Issues

**Error**: `Maximum execution time exceeded` or `Memory limit exceeded`

**Solutions**:

1. Increase PHP limits in `php.ini`:
```ini
memory_limit = 256M
max_execution_time = 300
```

2. Use pagination for large queries:
```php
// Instead of
$villages = Village::all(); // 83,467 records!

// Use
$villages = Village::paginate(50);
```

### Production Deployment

#### File Permissions

Ensure proper file permissions in production:

```bash
# Make database readable by web server
chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
chown www-data:www-data vendor/creasi/laravel-nusa/database/nusa.sqlite
```

#### Caching

Enable caching for better performance:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Security

Consider these security measures:

1. Disable API routes if not needed:
```dotenv
CREASI_NUSA_ROUTES_ENABLE=false
```

2. Add rate limiting to API routes:
```php
// In RouteServiceProvider or custom middleware
Route::middleware(['throttle:100,1'])->group(function () {
    // Your API routes
});
```

### Getting Help

If you still encounter issues:

1. **Check Laravel logs**: `storage/logs/laravel.log`
2. **Enable debug mode**: Set `APP_DEBUG=true` in `.env` (development only)
3. **GitHub Issues**: [Report bugs](https://github.com/creasico/laravel-nusa/issues)
4. **Community Support**: [GitHub Discussions](https://github.com/orgs/creasico/discussions)

When reporting issues, please include:
- PHP version (`php -v`)
- Laravel version
- Error messages from logs
- Steps to reproduce the issue

## Next Steps

After successful installation:

- **[Getting Started](/guide/getting-started)** - Quick start guide and basic usage
- **[Configuration](/guide/configuration)** - Detailed configuration options
- **[Models & Relationships](/guide/models)** - Understanding the data structure
