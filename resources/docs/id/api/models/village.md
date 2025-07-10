# Model Kelurahan/Desa

Dokumentasi lengkap untuk model Village Laravel Nusa, termasuk atribut, relasi, scope, dan metode yang tersedia untuk mengelola data kelurahan dan desa di Indonesia.

This comprehensive documentation covers the Village model in Laravel Nusa, including attributes, relationships, scopes, and available methods for managing Indonesian village and urban village data.

## Model Overview

The Village model represents the lowest level of Indonesia's administrative hierarchy. With 83,467 villages and urban villages across Indonesia, this model provides the most granular administrative data for precise location targeting.

### Basic Usage

```php
use Creasi\Nusa\Models\Village;

// Get all villages (use pagination for large datasets)
$villages = Village::paginate(50);

// Find specific village
$village = Village::find('33.74.01.1001');

// Search villages
$villages = Village::search('medono')->get();
```

## Model Attributes

### Database Schema

| Attribute | Type | Description |
|-----------|------|-------------|
| `code` | string(13) | Primary key, thirteen-character village code (xx.xx.xx.xxxx format) |
| `district_code` | string(8) | Foreign key to districts table |
| `regency_code` | string(5) | Foreign key to regencies table |
| `province_code` | string(2) | Foreign key to provinces table |
| `name` | string | Official village/urban village name |
| `postal_code` | string(5) | Five-digit postal code |
| `latitude` | decimal(10,8) | Center point latitude coordinate |
| `longitude` | decimal(11,8) | Center point longitude coordinate |

### Fillable Attributes

```php
protected $fillable = [
    'code',
    'district_code',
    'regency_code',
    'province_code',
    'name',
    'postal_code',
    'latitude',
    'longitude'
];
```

## Relationships

### Upward Relationships

```php
// Parent district
public function district(): BelongsTo

// Parent regency (through district)
public function regency(): BelongsTo

// Parent province (through district.regency)
public function province(): BelongsTo

// Usage
$village = Village::find('33.74.01.1001');
echo $village->district->name;  // "Semarang Tengah"
echo $village->regency->name;   // "Kota Semarang"
echo $village->province->name;  // "Jawa Tengah"
```

### Address Relationships

```php
// Addresses in this village
public function addresses(): HasMany

// Usage
$village = Village::find('33.74.01.1001');
$addresses = $village->addresses; // All addresses in this village
```

## Scopes

### Search Scope

```php
// Search by name, code, or postal code
$villages = Village::search('medono')->get();
$villages = Village::search('50132')->get(); // By postal code
$village = Village::search('33.74.01.1001')->first(); // By code
```

### Filter Scopes

```php
// Filter by district
$villages = Village::where('district_code', '33.74.01')->get();

// Filter by regency
$villages = Village::where('regency_code', '33.74')->get();

// Filter by province
$villages = Village::where('province_code', '33')->get();

// Filter by postal code
$villages = Village::where('postal_code', '50132')->get();
```

## Usage Examples

### Querying with Relationships

```php
// Get villages with complete hierarchy
$villages = Village::with(['district.regency.province'])->get();

// Get villages by regency name
$villages = Village::whereHas('regency', function ($query) {
    $query->where('name', 'like', '%Semarang%');
})->get();

// Get villages with addresses
$villages = Village::with('addresses')->has('addresses')->get();
```

### Postal Code Operations

```php
// Find villages by postal code
$villages = Village::where('postal_code', '50132')->get();

// Get all postal codes in a regency
$postalCodes = Village::where('regency_code', '33.74')
    ->distinct()
    ->pluck('postal_code')
    ->sort()
    ->values();

// Validate postal code for village
public function validatePostalCode($villageCode, $postalCode)
{
    $village = Village::find($villageCode);
    return $village && $village->postal_code === $postalCode;
}
```

### Geographic Operations

```php
// Find villages within radius
$centerLat = -6.2088;
$centerLng = 106.8456;
$radiusKm = 10;

$nearbyVillages = Village::selectRaw("
    *,
    (6371 * acos(
        cos(radians(?)) * 
        cos(radians(latitude)) * 
        cos(radians(longitude) - radians(?)) + 
        sin(radians(?)) * 
        sin(radians(latitude))
    )) AS distance
", [$centerLat, $centerLng, $centerLat])
->having('distance', '<=', $radiusKm)
->orderBy('distance')
->get();
```

## Integration Examples

### User Model Integration

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class User extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
    
    // Get user's full address
    public function getFullLocationAttribute()
    {
        if ($this->village) {
            return "{$this->village->name}, {$this->village->district->name}, {$this->village->regency->name}, {$this->village->province->name}";
        }
        return null;
    }
}

// Usage
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'village_code' => '33.74.01.1001'
]);

echo $user->full_location; // Complete address hierarchy
```

### Address Validation

```php
class AddressValidator
{
    public function validateHierarchy($data)
    {
        $village = Village::find($data['village_code']);
        
        if (!$village) {
            return ['valid' => false, 'message' => 'Village not found'];
        }
        
        $isValid = true;
        $errors = [];
        
        if (isset($data['district_code']) && $village->district_code !== $data['district_code']) {
            $isValid = false;
            $errors[] = 'Village does not belong to selected district';
        }
        
        if (isset($data['regency_code']) && $village->regency_code !== $data['regency_code']) {
            $isValid = false;
            $errors[] = 'Village does not belong to selected regency';
        }
        
        if (isset($data['province_code']) && $village->province_code !== $data['province_code']) {
            $isValid = false;
            $errors[] = 'Village does not belong to selected province';
        }
        
        return [
            'valid' => $isValid,
            'errors' => $errors,
            'village' => $village,
            'suggested_postal_code' => $village->postal_code
        ];
    }
}
```

### Delivery Zone Management

```php
class DeliveryZone extends Model
{
    use WithVillages;
    
    public function addVillagesByPostalCode($postalCode)
    {
        $villages = Village::where('postal_code', $postalCode)->get();
        $this->villages()->attach($villages->pluck('code'));
        
        return $villages->count();
    }
    
    public function getCoverageStats()
    {
        $villages = $this->villages()->with(['district.regency.province'])->get();
        
        return [
            'total_villages' => $villages->count(),
            'provinces' => $villages->pluck('province.name')->unique()->count(),
            'regencies' => $villages->pluck('regency.name')->unique()->count(),
            'districts' => $villages->pluck('district.name')->unique()->count(),
            'postal_codes' => $villages->pluck('postal_code')->unique()->sort()->values()
        ];
    }
}
```

## Performance Considerations

### Efficient Queries

```php
// Use pagination for large datasets
$villages = Village::paginate(50);

// Select only needed fields
$villages = Village::select('code', 'name', 'postal_code')->get();

// Use chunk for bulk processing
Village::chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Process village
    }
});

// Use cursor for memory-efficient iteration
foreach (Village::cursor() as $village) {
    // Process village with minimal memory usage
}
```

### Caching Strategies

```php
// Cache villages by postal code
$villages = Cache::remember("villages_postal_50132", 3600, function () {
    return Village::where('postal_code', '50132')->get();
});

// Cache village with hierarchy
$village = Cache::remember("village_33.74.01.1001", 3600, function () {
    return Village::with(['district.regency.province'])->find('33.74.01.1001');
});
```

## Next Steps

- **[Address Model](/id/api/models/address)** - Address management documentation
- **[District Model](/id/api/models/district)** - Model kecamatan documentation
- **[Village API](/id/api/villages)** - Village API endpoints
- **[Address Forms](/id/examples/address-forms)** - Building address forms with villages
