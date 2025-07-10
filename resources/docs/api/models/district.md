# District Model

The `District` model represents Indonesian districts (kecamatan) and provides access to all 7,266 third-level administrative regions.

## Class Reference

```php
namespace Creasi\Nusa\Models;

class District extends Model
{
    // Model implementation
}
```

## Attributes

### Primary Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `code` | `string` | District code in xx.xx.xx format (Primary Key) | `"33.75.01"` |
| `regency_code` | `string` | Parent regency code (Foreign Key) | `"33.75"` |
| `province_code` | `string` | Parent province code (Foreign Key) | `"33"` |
| `name` | `string` | District name in Indonesian | `"Pekalongan Barat"` |
| `latitude` | `float\|null` | Geographic center latitude | `-6.8969497174987` |
| `longitude` | `float\|null` | Geographic center longitude | `109.66208089654` |

### Computed Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `postal_codes` | `array` | All postal codes within the district |

## Relationships

### Belongs To

```php
// Get parent regency
$district->regency; // Regency model

// Get parent province
$district->province; // Province model
```

### One-to-Many

```php
// Get all villages in the district
$district->villages; // Collection<Village>
```

### Relationship Methods

```php
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

// Villages relationship
public function villages(): HasMany
{
    return $this->hasMany(Village::class, 'district_code', 'code');
}
```

## Scopes

### Search Scope

```php
// Search by name or code (case-insensitive)
District::search('pekalongan')->get();
District::search('33.75.01')->first();
District::search('barat')->get();
```

## Usage Examples

### Basic Queries

```php
use Creasi\Nusa\Models\District;

// Get all districts (use pagination for performance)
$districts = District::paginate(50);

// Find specific district
$district = District::find('33.75.01');

// Search districts
$pekalongans = District::search('pekalongan')->get();
$westDistricts = District::search('barat')->get();
```

### Hierarchical Queries

```php
// Get districts in a specific regency
$regencyDistricts = District::where('regency_code', '33.75')->get();

// Get districts in a specific province
$provinceDistricts = District::where('province_code', '33')->get();

// Get districts with their parent regions
$districts = District::with(['regency', 'province'])->get();

// Get districts in multiple regencies
$districts = District::whereIn('regency_code', ['33.75', '33.76', '33.77'])->get();
```

### With Relationships

```php
// Load district with all relationships
$district = District::with(['province', 'regency', 'villages'])->find('33.75.01');

// Load specific columns from relationships
$districts = District::with([
    'province:code,name',
    'regency:code,name',
    'villages:code,district_code,name,postal_code'
])->get();

// Count related records
$districts = District::withCount('villages')->get();
```

### Filtering and Sorting

```php
// Order by name
$districts = District::orderBy('name')->get();

// Get districts with coordinates
$districtsWithCoords = District::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();

// Filter by name patterns
$centralDistricts = District::where('name', 'like', '%Tengah%')->get();
$northDistricts = District::where('name', 'like', '%Utara%')->get();
```

### Geographic Operations

```php
// Find districts in coordinate range
$districts = District::whereBetween('latitude', [-7, -6])
    ->whereBetween('longitude', [109, 111])
    ->get();

// Get district center coordinates
$district = District::find('33.75.01');
if ($district->latitude && $district->longitude) {
    echo "District center: {$district->latitude}, {$district->longitude}";
}
```

## Code Structure

### District Codes

District codes follow the pattern: `XX.YY.ZZ`
- `XX` = Province code (2 digits)
- `YY` = Regency code within province (2 digits)
- `ZZ` = District code within regency (2 digits)

```php
$district = District::find('33.75.01');
echo $district->province_code; // "33" (Central Java)
echo $district->regency_code;  // "33.75" (Pekalongan City)
echo explode('.', $district->code)[2]; // "01" (First district in Pekalongan)
```

### Hierarchical Navigation

```php
// Navigate up the hierarchy
$district = District::find('33.75.01');
$regency = $district->regency;
$province = $district->province;

echo "Full hierarchy: {$province->name} > {$regency->name} > {$district->name}";

// Navigate down the hierarchy
$villages = $district->villages;
echo "Villages in {$district->name}: {$villages->count()}";
```

## Postal Code Operations

```php
$district = District::find('33.75.01');

// Get all postal codes in district
$postalCodes = $district->postal_codes;
echo "Postal codes in {$district->name}: " . implode(', ', $postalCodes);

// Find districts by postal code
$districts = District::whereHas('villages', function ($query) {
    $query->where('postal_code', '51111');
})->get();

// Group villages by postal code
$district = District::with('villages')->find('33.75.01');
$villagesByPostal = $district->villages->groupBy('postal_code');
```

## Aggregations and Statistics

```php
// Count villages per district
$districtsWithCounts = District::withCount('villages')->get();

// Get district with most villages
$topDistrict = District::withCount('villages')
    ->orderBy('villages_count', 'desc')
    ->first();

// Group by regency
$districtsByRegency = District::with('regency')
    ->get()
    ->groupBy('regency.name');

// Statistics by province
$districtCounts = District::selectRaw('province_code, count(*) as total')
    ->groupBy('province_code')
    ->get();
```

## Performance Tips

### Efficient Queries

```php
// Good: Use pagination for large datasets
$districts = District::paginate(50);

// Good: Filter by parent region first
$districts = District::where('regency_code', '33.75')
    ->with('villages')
    ->get();

// Good: Select specific columns
$districts = District::select('code', 'name', 'regency_code')->get();

// Avoid: Loading all districts at once
$districts = District::all(); // 7,266 records!
```

### Caching Strategies

```php
use Illuminate\Support\Facades\Cache;

// Cache districts by regency
function getDistrictsByRegency($regencyCode) {
    $cacheKey = "districts.regency.{$regencyCode}";
    
    return Cache::remember($cacheKey, 3600, function () use ($regencyCode) {
        return District::where('regency_code', $regencyCode)
            ->orderBy('name')
            ->get(['code', 'name']);
    });
}

// Cache district with relationships
function getDistrictWithDetails($code) {
    $cacheKey = "district.details.{$code}";
    
    return Cache::remember($cacheKey, 3600, function () use ($code) {
        return District::with(['regency', 'province', 'villages'])
            ->find($code);
    });
}
```

## Validation

### Form Validation

```php
// Validate district exists and belongs to regency
'district_code' => [
    'required',
    'exists:nusa.districts,code',
    function ($attribute, $value, $fail) {
        $district = District::find($value);
        if (!$district || $district->regency_code !== request('regency_code')) {
            $fail('The selected district is invalid for this regency.');
        }
    }
]
```

### Custom Validation Rules

```php
use Illuminate\Contracts\Validation\Rule;

class ValidDistrictForRegency implements Rule
{
    private $regencyCode;
    
    public function __construct($regencyCode)
    {
        $this->regencyCode = $regencyCode;
    }
    
    public function passes($attribute, $value)
    {
        $district = District::find($value);
        return $district && $district->regency_code === $this->regencyCode;
    }
    
    public function message()
    {
        return 'The selected district does not belong to the specified regency.';
    }
}

// Usage
'district_code' => ['required', new ValidDistrictForRegency($regencyCode)]
```

## Database Schema

```sql
CREATE TABLE districts (
    code VARCHAR(8) PRIMARY KEY,
    regency_code VARCHAR(5) NOT NULL,
    province_code VARCHAR(2) NOT NULL,
    name VARCHAR(255) NOT NULL,
    latitude DOUBLE NULL,
    longitude DOUBLE NULL,
    FOREIGN KEY (regency_code) REFERENCES regencies(code),
    FOREIGN KEY (province_code) REFERENCES provinces(code)
);

-- Indexes
CREATE INDEX idx_districts_regency ON districts(regency_code);
CREATE INDEX idx_districts_province ON districts(province_code);
CREATE INDEX idx_districts_name ON districts(name);
CREATE INDEX idx_districts_coordinates ON districts(latitude, longitude);
```

## Constants

```php
// Total number of districts in Indonesia
District::count(); // 7,266

// Average districts per regency
$avgDistrictsPerRegency = District::count() / Regency::count(); // ~14.1
```

## Related Models

- **[Province Model](/api/models/province)** - Grandparent administrative division
- **[Regency Model](/api/models/regency)** - Parent administrative division
- **[Village Model](/api/models/village)** - Child administrative division
- **[Address Model](/api/models/address)** - Address management with district reference
