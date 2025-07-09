# Getting Started

This guide will help you install and start using Laravel Nusa in your Laravel application.

## Prerequisites

Before installing Laravel Nusa, ensure your system meets these requirements:

- **PHP** ≥ 8.2 with `php-sqlite3` extension
- **Laravel** ≥ 9.0 (supports up to Laravel 12.0)
- **Composer** for package management

## Installation

Install Laravel Nusa via Composer:

```bash
composer require creasi/laravel-nusa
```

That's it! Laravel Nusa is now ready to use. The package includes:

- ✅ Pre-built SQLite database with all Indonesian administrative data
- ✅ Automatic service provider registration
- ✅ Database connection configuration
- ✅ RESTful API routes (optional)

## Verify Installation

Let's verify the installation by testing some basic functionality:

### 1. Test Models

Create a simple test in your application:

```php
<?php

use Creasi\Nusa\Models\Province;

// Get all provinces
$provinces = Province::all();
echo "Total provinces: " . $provinces->count(); // Should output: 34

// Get a specific province
$jateng = Province::search('Jawa Tengah')->first();
echo "Province: " . $jateng->name; // Should output: Jawa Tengah
echo "Code: " . $jateng->code; // Should output: 33
```

### 2. Test API Endpoints

If you have API routes enabled (default), test the endpoints:

```bash
# Get all provinces
curl http://your-app.test/nusa/provinces

# Get specific province
curl http://your-app.test/nusa/provinces/33

# Search provinces
curl "http://your-app.test/nusa/provinces?search=jawa"
```

### 3. Test Relationships

Verify that relationships work correctly:

```php
<?php

use Creasi\Nusa\Models\Province;

$jateng = Province::find('33');

// Get regencies in Central Java
$regencies = $jateng->regencies;
echo "Regencies in Central Java: " . $regencies->count();

// Get all districts in Central Java
$districts = $jateng->districts;
echo "Districts in Central Java: " . $districts->count();

// Get all villages in Central Java
$villages = $jateng->villages;
echo "Villages in Central Java: " . $villages->count();
```

## Basic Usage Examples

### Finding Administrative Regions

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Find by code
$province = Province::find('33');
$regency = Regency::find('3375');
$district = District::find('337501');
$village = Village::find('3375011002');

// Search by name
$provinces = Province::search('jawa')->get();
$regencies = Regency::search('semarang')->get();
$districts = District::search('pekalongan')->get();
$villages = Village::search('medono')->get();

// Get with relationships
$province = Province::with(['regencies', 'districts'])->find('33');
```

### Working with Geographic Data

```php
use Creasi\Nusa\Models\Province;

$province = Province::find('33');

// Access coordinates
echo "Latitude: " . $province->latitude;
echo "Longitude: " . $province->longitude;

// Access boundary coordinates (if available)
$coordinates = $province->coordinates; // Array of coordinate points

// Get postal codes in this province
$postalCodes = $province->postal_codes; // Array of postal codes
```

### Building Address Forms

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Get data for cascading dropdowns
$provinces = Province::orderBy('name')->get(['code', 'name']);

// When user selects a province
$regencies = Regency::where('province_code', '33')
    ->orderBy('name')
    ->get(['code', 'name']);

// When user selects a regency
$districts = District::where('regency_code', '3375')
    ->orderBy('name')
    ->get(['code', 'name']);

// When user selects a district
$villages = Village::where('district_code', '337501')
    ->orderBy('name')
    ->get(['code', 'name', 'postal_code']);
```

## Configuration

Laravel Nusa works out of the box with sensible defaults, but you can customize it if needed.

### Publish Configuration

```bash
php artisan vendor:publish --tag=creasi-nusa-config
```

This creates `config/creasi/nusa.php` with these options:

```php
<?php

return [
    // Database connection name
    'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),
    
    // Table names (if you need to customize)
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

### Environment Variables

Add these to your `.env` file if you need to customize:

```dotenv
# Disable API routes
CREASI_NUSA_ROUTES_ENABLE=false

# Change API prefix
CREASI_NUSA_ROUTES_PREFIX=api/indonesia

# Use custom database connection
CREASI_NUSA_CONNECTION=indonesia
```

## Next Steps

Now that you have Laravel Nusa installed and working, explore these topics:

- **[Models & Relationships](/guide/models)** - Learn about the Eloquent models and their relationships
- **[RESTful API](/guide/api)** - Discover the built-in API endpoints
- **[Address Management](/guide/addresses)** - Integrate address functionality into your models
- **[Configuration](/guide/configuration)** - Customize Laravel Nusa for your needs

## Troubleshooting

### SQLite Extension Missing

If you get an error about SQLite, install the PHP SQLite extension:

```bash
# Ubuntu/Debian
sudo apt-get install php-sqlite3

# CentOS/RHEL
sudo yum install php-sqlite3

# macOS with Homebrew
brew install php@8.2 --with-sqlite3
```

### Permission Issues

If you encounter permission issues with the SQLite database:

```bash
# Make sure the database file is readable
chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
```

### API Routes Not Working

If API routes aren't accessible:

1. Check that routes are enabled in config
2. Clear route cache: `php artisan route:clear`
3. Verify the route prefix in your configuration
