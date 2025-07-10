# Model Kecamatan

Dokumentasi lengkap untuk model District Laravel Nusa, termasuk atribut, relasi, scope, dan metode yang tersedia untuk mengelola data kecamatan di Indonesia.

This comprehensive documentation covers the District model in Laravel Nusa, including attributes, relationships, scopes, and available methods for managing Indonesian district data.

## Model Overview

The District model represents the third level of Indonesia's administrative hierarchy. With 7,266 districts across Indonesia, this model provides detailed sub-regional administrative data for community-level operations.

### Basic Usage

```php
use Creasi\Nusa\Models\District;

// Get all districts
$districts = District::all();

// Find specific district
$district = District::find('33.74.01'); // Semarang Tengah

// Search districts
$centralDistricts = District::search('tengah')->get();
```

## Model Attributes

### Database Schema

| Attribute | Type | Description |
|-----------|------|-------------|
| `code` | string(8) | Primary key, eight-character district code (xx.xx.xx format) |
| `regency_code` | string(5) | Foreign key to regencies table |
| `province_code` | string(2) | Foreign key to provinces table |
| `name` | string | Official district name |
| `latitude` | decimal(10,8) | Center point latitude coordinate |
| `longitude` | decimal(11,8) | Center point longitude coordinate |

### Fillable Attributes

```php
protected $fillable = [
    'code',
    'regency_code',
    'province_code',
    'name', 
    'latitude',
    'longitude'
];
```

## Relationships

### Upward Relationships

```php
// Parent regency
public function regency(): BelongsTo

// Parent province (through regency)
public function province(): BelongsTo

// Usage
$district = District::find('33.74.01');
echo $district->regency->name;  // "Kota Semarang"
echo $district->province->name; // "Jawa Tengah"
```

### Downward Relationships

```php
// Villages in this district
public function villages(): HasMany

// Usage
$district = District::find('33.74.01');
$villages = $district->villages; // All villages in district
```

## Scopes

### Search Scope

```php
// Search by name or code
$districts = District::search('tengah')->get();
$district = District::search('33.74.01')->first();
```

### Filter Scopes

```php
// Filter by regency
$districts = District::where('regency_code', '33.74')->get();

// Filter by province
$districts = District::where('province_code', '33')->get();
```

## Usage Examples

### Querying with Relationships

```php
// Get districts with their regencies and provinces
$districts = District::with(['regency.province'])->get();

// Get districts by regency name
$districts = District::whereHas('regency', function ($query) {
    $query->where('name', 'like', '%Semarang%');
})->get();

// Get districts with minimum village count
$largeDistricts = District::has('villages', '>=', 20)->get();
```

### Service Area Analysis

```php
class ServiceAreaAnalyzer
{
    public function getDistrictCoverage($servicePoints)
    {
        return District::withCount('villages')
            ->get()
            ->map(function ($district) use ($servicePoints) {
                $coverage = $this->calculateCoverage($district, $servicePoints);
                
                return [
                    'district' => $district->name,
                    'regency' => $district->regency->name,
                    'total_villages' => $district->villages_count,
                    'covered_villages' => $coverage['covered'],
                    'coverage_percentage' => $coverage['percentage']
                ];
            });
    }
}
```

## Integration Examples

### Service Center Model

```php
use Creasi\Nusa\Models\Concerns\WithDistrict;

class ServiceCenter extends Model
{
    use WithDistrict;
    
    protected $fillable = ['name', 'district_code', 'address'];
}

// Usage
$center = ServiceCenter::create([
    'name' => 'Service Center A',
    'district_code' => '33.74.01',
    'address' => 'Jl. Pemuda No. 456'
]);

echo $center->district->name; // "Semarang Tengah"
echo $center->district->regency->name; // "Kota Semarang"
```

## Next Steps

- **[Village Model](/id/api/models/village)** - Model kelurahan/desa documentation
- **[Regency Model](/id/api/models/regency)** - Model kabupaten/kota documentation
- **[District API](/id/api/districts)** - District API endpoints
- **[Address Model](/id/api/models/address)** - Address management documentation
