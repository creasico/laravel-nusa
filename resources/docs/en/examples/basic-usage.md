# Basic Usage Examples

This page provides practical examples of using Laravel Nusa in common scenarios. These examples demonstrate the most frequent use cases and patterns you'll encounter when working with Indonesian administrative data.

## Finding Administrative Regions

### By Code

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Find by exact code
$province = Province::find('33');              // Central Java
$regency = Regency::find('33.75');            // Pekalongan City
$district = District::find('33.75.01');       // West Pekalongan
$village = Village::find('33.75.01.1002');    // Medono Village

// Check if found
if ($province) {
    echo "Found: {$province->name}";
} else {
    echo "Province not found";
}
```

### By Name Search

```php
// Case-insensitive search
$provinces = Province::search('jawa')->get();
$regencies = Regency::search('semarang')->get();
$districts = District::search('pekalongan')->get();
$villages = Village::search('medono')->get();

// Get first result
$jateng = Province::search('jawa tengah')->first();
$semarang = Regency::search('kota semarang')->first();
```

### Multiple Search Terms

```php
// Search multiple provinces
$javaProvinces = Province::where(function ($query) {
    $query->search('jawa barat')
          ->orWhere(function ($q) { $q->search('jawa tengah'); })
          ->orWhere(function ($q) { $q->search('jawa timur'); });
})->get();

// Search with code alternatives
$results = Province::search('33')
    ->orWhere(function ($query) {
        $query->search('jawa tengah');
    })->get();
```

## Working with Relationships

### Getting Related Data

```php
use Creasi\Nusa\Models\Province;

$province = Province::find('33');

// Get all regencies in the province
$regencies = $province->regencies;
echo "Regencies in {$province->name}: {$regencies->count()}";

// Get all districts in the province
$districts = $province->districts;
echo "Districts in {$province->name}: {$districts->count()}";

// Get all villages in the province
$villages = $province->villages;
echo "Villages in {$province->name}: {$villages->count()}";
```

### Eager Loading for Performance

```php
// Load province with its regencies
$province = Province::with('regencies')->find('33');

// Load multiple relationships
$province = Province::with(['regencies', 'districts'])->find('33');

// Load nested relationships
$provinces = Province::with(['regencies.districts.villages'])->get();

// Load specific columns only
$provinces = Province::with(['regencies:code,province_code,name'])->get();
```

### Reverse Relationships

```php
use Creasi\Nusa\Models\Village;

$village = Village::find('33.75.01.1002');

// Get parent administrative levels
$district = $village->district;
$regency = $village->regency;
$province = $village->province;

echo "Full address: {$village->name}, {$district->name}, {$regency->name}, {$province->name}";
```

## Address Form Integration

For building complete address forms with cascading dropdowns, see the dedicated [Address Forms](/en/examples/address-forms) guide which covers:

- Complete backend controller implementation
- Frontend JavaScript integration with multiple frameworks
- Form validation and error handling
- Styling and UX considerations

### Quick Address Form Data

```php
// Simple cascading dropdown data endpoints
class AddressController extends Controller
{
    public function getRegencies(Request $request)
    {
        return Regency::where('province_code', $request->province_code)
            ->orderBy('name')
            ->get(['code', 'name']);
    }

    public function getDistricts(Request $request)
    {
        return District::where('regency_code', $request->regency_code)
            ->orderBy('name')
            ->get(['code', 'name']);
    }

    public function getVillages(Request $request)
    {
        return Village::where('district_code', $request->district_code)
            ->orderBy('name')
            ->get(['code', 'name', 'postal_code']);
    }
}
```

## Geographic Data Usage

### Working with Coordinates

```php
use Creasi\Nusa\Models\Province;

$province = Province::find('33');

// Get center coordinates
$latitude = $province->latitude;
$longitude = $province->longitude;

// Get boundary coordinates (if available)
$boundaries = $province->coordinates;

if ($boundaries) {
    echo "Province has " . count($boundaries) . " boundary points";
    
    // Use with mapping libraries
    $geoJson = [
        'type' => 'Polygon',
        'coordinates' => [$boundaries]
    ];
}
```

### Postal Code Aggregation

```php
// Get all postal codes in a province
$province = Province::find('33');
$postalCodes = $province->postal_codes;

echo "Postal codes in {$province->name}: " . implode(', ', $postalCodes);

// Get postal codes in a district
$district = District::find('33.75.01');
$districtPostalCodes = $district->postal_codes;

// Find villages by postal code
$villages = Village::where('postal_code', '51111')->get();
```

## Data Validation

### Form Request Validation

```php
use Illuminate\Foundation\Http\FormRequest;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class AddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'province_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Province::find($value)) {
                        $fail('The selected province is invalid.');
                    }
                },
            ],
            'regency_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $regency = Regency::find($value);
                    if (!$regency || $regency->province_code !== $this->province_code) {
                        $fail('The selected regency is invalid for this province.');
                    }
                },
            ],
            'district_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $district = District::find($value);
                    if (!$district || $district->regency_code !== $this->regency_code) {
                        $fail('The selected district is invalid for this regency.');
                    }
                },
            ],
            'village_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $village = Village::find($value);
                    if (!$village || $village->district_code !== $this->district_code) {
                        $fail('The selected village is invalid for this district.');
                    }
                },
            ],
        ];
    }
}
```

### Custom Validation Rules

```php
use Illuminate\Contracts\Validation\Rule;
use Creasi\Nusa\Models\Village;

class ValidIndonesianAddress implements Rule
{
    public function passes($attribute, $value)
    {
        // Validate that all address components are consistent
        $village = Village::find($value['village_code']);
        
        return $village &&
               $village->district_code === $value['district_code'] &&
               $village->regency_code === $value['regency_code'] &&
               $village->province_code === $value['province_code'];
    }
    
    public function message()
    {
        return 'The address components are not consistent.';
    }
}
```

## Performance Optimization

### Efficient Queries

```php
// Good: Use specific columns
$provinces = Province::select('code', 'name')->get();

// Good: Use pagination for large datasets
$villages = Village::paginate(50);

// Good: Use whereIn for multiple codes
$regencies = Regency::whereIn('code', ['33.75', '33.76', '33.77'])->get();

// Avoid: Loading all villages at once
// $allVillages = Village::all(); // 83,467 records!
```

### Caching Strategies

```php
use Illuminate\Support\Facades\Cache;

class LocationService
{
    public function getProvinces()
    {
        return Cache::remember('nusa.provinces', 3600, function () {
            return Province::orderBy('name')->get(['code', 'name']);
        });
    }
    
    public function getRegenciesByProvince(string $provinceCode)
    {
        $cacheKey = "nusa.regencies.{$provinceCode}";
        
        return Cache::remember($cacheKey, 3600, function () use ($provinceCode) {
            return Regency::where('province_code', $provinceCode)
                ->orderBy('name')
                ->get(['code', 'name']);
        });
    }
}
```

## Error Handling

### Graceful Fallbacks

```php
use Creasi\Nusa\Models\Province;

function getProvinceName(string $code): string
{
    try {
        $province = Province::find($code);
        return $province ? $province->name : "Unknown Province ({$code})";
    } catch (Exception $e) {
        Log::error("Failed to get province name for code: {$code}", [
            'error' => $e->getMessage()
        ]);
        return "Unknown Province";
    }
}

function buildFullAddress(array $codes): string
{
    $parts = [];
    
    if ($village = Village::find($codes['village_code'] ?? null)) {
        $parts[] = $village->name;
        $parts[] = $village->district->name;
        $parts[] = $village->regency->name;
        $parts[] = $village->province->name;
    }
    
    return implode(', ', array_filter($parts));
}
```

## Next Steps

- **[API Integration](/en/examples/api-integration)** - Learn to use the RESTful API
- **[Address Forms](/en/examples/address-forms)** - Build complete address forms
- **[Geographic Queries](/en/examples/geographic-queries)** - Work with coordinates and boundaries
