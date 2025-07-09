# Models & Relationships

Laravel Nusa provides four primary Eloquent models representing the Indonesian administrative hierarchy. Each model includes proper relationships, scopes, and attributes for working with administrative data.

## Model Overview

### Administrative Hierarchy

```
Province (Provinsi)
├── Regency (Kabupaten/Kota)
│   ├── District (Kecamatan)
│   │   └── Village (Kelurahan/Desa)
```

### Model Classes

- `Creasi\Nusa\Models\Province` - Provincial level (34 records)
- `Creasi\Nusa\Models\Regency` - Regency/City level (514 records)
- `Creasi\Nusa\Models\District` - District level (7,266 records)
- `Creasi\Nusa\Models\Village` - Village level (83,467 records)

## Base Model Features

All models extend a common base class with shared functionality:

### Common Attributes

```php
// All models have these attributes
$model->code;        // string - Official administrative code
$model->name;        // string - Official name
$model->coordinates; // array|null - Geographic boundary coordinates
```

### Search Scope

All models include a `search()` scope for finding by code or name:

```php
use Creasi\Nusa\Models\Province;

// Search by name (case-insensitive)
$provinces = Province::search('jawa')->get();

// Search by code
$province = Province::search('33')->first();

// Search works with partial matches
$regencies = Regency::search('semarang')->get();
```

### Database Configuration

Models use a separate database connection:

```php
// Models automatically use the 'nusa' connection
$province = Province::find('33');
echo $province->getConnectionName(); // 'nusa'
```

## Province Model

Represents Indonesian provinces (provinsi).

### Attributes

```php
use Creasi\Nusa\Models\Province;

$province = Province::find('33');

echo $province->code;        // '33'
echo $province->name;        // 'Jawa Tengah'
echo $province->latitude;    // -6.9934809206806
echo $province->longitude;   // 110.42024335421
$coordinates = $province->coordinates; // Array of boundary coordinates
```

### Relationships

```php
// One-to-many relationships
$regencies = $province->regencies;  // Collection of Regency models
$districts = $province->districts;  // Collection of District models  
$villages = $province->villages;    // Collection of Village models

// Count related records
echo $province->regencies()->count(); // Number of regencies
echo $province->districts()->count(); // Number of districts
echo $province->villages()->count();  // Number of villages
```

### Postal Codes

Get all postal codes within a province:

```php
$postalCodes = $province->postal_codes; // Array of postal codes
```

### Usage Examples

```php
use Creasi\Nusa\Models\Province;

// Get all provinces
$provinces = Province::all();

// Find specific province
$jateng = Province::find('33');
$jateng = Province::search('Jawa Tengah')->first();

// Get provinces with relationships
$provinces = Province::with(['regencies'])->get();

// Get provinces in specific region
$javaProvinces = Province::search('jawa')->get();
```

## Regency Model

Represents regencies and cities (kabupaten/kota).

### Attributes

```php
use Creasi\Nusa\Models\Regency;

$regency = Regency::find('3375');

echo $regency->code;           // '3375'
echo $regency->province_code;  // '33'
echo $regency->name;           // 'Kota Pekalongan'
echo $regency->latitude;       // -6.8969497174987
echo $regency->longitude;      // 109.66208089654
```

### Relationships

```php
// Belongs to province
$province = $regency->province; // Province model

// Has many districts and villages
$districts = $regency->districts; // Collection of District models
$villages = $regency->villages;   // Collection of Village models
```

### Usage Examples

```php
use Creasi\Nusa\Models\Regency;

// Get all regencies
$regencies = Regency::all();

// Get regencies in a province
$regencies = Regency::where('province_code', '33')->get();

// Find by name
$semarang = Regency::search('semarang')->first();

// Get with relationships
$regency = Regency::with(['province', 'districts'])->find('3375');
```

## District Model

Represents districts (kecamatan).

### Attributes

```php
use Creasi\Nusa\Models\District;

$district = District::find('337501');

echo $district->code;          // '337501'
echo $district->regency_code;  // '3375'
echo $district->province_code; // '33'
echo $district->name;          // 'Pekalongan Barat'
```

### Relationships

```php
// Belongs to province and regency
$province = $district->province; // Province model
$regency = $district->regency;   // Regency model

// Has many villages
$villages = $district->villages; // Collection of Village models
```

### Postal Codes

Get postal codes within a district:

```php
$postalCodes = $district->postal_codes; // Array of postal codes
```

### Usage Examples

```php
use Creasi\Nusa\Models\District;

// Get all districts
$districts = District::all();

// Get districts in a regency
$districts = District::where('regency_code', '3375')->get();

// Get districts in a province
$districts = District::where('province_code', '33')->get();

// Paginate large results
$districts = District::paginate(15);
```

## Village Model

Represents villages and urban villages (kelurahan/desa).

### Attributes

```php
use Creasi\Nusa\Models\Village;

$village = Village::find('3375011002');

echo $village->code;          // '3375011002'
echo $village->district_code; // '337501'
echo $village->regency_code;  // '3375'
echo $village->province_code; // '33'
echo $village->name;          // 'Medono'
echo $village->postal_code;   // '51111'
```

### Relationships

```php
// Belongs to province, regency, and district
$province = $village->province; // Province model
$regency = $village->regency;   // Regency model
$district = $village->district; // District model
```

### Usage Examples

```php
use Creasi\Nusa\Models\Village;

// Get all villages (use pagination for performance)
$villages = Village::paginate(50);

// Get villages in a district
$villages = Village::where('district_code', '337501')->get();

// Find by postal code
$villages = Village::where('postal_code', '51111')->get();

// Search by name
$villages = Village::search('medono')->get();
```

## Advanced Querying

### Eager Loading

Load relationships efficiently:

```php
// Load multiple relationships
$provinces = Province::with(['regencies.districts.villages'])->get();

// Load specific columns
$provinces = Province::with(['regencies:code,province_code,name'])->get();

// Conditional loading
$provinces = Province::with(['regencies' => function ($query) {
    $query->where('name', 'like', '%kota%');
}])->get();
```

### Filtering and Searching

```php
// Multiple search terms
$results = Province::search('jawa')
    ->orWhere(function ($query) {
        $query->search('sumatra');
    })
    ->get();

// Filter by multiple codes
$provinces = Province::whereIn('code', ['33', '34', '35'])->get();

// Complex filtering
$regencies = Regency::where('province_code', '33')
    ->where('name', 'like', '%kota%')
    ->orderBy('name')
    ->get();
```

### Aggregations

```php
// Count records by province
$counts = Regency::selectRaw('province_code, count(*) as total')
    ->groupBy('province_code')
    ->get();

// Get provinces with most regencies
$provinces = Province::withCount('regencies')
    ->orderBy('regencies_count', 'desc')
    ->get();
```

## Model Contracts

Laravel Nusa provides contracts (interfaces) for type hinting:

```php
use Creasi\Nusa\Contracts\{Province, Regency, District, Village};

class LocationService
{
    public function __construct(
        private Province $provinceModel,
        private Regency $regencyModel,
        private District $districtModel,
        private Village $villageModel
    ) {}
    
    public function getLocationHierarchy(string $villageCode): array
    {
        $village = $this->villageModel->find($villageCode);
        
        return [
            'village' => $village,
            'district' => $village->district,
            'regency' => $village->regency,
            'province' => $village->province,
        ];
    }
}
```

## Performance Tips

### Use Pagination

For large datasets, always use pagination:

```php
// Good - paginated results
$villages = Village::paginate(50);

// Avoid - loading all records
$villages = Village::all(); // 83,467 records!
```

### Select Specific Columns

Only load columns you need:

```php
// Good - specific columns
$provinces = Province::select('code', 'name')->get();

// Avoid - all columns including large coordinates
$provinces = Province::all();
```

### Use Appropriate Indexes

The database includes proper indexes for common queries:

```php
// These queries are optimized
Province::find('33');                    // Primary key
Regency::where('province_code', '33');   // Foreign key index
Village::where('postal_code', '51111');  // Postal code index
```

## Next Steps

- **[Database Structure](/guide/database)** - Learn about the underlying database schema
- **[Address Management](/guide/addresses)** - Integrate addresses into your models
- **[API Reference](/api/overview)** - Explore the RESTful API endpoints
