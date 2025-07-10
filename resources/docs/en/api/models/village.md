# Village Model

The `Village` model represents Indonesian villages and urban villages (kelurahan/desa) and provides access to all 83,467 fourth-level administrative regions.

## Class Reference

```php
namespace Creasi\Nusa\Models;

class Village extends Model
{
    // Model implementation
}
```

## Attributes

### Primary Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `code` | `string` | Village code in xx.xx.xx.xxxx format (Primary Key) | `"33.75.01.1002"` |
| `district_code` | `string` | Parent district code (Foreign Key) | `"33.75.01"` |
| `regency_code` | `string` | Parent regency code (Foreign Key) | `"33.75"` |
| `province_code` | `string` | Parent province code (Foreign Key) | `"33"` |
| `name` | `string` | Village name in Indonesian | `"Medono"` |
| `latitude` | `float\|null` | Geographic center latitude | `-6.8969497174987` |
| `longitude` | `float\|null` | Geographic center longitude | `109.66208089654` |
| `postal_code` | `string\|null` | 5-digit postal code | `"51111"` |

## Relationships

### Belongs To

```php
// Get parent district
$village->district; // District model

// Get parent regency
$village->regency; // Regency model

// Get parent province
$village->province; // Province model
```

### Relationship Methods

```php
// District relationship
public function district(): BelongsTo
{
    return $this->belongsTo(District::class, 'district_code', 'code');
}

// Regency relationship
public function regency(): BelongsTo
{
    return $this->belongsTo(Regency::class, 'regency_code', 'code');
}

// Province relationship
public function province(): BelongsTo
{
    return $this->belongsTo(Province::class, 'province_code', 'code');
}
```

## Scopes

### Search Scope

```php
// Search by name or code (case-insensitive)
Village::search('medono')->get();
Village::search('33.75.01.1002')->first();
Village::search('desa')->get();
```

## Usage Examples

### Basic Queries

```php
use Creasi\Nusa\Models\Village;

// Get villages with pagination (recommended for performance)
$villages = Village::paginate(50);

// Find specific village
$village = Village::find('33.75.01.1002');

// Search villages
$medonos = Village::search('medono')->get();
$villages = Village::search('kelurahan')->get();
```

### Hierarchical Queries

```php
// Get villages in a specific district
$districtVillages = Village::where('district_code', '33.75.01')->get();

// Get villages in a specific regency
$regencyVillages = Village::where('regency_code', '33.75')->get();

// Get villages in a specific province
$provinceVillages = Village::where('province_code', '33')->paginate(100);

// Get villages with their complete hierarchy
$villages = Village::with(['district', 'regency', 'province'])->get();
```

### Postal Code Queries

```php
// Find villages by postal code
$villages = Village::where('postal_code', '51111')->get();

// Find villages with postal codes starting with 511
$villages = Village::where('postal_code', 'like', '511%')->get();

// Get villages without postal codes
$villagesWithoutPostal = Village::whereNull('postal_code')->get();

// Group villages by postal code
$villagesByPostal = Village::whereNotNull('postal_code')
    ->get()
    ->groupBy('postal_code');
```

### With Relationships

```php
// Load village with complete hierarchy
$village = Village::with(['district', 'regency', 'province'])->find('33.75.01.1002');

// Load specific columns from relationships
$villages = Village::with([
    'district:code,name',
    'regency:code,name',
    'province:code,name'
])->get();

// Get full address hierarchy
$village = Village::with(['district', 'regency', 'province'])->find('33.75.01.1002');
$fullAddress = implode(', ', [
    $village->name,
    $village->district->name,
    $village->regency->name,
    $village->province->name,
    $village->postal_code
]);
```

### Geographic Operations

```php
// Find villages in coordinate range
$villages = Village::whereBetween('latitude', [-7, -6])
    ->whereBetween('longitude', [109, 111])
    ->get();

// Get villages with coordinates
$villagesWithCoords = Village::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();

// Find nearest village to coordinates
function findNearestVillage($lat, $lon) {
    $villages = Village::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();
        
    $nearest = null;
    $minDistance = PHP_FLOAT_MAX;
    
    foreach ($villages as $village) {
        $distance = calculateDistance($lat, $lon, $village->latitude, $village->longitude);
        if ($distance < $minDistance) {
            $minDistance = $distance;
            $nearest = $village;
        }
    }
    
    return $nearest;
}
```

## Code Structure

### Village Codes

Village codes follow the pattern: `XX.YY.ZZ.VVVV`
- `XX` = Province code (2 digits)
- `YY` = Regency code within province (2 digits)
- `ZZ` = District code within regency (2 digits)
- `VVVV` = Village code within district (4 digits)

```php
$village = Village::find('33.75.01.1002');
echo $village->province_code; // "33" (Central Java)
echo $village->regency_code;  // "33.75" (Pekalongan City)
echo $village->district_code; // "33.75.01" (West Pekalongan)
echo explode('.', $village->code)[3]; // "1002" (Village within district)
```

### Full Address Building

```php
function buildFullAddress($villageCode) {
    $village = Village::with(['district', 'regency', 'province'])
        ->find($villageCode);
        
    if (!$village) {
        return null;
    }
    
    return [
        'village' => $village->name,
        'district' => $village->district->name,
        'regency' => $village->regency->name,
        'province' => $village->province->name,
        'postal_code' => $village->postal_code,
        'full_address' => implode(', ', array_filter([
            $village->name,
            $village->district->name,
            $village->regency->name,
            $village->province->name,
            $village->postal_code
        ]))
    ];
}
```

## Postal Code Operations

```php
// Get all postal codes in a province
$postalCodes = Village::where('province_code', '33')
    ->whereNotNull('postal_code')
    ->distinct()
    ->pluck('postal_code')
    ->sort()
    ->values();

// Find villages sharing a postal code
$sharedPostalVillages = Village::where('postal_code', '51111')->get();

// Validate postal code for village
function validatePostalCode($villageCode, $postalCode) {
    $village = Village::find($villageCode);
    return $village && $village->postal_code === $postalCode;
}

// Get postal code statistics
$postalStats = Village::selectRaw('postal_code, count(*) as village_count')
    ->whereNotNull('postal_code')
    ->groupBy('postal_code')
    ->orderBy('village_count', 'desc')
    ->get();
```

## Performance Tips

### Efficient Queries

```php
// Good: Always use pagination for villages
$villages = Village::paginate(50);

// Good: Filter by parent region first
$villages = Village::where('district_code', '33.75.01')
    ->select('code', 'name', 'postal_code')
    ->get();

// Good: Use specific columns
$villages = Village::select('code', 'name', 'postal_code')->get();

// Avoid: Loading all villages at once
$villages = Village::all(); // 83,467 records - will cause memory issues!
```

### Chunking for Large Operations

```php
// Process villages in chunks
Village::chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Process each village
        processVillage($village);
    }
});

// Chunk with filtering
Village::where('province_code', '33')
    ->chunk(1000, function ($villages) {
        // Process Central Java villages
    });
```

### Caching Strategies

```php
use Illuminate\Support\Facades\Cache;

// Cache villages by district
function getVillagesByDistrict($districtCode) {
    $cacheKey = "villages.district.{$districtCode}";
    
    return Cache::remember($cacheKey, 3600, function () use ($districtCode) {
        return Village::where('district_code', $districtCode)
            ->orderBy('name')
            ->get(['code', 'name', 'postal_code']);
    });
}

// Cache village with full hierarchy
function getVillageWithHierarchy($code) {
    $cacheKey = "village.hierarchy.{$code}";
    
    return Cache::remember($cacheKey, 3600, function () use ($code) {
        return Village::with(['district', 'regency', 'province'])
            ->find($code);
    });
}
```

## Validation

### Form Validation

```php
// Validate village exists and belongs to district
'village_code' => [
    'required',
    'exists:nusa.villages,code',
    function ($attribute, $value, $fail) {
        $village = Village::find($value);
        if (!$village || $village->district_code !== request('district_code')) {
            $fail('The selected village is invalid for this district.');
        }
    }
]

// Validate complete address hierarchy
'address' => [
    'required',
    'array',
    function ($attribute, $value, $fail) {
        $village = Village::find($value['village_code']);
        if (!$village ||
            $village->district_code !== $value['district_code'] ||
            $village->regency_code !== $value['regency_code'] ||
            $village->province_code !== $value['province_code']) {
            $fail('The address components are not consistent.');
        }
    }
]
```

### Custom Validation Rules

```php
use Illuminate\Contracts\Validation\Rule;

class ValidVillageForDistrict implements Rule
{
    private $districtCode;
    
    public function __construct($districtCode)
    {
        $this->districtCode = $districtCode;
    }
    
    public function passes($attribute, $value)
    {
        $village = Village::find($value);
        return $village && $village->district_code === $this->districtCode;
    }
    
    public function message()
    {
        return 'The selected village does not belong to the specified district.';
    }
}

// Usage
'village_code' => ['required', new ValidVillageForDistrict($districtCode)]
```

## Database Schema

```sql
CREATE TABLE villages (
    code VARCHAR(13) PRIMARY KEY,
    district_code VARCHAR(8) NOT NULL,
    regency_code VARCHAR(5) NOT NULL,
    province_code VARCHAR(2) NOT NULL,
    name VARCHAR(255) NOT NULL,
    latitude DOUBLE NULL,
    longitude DOUBLE NULL,
    postal_code VARCHAR(5) NULL,
    FOREIGN KEY (district_code) REFERENCES districts(code),
    FOREIGN KEY (regency_code) REFERENCES regencies(code),
    FOREIGN KEY (province_code) REFERENCES provinces(code)
);

-- Indexes
CREATE INDEX idx_villages_district ON villages(district_code);
CREATE INDEX idx_villages_regency ON villages(regency_code);
CREATE INDEX idx_villages_province ON villages(province_code);
CREATE INDEX idx_villages_postal ON villages(postal_code);
CREATE INDEX idx_villages_name ON villages(name);
CREATE INDEX idx_villages_coordinates ON villages(latitude, longitude);
```

## Constants

```php
// Total number of villages in Indonesia
Village::count(); // 83,467

// Average villages per district
$avgVillagesPerDistrict = Village::count() / District::count(); // ~11.5

// Villages with postal codes
$villagesWithPostal = Village::whereNotNull('postal_code')->count();
```

## Related Models

- **[Province Model](/en/api/models/province)** - Great-grandparent administrative division
- **[Regency Model](/en/api/models/regency)** - Grandparent administrative division
- **[District Model](/en/api/models/district)** - Parent administrative division
- **[Address Model](/en/api/models/address)** - Address management with village reference
