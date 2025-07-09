# Province Model

The `Province` model represents Indonesian provinces (provinsi) and provides access to all 34 provincial administrative regions.

## Class Reference

```php
namespace Creasi\Nusa\Models;

class Province extends Model
{
    // Model implementation
}
```

## Attributes

### Primary Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `code` | `string` | 2-digit province code (Primary Key) | `"33"` |
| `name` | `string` | Province name in Indonesian | `"Jawa Tengah"` |
| `latitude` | `float` | Geographic center latitude | `-6.9934809206806` |
| `longitude` | `float` | Geographic center longitude | `110.42024335421` |
| `coordinates` | `array\|null` | Boundary polygon coordinates | `[[-6.123, 110.456], ...]` |

### Computed Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `postal_codes` | `array` | All postal codes within the province |

## Relationships

### One-to-Many Relationships

```php
// Get all regencies in the province
$province->regencies; // Collection<Regency>

// Get all districts in the province  
$province->districts; // Collection<District>

// Get all villages in the province
$province->villages; // Collection<Village>
```

### Relationship Methods

```php
// Regencies relationship
public function regencies(): HasMany
{
    return $this->hasMany(Regency::class, 'province_code', 'code');
}

// Districts relationship (through regencies)
public function districts(): HasMany
{
    return $this->hasMany(District::class, 'province_code', 'code');
}

// Villages relationship (through districts)
public function villages(): HasMany
{
    return $this->hasMany(Village::class, 'province_code', 'code');
}
```

## Scopes

### Search Scope

```php
// Search by name or code (case-insensitive)
Province::search('jawa')->get();
Province::search('33')->first();
Province::search('tengah')->get();
```

**Implementation:**
```php
public function scopeSearch($query, $term)
{
    return $query->where(function ($q) use ($term) {
        $q->where('name', 'like', "%{$term}%")
          ->orWhere('code', 'like', "%{$term}%");
    });
}
```

## Methods

### Static Methods

```php
// Find province by code
Province::find('33'); // Returns Province or null

// Find or fail
Province::findOrFail('33'); // Returns Province or throws exception

// Get all provinces
Province::all(); // Collection<Province>

// Get with pagination
Province::paginate(15); // LengthAwarePaginator
```

### Instance Methods

```php
$province = Province::find('33');

// Get postal codes in this province
$postalCodes = $province->postal_codes; // array

// Count related records
$regencyCount = $province->regencies()->count();
$districtCount = $province->districts()->count(); 
$villageCount = $province->villages()->count();
```

## Usage Examples

### Basic Queries

```php
use Creasi\Nusa\Models\Province;

// Get all provinces
$provinces = Province::all();

// Find specific province
$centralJava = Province::find('33');
$westJava = Province::find('32');

// Search provinces
$javaProvinces = Province::search('jawa')->get();
$province33 = Province::search('33')->first();
```

### With Relationships

```php
// Eager load relationships
$provinces = Province::with(['regencies'])->get();

// Load nested relationships
$province = Province::with(['regencies.districts.villages'])->find('33');

// Load specific columns
$provinces = Province::with(['regencies:code,province_code,name'])->get();
```

### Filtering and Sorting

```php
// Get specific provinces
$selectedProvinces = Province::whereIn('code', ['33', '34', '35'])->get();

// Order by name
$provinces = Province::orderBy('name')->get();

// Get provinces with coordinates
$provincesWithCoords = Province::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();
```

### Aggregations

```php
// Count regencies per province
$provincesWithCounts = Province::withCount('regencies')->get();

foreach ($provincesWithCounts as $province) {
    echo "{$province->name}: {$province->regencies_count} regencies";
}

// Get province with most regencies
$topProvince = Province::withCount('regencies')
    ->orderBy('regencies_count', 'desc')
    ->first();
```

### Geographic Queries

```php
// Get provinces in specific coordinate range
$provinces = Province::whereBetween('latitude', [-8, -5])
    ->whereBetween('longitude', [105, 115])
    ->get();

// Find nearest province to coordinates
function findNearestProvince($lat, $lon) {
    $provinces = Province::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();
        
    $nearest = null;
    $minDistance = PHP_FLOAT_MAX;
    
    foreach ($provinces as $province) {
        $distance = calculateDistance($lat, $lon, $province->latitude, $province->longitude);
        if ($distance < $minDistance) {
            $minDistance = $distance;
            $nearest = $province;
        }
    }
    
    return $nearest;
}
```

## Working with Coordinates

### Boundary Coordinates

```php
$province = Province::find('33');

if ($province->coordinates) {
    // Convert to GeoJSON
    $geoJson = [
        'type' => 'Feature',
        'properties' => [
            'name' => $province->name,
            'code' => $province->code
        ],
        'geometry' => [
            'type' => 'Polygon',
            'coordinates' => [$province->coordinates]
        ]
    ];
    
    // Use with mapping libraries
    return response()->json($geoJson);
}
```

### Center Coordinates

```php
$province = Province::find('33');

// Get center point
$centerLat = $province->latitude;
$centerLon = $province->longitude;

// Use with maps
echo "Province center: {$centerLat}, {$centerLon}";
```

## Postal Code Operations

```php
$province = Province::find('33');

// Get all postal codes in province
$postalCodes = $province->postal_codes;
echo "Postal codes: " . implode(', ', $postalCodes);

// Find provinces by postal code
$villages = Village::where('postal_code', '51111')->get();
$provinces = $villages->pluck('province_code')->unique();
```

## Performance Tips

### Efficient Queries

```php
// Good: Select specific columns
$provinces = Province::select('code', 'name')->get();

// Good: Use pagination for large datasets
$provinces = Province::paginate(15);

// Good: Use whereIn for multiple codes
$provinces = Province::whereIn('code', ['33', '34', '35'])->get();

// Avoid: Loading all data including large coordinates
$provinces = Province::all(); // Loads all coordinates
```

### Caching

```php
use Illuminate\Support\Facades\Cache;

// Cache provinces list
$provinces = Cache::remember('provinces', 3600, function () {
    return Province::orderBy('name')->get(['code', 'name']);
});

// Cache specific province
$province = Cache::remember("province.{$code}", 3600, function () use ($code) {
    return Province::find($code);
});
```

## Validation

### Form Validation

```php
// Validate province code exists
'province_code' => 'required|exists:nusa.provinces,code'

// Custom validation rule
use Illuminate\Contracts\Validation\Rule;

class ValidProvinceCode implements Rule
{
    public function passes($attribute, $value)
    {
        return Province::where('code', $value)->exists();
    }
    
    public function message()
    {
        return 'The selected province is invalid.';
    }
}
```

## Database Schema

```sql
CREATE TABLE provinces (
    code VARCHAR(2) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    latitude DOUBLE NULL,
    longitude DOUBLE NULL,
    coordinates JSON NULL
);

-- Indexes
CREATE INDEX idx_provinces_name ON provinces(name);
CREATE INDEX idx_provinces_coordinates ON provinces(latitude, longitude);
```

## Constants

```php
// Total number of provinces in Indonesia
Province::count(); // 34

// Province codes are always 2 digits
// Examples: '11', '12', '13', ..., '94', '95', '96'
```

## Related Models

- **[Regency Model](/api/models/regency)** - Second-level administrative division
- **[District Model](/api/models/district)** - Third-level administrative division  
- **[Village Model](/api/models/village)** - Fourth-level administrative division
- **[Address Model](/api/models/address)** - Address management with province reference
