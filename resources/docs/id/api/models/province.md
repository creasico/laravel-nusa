# Model Provinsi

Dokumentasi lengkap untuk model Provinsi Laravel Nusa, termasuk atribut, relasi, scope, dan metode yang tersedia untuk mengelola data provinsi di Indonesia.

This comprehensive documentation covers the Province model in Laravel Nusa, including attributes, relationships, scopes, and available methods for managing Indonesian province data.

## Model Overview

The Province model represents the highest level of Indonesia's administrative hierarchy. With 34 provinces covering the entire archipelago, this model serves as the entry point for accessing regional administrative data.

### Basic Usage

```php
use Creasi\Nusa\Models\Province;

// Get all provinces
$provinces = Province::all();

// Find specific province
$jateng = Province::find('33'); // Central Java

// Search provinces
$javaProvinces = Province::search('jawa')->get();
```

## Model Attributes

### Database Schema

| Attribute | Type | Description |
|-----------|------|-------------|
| `code` | string(2) | Primary key, two-digit province code |
| `name` | string | Official province name |
| `latitude` | decimal(10,8) | Center point latitude coordinate |
| `longitude` | decimal(11,8) | Center point longitude coordinate |

### Fillable Attributes

```php
protected $fillable = [
    'code',
    'name', 
    'latitude',
    'longitude'
];
```

### Casts

```php
protected $casts = [
    'latitude' => 'decimal:8',
    'longitude' => 'decimal:8'
];
```

## Relationships

### Downward Relationships

```php
// Direct relationships
public function regencies(): HasMany
public function districts(): HasManyThrough  
public function villages(): HasManyThrough

// Usage examples
$province = Province::find('33');
$regencies = $province->regencies; // All regencies in province
$districts = $province->districts; // All districts in province
$villages = $province->villages;   // All villages in province
```

### Relationship Counts

```php
// Load with counts
$provinces = Province::withCount(['regencies', 'districts', 'villages'])->get();

foreach ($provinces as $province) {
    echo "{$province->name}:";
    echo "- Regencies: {$province->regencies_count}";
    echo "- Districts: {$province->districts_count}"; 
    echo "- Villages: {$province->villages_count}";
}
```

## Scopes

### Search Scope

```php
// Search by name or code
$provinces = Province::search('jawa')->get();
$province = Province::search('33')->first();
```

### Custom Scopes

```php
// Java provinces scope
public function scopeJava($query)
{
    return $query->whereIn('code', ['31', '32', '33', '34', '35', '36']);
}

// Usage
$javaProvinces = Province::java()->get();
```

## Methods

### Accessor Methods

```php
// Get formatted display name
public function getDisplayNameAttribute()
{
    return "Provinsi {$this->name}";
}

// Usage
echo $province->display_name; // "Provinsi Jawa Tengah"
```

### Custom Methods

```php
// Get total area (if area data available)
public function getTotalArea()
{
    return $this->regencies()->sum('area_km2');
}

// Get population (if population data available)  
public function getTotalPopulation()
{
    return $this->regencies()->sum('population');
}
```

## Usage Examples

### Basic Operations

```php
// Create new province (rarely needed)
$province = Province::create([
    'code' => '99',
    'name' => 'New Province',
    'latitude' => -6.2088,
    'longitude' => 106.8456
]);

// Update province
$province = Province::find('33');
$province->update(['name' => 'Updated Name']);

// Delete province (rarely needed)
$province->delete();
```

### Querying with Relationships

```php
// Get provinces with specific regency types
$provincesWithCities = Province::whereHas('regencies', function ($query) {
    $query->where('name', 'like', '%Kota%');
})->get();

// Get provinces by regency count
$largeProvinces = Province::has('regencies', '>=', 20)->get();

// Get provinces with villages in specific postal code
$provinces = Province::whereHas('villages', function ($query) {
    $query->where('postal_code', '50132');
})->get();
```

### Geographic Queries

```php
// Find provinces within bounding box
$provinces = Province::whereBetween('latitude', [-8, -6])
    ->whereBetween('longitude', [106, 108])
    ->get();

// Order by distance from point
$centerLat = -6.2088;
$centerLng = 106.8456;

$provinces = Province::selectRaw("
    *,
    (6371 * acos(
        cos(radians(?)) * 
        cos(radians(latitude)) * 
        cos(radians(longitude) - radians(?)) + 
        sin(radians(?)) * 
        sin(radians(latitude))
    )) AS distance
", [$centerLat, $centerLng, $centerLat])
->orderBy('distance')
->get();
```

## Integration Examples

### With User Model

```php
use Creasi\Nusa\Models\Concerns\WithProvince;

class User extends Model
{
    use WithProvince;
    
    protected $fillable = ['name', 'email', 'province_code'];
}

// Usage
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com', 
    'province_code' => '33'
]);

echo $user->province->name; // "Jawa Tengah"
```

### Business Analytics

```php
class ProvinceAnalytics
{
    public function getBusinessDistribution()
    {
        return Province::withCount('businesses')
            ->orderBy('businesses_count', 'desc')
            ->get()
            ->map(function ($province) {
                return [
                    'province' => $province->name,
                    'business_count' => $province->businesses_count,
                    'market_share' => $this->calculateMarketShare($province)
                ];
            });
    }
}
```

## Performance Considerations

### Efficient Queries

```php
// Select only needed fields
$provinces = Province::select('code', 'name')->get();

// Use pagination for large datasets
$provinces = Province::paginate(10);

// Eager load relationships
$provinces = Province::with(['regencies' => function ($query) {
    $query->select('code', 'name', 'province_code');
}])->get();
```

### Caching

```php
// Cache province list
$provinces = Cache::remember('provinces', 3600, function () {
    return Province::select('code', 'name')->get();
});

// Cache with relationships
$province = Cache::remember("province_33", 3600, function () {
    return Province::with('regencies')->find('33');
});
```

## Validation

### Form Requests

```php
class ProvinceRequest extends FormRequest
{
    public function rules()
    {
        return [
            'code' => 'required|string|size:2|unique:nusa.provinces,code',
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ];
    }
}
```

### Custom Validation Rules

```php
class ValidProvinceCode implements Rule
{
    public function passes($attribute, $value)
    {
        return Province::where('code', $value)->exists();
    }
    
    public function message()
    {
        return 'The selected province code is invalid.';
    }
}
```

## API Resources

### Province Resource

```php
class ProvinceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ],
            'statistics' => [
                'regencies' => $this->regencies_count,
                'districts' => $this->districts_count,
                'villages' => $this->villages_count
            ],
            'links' => [
                'self' => route('api.provinces.show', $this->code),
                'regencies' => route('api.provinces.regencies', $this->code)
            ]
        ];
    }
}
```

## Testing

### Model Tests

```php
class ProvinceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_province()
    {
        $province = Province::create([
            'code' => '99',
            'name' => 'Test Province',
            'latitude' => -6.2088,
            'longitude' => 106.8456
        ]);
        
        $this->assertDatabaseHas('nusa.provinces', [
            'code' => '99',
            'name' => 'Test Province'
        ]);
    }
    
    public function test_can_search_provinces()
    {
        Province::factory()->create(['name' => 'Jawa Tengah']);
        Province::factory()->create(['name' => 'Jawa Barat']);
        
        $results = Province::search('tengah')->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals('Jawa Tengah', $results->first()->name);
    }
    
    public function test_has_regencies_relationship()
    {
        $province = Province::factory()->create();
        $regency = Regency::factory()->create(['province_code' => $province->code]);
        
        $this->assertTrue($province->regencies->contains($regency));
    }
}
```

## Next Steps

- **[Regency Model](/id/api/models/regency)** - Model kabupaten/kota documentation
- **[District Model](/id/api/models/district)** - Model kecamatan documentation  
- **[Village Model](/id/api/models/village)** - Model kelurahan/desa documentation
- **[Province API](/id/api/provinces)** - Province API endpoints
