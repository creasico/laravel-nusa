# Regency Model

The `Regency` model represents Indonesian regencies and cities (kabupaten/kota) and provides access to all 514 second-level administrative regions.

## Class Reference

```php
namespace Creasi\Nusa\Models;

class Regency extends Model
{
    // Model implementation
}
```

## Attributes

### Primary Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `code` | `string` | Regency code in xx.xx format (Primary Key) | `"33.75"` |
| `province_code` | `string` | Parent province code (Foreign Key) | `"33"` |
| `name` | `string` | Regency/City name in Indonesian | `"Kota Pekalongan"` |
| `latitude` | `float` | Geographic center latitude | `-6.8969497174987` |
| `longitude` | `float` | Geographic center longitude | `109.66208089654` |
| `coordinates` | `array\|null` | Boundary polygon coordinates | `[[-6.789, 109.567], ...]` |

### Computed Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `postal_codes` | `array` | All postal codes within the regency |

## Relationships

### Belongs To

```php
// Get parent province
$regency->province; // Province model
```

### One-to-Many

```php
// Get all districts in the regency
$regency->districts; // Collection<District>

// Get all villages in the regency
$regency->villages; // Collection<Village>
```

### Relationship Methods

```php
// Province relationship
public function province(): BelongsTo
{
    return $this->belongsTo(Province::class, 'province_code', 'code');
}

// Districts relationship
public function districts(): HasMany
{
    return $this->hasMany(District::class, 'regency_code', 'code');
}

// Villages relationship (through districts)
public function villages(): HasMany
{
    return $this->hasMany(Village::class, 'regency_code', 'code');
}
```

## Scopes

### Search Scope

```php
// Search by name or code (case-insensitive)
Regency::search('semarang')->get();
Regency::search('33.75')->first();
Regency::search('kota')->get();
```

## Usage Examples

### Basic Queries

```php
use Creasi\Nusa\Models\Regency;

// Get all regencies
$regencies = Regency::all();

// Find specific regency
$pekalongan = Regency::find('33.75');

// Search regencies
$semarangRegencies = Regency::search('semarang')->get();
$cities = Regency::search('kota')->get();
```

### Province-based Queries

```php
// Get regencies in a specific province
$centralJavaRegencies = Regency::where('province_code', '33')->get();

// Get regencies with their province
$regencies = Regency::with('province')->get();

// Get regencies in multiple provinces
$javaRegencies = Regency::whereIn('province_code', ['32', '33', '34'])->get();
```

### With Relationships

```php
// Load regency with all relationships
$regency = Regency::with(['province', 'districts', 'villages'])->find('33.75');

// Load specific columns from relationships
$regencies = Regency::with([
    'province:code,name',
    'districts:code,regency_code,name'
])->get();

// Count related records
$regencies = Regency::withCount(['districts', 'villages'])->get();
```

### Filtering and Sorting

```php
// Get cities only (containing "Kota")
$cities = Regency::where('name', 'like', '%Kota%')->get();

// Get regencies only (containing "Kabupaten")
$kabupaten = Regency::where('name', 'like', '%Kabupaten%')->get();

// Order by name
$regencies = Regency::orderBy('name')->get();

// Get regencies with coordinates
$regenciesWithCoords = Regency::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();
```

### Geographic Operations

```php
// Find regencies in coordinate range
$regencies = Regency::whereBetween('latitude', [-7, -6])
    ->whereBetween('longitude', [109, 111])
    ->get();

// Get regency boundaries for mapping
$regency = Regency::find('33.75');
if ($regency->coordinates) {
    $geoJson = [
        'type' => 'Feature',
        'properties' => [
            'name' => $regency->name,
            'code' => $regency->code,
            'province' => $regency->province->name
        ],
        'geometry' => [
            'type' => 'Polygon',
            'coordinates' => [$regency->coordinates]
        ]
    ];
}
```

## Code Structure

### Regency Codes

Regency codes follow the pattern: `XX.YY`
- `XX` = Province code (2 digits)
- `YY` = Regency code within province (2 digits)

```php
$regency = Regency::find('33.75');
echo $regency->province_code; // "33" (Central Java)
echo explode('.', $regency->code)[1]; // "75" (Pekalongan City within Central Java)
```

### Types of Regencies

```php
// Distinguish between cities and regencies
function getRegencyType($regency) {
    if (str_contains($regency->name, 'Kota')) {
        return 'City';
    } elseif (str_contains($regency->name, 'Kabupaten')) {
        return 'Regency';
    }
    return 'Unknown';
}

// Get all cities
$cities = Regency::where('name', 'like', '%Kota%')->get();

// Get all regencies (kabupaten)
$kabupaten = Regency::where('name', 'like', '%Kabupaten%')->get();
```

## Postal Code Operations

```php
$regency = Regency::find('33.75');

// Get all postal codes in regency
$postalCodes = $regency->postal_codes;
echo "Postal codes in {$regency->name}: " . implode(', ', $postalCodes);

// Find regencies by postal code range
$regencies = Regency::whereHas('villages', function ($query) {
    $query->where('postal_code', 'like', '511%');
})->get();
```

## Aggregations and Statistics

```php
// Count districts per regency
$regenciesWithCounts = Regency::withCount('districts')->get();

// Get regency with most districts
$topRegency = Regency::withCount('districts')
    ->orderBy('districts_count', 'desc')
    ->first();

// Group by province
$regenciesByProvince = Regency::with('province')
    ->get()
    ->groupBy('province.name');

// Statistics by type
$cityCount = Regency::where('name', 'like', '%Kota%')->count();
$regencyCount = Regency::where('name', 'like', '%Kabupaten%')->count();
```

## Performance Tips

### Efficient Queries

```php
// Good: Select specific columns
$regencies = Regency::select('code', 'name', 'province_code')->get();

// Good: Use pagination
$regencies = Regency::paginate(25);

// Good: Filter by province first
$regencies = Regency::where('province_code', '33')
    ->with('districts')
    ->get();

// Avoid: Loading all regencies with all relationships
$regencies = Regency::with(['province', 'districts.villages'])->get();
```

### Caching Strategies

```php
use Illuminate\Support\Facades\Cache;

// Cache regencies by province
function getRegenciesByProvince($provinceCode) {
    $cacheKey = "regencies.province.{$provinceCode}";
    
    return Cache::remember($cacheKey, 3600, function () use ($provinceCode) {
        return Regency::where('province_code', $provinceCode)
            ->orderBy('name')
            ->get(['code', 'name']);
    });
}

// Cache regency with relationships
function getRegencyWithDetails($code) {
    $cacheKey = "regency.details.{$code}";
    
    return Cache::remember($cacheKey, 3600, function () use ($code) {
        return Regency::with(['province', 'districts'])
            ->find($code);
    });
}
```

## Validation

### Form Validation

```php
// Validate regency exists and belongs to province
'regency_code' => [
    'required',
    'exists:nusa.regencies,code',
    function ($attribute, $value, $fail) {
        $regency = Regency::find($value);
        if (!$regency || $regency->province_code !== request('province_code')) {
            $fail('The selected regency is invalid for this province.');
        }
    }
]
```

### Custom Validation Rules

```php
use Illuminate\Contracts\Validation\Rule;

class ValidRegencyForProvince implements Rule
{
    private $provinceCode;
    
    public function __construct($provinceCode)
    {
        $this->provinceCode = $provinceCode;
    }
    
    public function passes($attribute, $value)
    {
        $regency = Regency::find($value);
        return $regency && $regency->province_code === $this->provinceCode;
    }
    
    public function message()
    {
        return 'The selected regency does not belong to the specified province.';
    }
}

// Usage
'regency_code' => ['required', new ValidRegencyForProvince($provinceCode)]
```

## Database Schema

```sql
CREATE TABLE regencies (
    code VARCHAR(4) PRIMARY KEY,
    province_code VARCHAR(2) NOT NULL,
    name VARCHAR(255) NOT NULL,
    latitude DOUBLE NULL,
    longitude DOUBLE NULL,
    coordinates JSON NULL,
    FOREIGN KEY (province_code) REFERENCES provinces(code)
);

-- Indexes
CREATE INDEX idx_regencies_province ON regencies(province_code);
CREATE INDEX idx_regencies_name ON regencies(name);
CREATE INDEX idx_regencies_coordinates ON regencies(latitude, longitude);
```

## Constants

```php
// Total number of regencies in Indonesia
Regency::count(); // 514

// Breakdown by type
$cities = Regency::where('name', 'like', '%Kota%')->count(); // ~98 cities
$kabupaten = Regency::where('name', 'like', '%Kabupaten%')->count(); // ~416 regencies
```

## Related Models

- **[Province Model](/api/models/province)** - Parent administrative division
- **[District Model](/api/models/district)** - Child administrative division
- **[Village Model](/api/models/village)** - Grandchild administrative division
- **[Address Model](/api/models/address)** - Address management with regency reference
