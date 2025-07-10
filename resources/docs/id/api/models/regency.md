# Model Kabupaten/Kota

Dokumentasi lengkap untuk model Regency Laravel Nusa, termasuk atribut, relasi, scope, dan metode yang tersedia untuk mengelola data kabupaten dan kota di Indonesia.

This comprehensive documentation covers the Regency model in Laravel Nusa, including attributes, relationships, scopes, and available methods for managing Indonesian regency and city data.

## Model Overview

The Regency model represents the second level of Indonesia's administrative hierarchy, encompassing both regencies (kabupaten) and cities (kota). With 514 regencies and cities across Indonesia, this model provides crucial regional administrative data.

### Basic Usage

```php
use Creasi\Nusa\Models\Regency;

// Get all regencies
$regencies = Regency::all();

// Find specific regency
$semarang = Regency::find('33.74'); // Kota Semarang

// Search regencies
$cities = Regency::search('kota')->get();
```

## Model Attributes

### Database Schema

| Attribute | Type | Description |
|-----------|------|-------------|
| `code` | string(5) | Primary key, five-character regency code (xx.xx format) |
| `province_code` | string(2) | Foreign key to provinces table |
| `name` | string | Official regency/city name |
| `latitude` | decimal(10,8) | Center point latitude coordinate |
| `longitude` | decimal(11,8) | Center point longitude coordinate |

### Fillable Attributes

```php
protected $fillable = [
    'code',
    'province_code',
    'name', 
    'latitude',
    'longitude'
];
```

## Relationships

### Upward Relationships

```php
// Parent province
public function province(): BelongsTo

// Usage
$regency = Regency::find('33.74');
echo $regency->province->name; // "Jawa Tengah"
```

### Downward Relationships

```php
// Direct relationships
public function districts(): HasMany
public function villages(): HasManyThrough

// Usage examples
$regency = Regency::find('33.74');
$districts = $regency->districts; // All districts in regency
$villages = $regency->villages;   // All villages in regency
```

## Scopes

### Search Scope

```php
// Search by name or code
$regencies = Regency::search('semarang')->get();
$regency = Regency::search('33.74')->first();
```

### Filter Scopes

```php
// Filter by province
$regencies = Regency::where('province_code', '33')->get();

// Cities only (containing "Kota")
$cities = Regency::where('name', 'like', '%Kota%')->get();

// Regencies only (containing "Kabupaten")
$kabupaten = Regency::where('name', 'like', '%Kabupaten%')->get();
```

## Usage Examples

### Querying with Relationships

```php
// Get regencies with their provinces
$regencies = Regency::with('province')->get();

// Get regencies by province name
$regencies = Regency::whereHas('province', function ($query) {
    $query->where('name', 'like', '%Jawa%');
})->get();

// Get regencies with minimum district count
$largeRegencies = Regency::has('districts', '>=', 15)->get();
```

### Geographic Operations

```php
// Find regencies near coordinates
$centerLat = -6.2088;
$centerLng = 106.8456;

$nearbyRegencies = Regency::selectRaw("
    *,
    (6371 * acos(
        cos(radians(?)) * 
        cos(radians(latitude)) * 
        cos(radians(longitude) - radians(?)) + 
        sin(radians(?)) * 
        sin(radians(latitude))
    )) AS distance
", [$centerLat, $centerLng, $centerLat])
->having('distance', '<=', 50)
->orderBy('distance')
->get();
```

## Integration Examples

### Business Location Model

```php
use Creasi\Nusa\Models\Concerns\WithRegency;

class BusinessLocation extends Model
{
    use WithRegency;
    
    protected $fillable = ['name', 'regency_code', 'address'];
}

// Usage
$location = BusinessLocation::create([
    'name' => 'Main Office',
    'regency_code' => '33.74',
    'address' => 'Jl. Sudirman No. 123'
]);

echo $location->regency->name; // "Kota Semarang"
echo $location->regency->province->name; // "Jawa Tengah"
```

## Next Steps

- **[District Model](/id/api/models/district)** - Model kecamatan documentation
- **[Village Model](/id/api/models/village)** - Model kelurahan/desa documentation
- **[Province Model](/id/api/models/province)** - Model provinsi documentation
- **[Regency API](/id/api/regencies)** - Regency API endpoints
