# Getting Started

This guide will help you quickly install and start using Laravel Nusa in your Laravel application.

## Quick Installation

Install Laravel Nusa via Composer:

```bash
composer require creasi/laravel-nusa
```

That's it! Laravel Nusa is now ready to use. The package includes:

- ✅ Pre-built SQLite database with all Indonesian administrative data
- ✅ Automatic service provider registration
- ✅ Database connection configuration
- ✅ RESTful API routes (optional)

::: tip Requirements
Laravel Nusa requires **PHP ≥ 8.2** with `php-sqlite3` extension and **Laravel ≥ 9.0**. For detailed system requirements and troubleshooting, see the [Installation Guide](/en/guide/installation).
:::

## Verify Installation

Let's verify the installation works correctly:

```php
use Creasi\Nusa\Models\Province;

// Test basic functionality
$provinces = Province::all();
echo "Total provinces: " . $provinces->count(); // Should output: 34

// Test search functionality
$jateng = Province::search('Jawa Tengah')->first();
echo "Found: " . $jateng->name; // Should output: Jawa Tengah
```

If this works, you're ready to go! If you encounter issues, check the [Installation Guide](/en/guide/installation) for troubleshooting.

## First Steps with Laravel Nusa

### 1. Understanding the Data Structure

Laravel Nusa provides a complete hierarchy of Indonesian administrative regions:

```
Indonesia
└── 34 Provinces (Provinsi)
    └── 514 Regencies (Kabupaten/Kota)
        └── 7,266 Districts (Kecamatan)
            └── 83,467 Villages (Kelurahan/Desa)
```

Each level has a specific code format:
- **Province**: `33` (2 digits)
- **Regency**: `33.75` (province.regency)
- **District**: `33.75.01` (province.regency.district)
- **Village**: `33.75.01.1002` (province.regency.district.village)

### 2. Basic Queries

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Find by code
$province = Province::find('33');              // Central Java
$regency = Regency::find('33.75');            // Pekalongan City
$district = District::find('33.75.01');       // West Pekalongan
$village = Village::find('33.75.01.1002');    // Medono Village

// Search by name (case-insensitive)
$provinces = Province::search('jawa')->get();
$regencies = Regency::search('semarang')->get();

// Get with relationships
$province = Province::with('regencies')->find('33');
$regencies = $province->regencies; // All regencies in Central Java
```

### 3. Building Address Forms

A common use case is building cascading dropdown forms:

```php
// Get provinces for the first dropdown
$provinces = Province::orderBy('name')->get(['code', 'name']);

// When user selects a province, get its regencies
$regencies = Regency::where('province_code', '33')
    ->orderBy('name')
    ->get(['code', 'name']);

// When user selects a regency, get its districts
$districts = District::where('regency_code', '33.75')
    ->orderBy('name')
    ->get(['code', 'name']);

// When user selects a district, get its villages
$villages = Village::where('district_code', '33.75.01')
    ->orderBy('name')
    ->get(['code', 'name', 'postal_code']);
```

### 4. Using the API

Laravel Nusa provides RESTful API endpoints out of the box:

```bash
# Get all provinces
curl http://your-app.test/nusa/provinces

# Get regencies in a province
curl http://your-app.test/nusa/provinces/33/regencies

# Search for locations
curl "http://your-app.test/nusa/regencies?search=jakarta"
```

### 5. Working with Geographic Data

Access coordinates and postal codes:

```php
$province = Province::find('33');

// Get center coordinates
echo "Center: {$province->latitude}, {$province->longitude}";

// Get all postal codes in this province
$postalCodes = $province->postal_codes;
echo "Postal codes: " . implode(', ', $postalCodes);

// Get boundary coordinates (if available)
if ($province->coordinates) {
    echo "Has " . count($province->coordinates) . " boundary points";
}
```

## Next Steps

Now that you understand the basics, explore these guides:

- **[Basic Usage Examples](/en/examples/basic-usage)** - More detailed usage patterns and examples
- **[Address Forms](/en/examples/address-forms)** - Complete address form implementation
- **[Models & Relationships](/en/guide/models)** - Deep dive into the Eloquent models
- **[RESTful API](/en/guide/api)** - Using the built-in API endpoints
- **[Configuration](/en/guide/configuration)** - Customizing Laravel Nusa for your needs

## Need Help?

- **Installation Issues**: See the [Installation Guide](/en/guide/installation) for detailed setup and troubleshooting
- **Usage Questions**: Check the [Examples](/en/examples/basic-usage) section for common patterns
- **API Reference**: Browse the [API Documentation](/en/api/overview) for complete endpoint details
