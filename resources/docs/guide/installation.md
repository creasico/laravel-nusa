# Installation

This page provides detailed installation instructions for Laravel Nusa in production applications.

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
- **Memory**: No additional memory requirements
- **Database**: Uses separate SQLite connection (no impact on your main database)

## Installation Methods

### Method 1: Composer (Recommended)

Install via Composer in your Laravel project:

```bash
composer require creasi/laravel-nusa
```

### Method 2: Composer with Version Constraint

Install a specific version:

```bash
# Install latest stable
composer require creasi/laravel-nusa:^0.1

# Install specific version
composer require creasi/laravel-nusa:0.1.14
```



## Post-Installation Setup

### Automatic Configuration

Laravel Nusa automatically configures itself when installed:

1. **Service Provider Registration** - Auto-discovered by Laravel
2. **Database Connection** - Adds `nusa` connection to your database config
3. **Route Registration** - Registers API routes (if enabled)

### Verify Installation

Check that everything is working:

```php
<?php

use Creasi\Nusa\Models\Province;

// This should return 34 provinces
$count = Province::count();
echo "Provinces loaded: {$count}";
```

### Check Database Connection

Verify the SQLite database is accessible:

```bash
php artisan tinker
```

```php
// In Tinker
use Creasi\Nusa\Models\Province;
Province::first(); // Should return a Province model
```

## Configuration Options

### Publishing Configuration

Publish the configuration file to customize settings:

```bash
php artisan vendor:publish --tag=creasi-nusa-config
```

This creates `config/creasi/nusa.php`:

```php
<?php

use Creasi\Nusa\Models\Address;

return [
    /**
     * Database connection name for Nusa data
     */
    'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),

    /**
     * Table names (customize if needed)
     */
    'table_names' => [
        'provinces' => 'provinces',
        'regencies' => 'regencies', 
        'districts' => 'districts',
        'villages' => 'villages',
    ],

    /**
     * Address model implementation
     */
    'addressable' => Address::class,

    /**
     * API routes configuration
     */
    'routes_enable' => env('CREASI_NUSA_ROUTES_ENABLE', true),
    'routes_prefix' => env('CREASI_NUSA_ROUTES_PREFIX', 'nusa'),
];
```

### Environment Configuration

Add these variables to your `.env` file:

```dotenv
# Database connection (default: nusa)
CREASI_NUSA_CONNECTION=nusa

# Enable/disable API routes (default: true)
CREASI_NUSA_ROUTES_ENABLE=true

# API route prefix (default: nusa)
CREASI_NUSA_ROUTES_PREFIX=nusa
```

### Custom Database Connection

To use a custom database connection, add it to `config/database.php`:

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

## Address Management Setup

If you plan to use the address management features:

### Publish Migrations

```bash
php artisan vendor:publish --tag=creasi-migrations
```

This publishes the address table migration to `database/migrations/`.

### Run Migrations

```bash
php artisan migrate
```

This creates the `addresses` table in your main database for storing user addresses.

## API Routes Setup

### Default Routes

By default, Laravel Nusa registers these API routes:

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

### Disable API Routes

To disable API routes:

```dotenv
CREASI_NUSA_ROUTES_ENABLE=false
```

### Custom Route Prefix

To change the route prefix:

```dotenv
CREASI_NUSA_ROUTES_PREFIX=api/indonesia
```

Routes will then be available at `/api/indonesia/provinces`, etc.

## Troubleshooting

### Common Issues

#### SQLite Extension Not Found

**Error**: `could not find driver`

**Solution**: Install PHP SQLite extension:

```bash
# Ubuntu/Debian
sudo apt-get install php8.2-sqlite3

# CentOS/RHEL
sudo yum install php-sqlite3

# macOS with Homebrew
brew install php@8.2
```

#### Database File Not Found

**Error**: `database disk image is malformed`

**Solution**: Clear Composer cache and reinstall:

```bash
composer clear-cache
composer install --no-cache
```

#### Permission Denied

**Error**: `SQLSTATE[HY000] [14] unable to open database file`

**Solution**: Check file permissions:

```bash
# Check if file exists and is readable
ls -la vendor/creasi/laravel-nusa/database/nusa.sqlite

# Fix permissions if needed
chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
```

#### Routes Not Working

**Error**: `Route [nusa.provinces.index] not defined`

**Solutions**:
1. Clear route cache: `php artisan route:clear`
2. Check if routes are enabled in config
3. Verify service provider is loaded: `php artisan config:show app.providers`

### Getting Help

If you encounter issues:

1. **Check the logs**: `storage/logs/laravel.log`
2. **GitHub Issues**: [Report bugs](https://github.com/creasico/laravel-nusa/issues)
3. **Discussions**: [Community support](https://github.com/orgs/creasico/discussions)

## Next Steps

After successful installation:

- **[Getting Started](/guide/getting-started)** - Learn basic usage
- **[Models & Relationships](/guide/models)** - Understand the data structure
- **[Configuration](/guide/configuration)** - Customize for your needs
