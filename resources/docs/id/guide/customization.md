# Kustomisasi

Panduan lengkap untuk mengkustomisasi Laravel Nusa sesuai dengan kebutuhan aplikasi Anda, termasuk penggunaan trait, pembuatan model kustom, dan integrasi dengan sistem existing.

## Trait yang Tersedia

Laravel Nusa menyediakan beberapa trait yang dapat Anda gunakan untuk menambahkan fungsionalitas lokasi ke model existing Anda:

### WithProvince Trait

```php
use Creasi\Nusa\Models\Concerns\WithProvince;

class BusinessUnit extends Model
{
    use WithProvince;
    
    protected $fillable = ['name', 'province_code'];
}

// Usage
$unit = BusinessUnit::create([
    'name' => 'Central Java Division',
    'province_code' => '33'
]);

echo $unit->province->name; // "Jawa Tengah"
```

### WithRegency Trait

```php
use Creasi\Nusa\Models\Concerns\WithRegency;

class Branch extends Model
{
    use WithRegency;
    
    protected $fillable = ['name', 'regency_code'];
}
```

### WithDistrict Trait

```php
use Creasi\Nusa\Models\Concerns\WithDistrict;

class ServiceCenter extends Model
{
    use WithDistrict;
    
    protected $fillable = ['name', 'district_code'];
}
```

### WithVillage Trait

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class Customer extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
}
```

## Multiple Location Traits

### WithDistricts Trait (Multiple Districts)

```php
use Creasi\Nusa\Models\Concerns\WithDistricts;

class DeliveryZone extends Model
{
    use WithDistricts;
}

// Usage
$zone = DeliveryZone::create(['name' => 'Zone A']);
$zone->districts()->attach(['33.74.01', '33.74.02', '33.74.03']);

// Get all districts in this zone
$districts = $zone->districts;
```

### WithVillages Trait (Multiple Villages)

```php
use Creasi\Nusa\Models\Concerns\WithVillages;

class CoverageArea extends Model
{
    use WithVillages;
}

// Usage
$area = CoverageArea::create(['name' => 'Coverage Area 1']);
$area->villages()->attach([
    '33.74.01.1001',
    '33.74.01.1002',
    '33.74.01.1003'
]);
```

## Address Management Traits

### WithAddress Trait (Single Address)

```php
use Creasi\Nusa\Models\Concerns\WithAddress;

class Company extends Model
{
    use WithAddress;
}

// Usage
$company = Company::create(['name' => 'PT Example']);
$company->address()->create([
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Sudirman No. 123'
]);
```

### WithAddresses Trait (Multiple Addresses)

```php
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model
{
    use WithAddresses;
}

// Usage
$user = User::find(1);
$user->addresses()->create([
    'name' => 'Home',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 456',
    'is_default' => true
]);
```

## Coordinate Traits

### WithCoordinate Trait

```php
use Creasi\Nusa\Models\Concerns\WithCoordinate;

class Store extends Model
{
    use WithCoordinate;
    
    protected $fillable = [
        'name',
        'latitude',
        'longitude'
    ];
}

// Usage
$store = Store::create([
    'name' => 'Store A',
    'latitude' => -6.2088,
    'longitude' => 106.8456
]);

// Calculate distance to another store
$distance = $store->distanceTo($anotherStore);
```

## Custom Model Extensions

### Extending Base Models

```php
// app/Models/CustomProvince.php
namespace App\Models;

use Creasi\Nusa\Models\Province as BaseProvince;

class CustomProvince extends BaseProvince
{
    protected $appends = ['display_name'];
    
    public function getDisplayNameAttribute()
    {
        return "Provinsi {$this->name}";
    }
    
    // Add custom relationships
    public function businesses()
    {
        return $this->hasMany(Business::class, 'province_code', 'code');
    }
    
    // Add custom scopes
    public function scopeJava($query)
    {
        return $query->whereIn('code', ['31', '32', '33', '34', '35', '36']);
    }
}
```

### Configuration Update

```php
// config/nusa.php
return [
    'models' => [
        'province' => \App\Models\CustomProvince::class,
        'regency' => \Creasi\Nusa\Models\Regency::class,
        'district' => \Creasi\Nusa\Models\District::class,
        'village' => \Creasi\Nusa\Models\Village::class,
        'address' => \Creasi\Nusa\Models\Address::class,
    ],
];
```

## Creating Custom Traits

### Location Analytics Trait

```php
// app/Traits/HasLocationAnalytics.php
namespace App\Traits;

trait HasLocationAnalytics
{
    public function getLocationStats()
    {
        return [
            'total_customers' => $this->customers()->count(),
            'total_orders' => $this->orders()->count(),
            'revenue' => $this->orders()->sum('total'),
            'coverage_area' => $this->getCoverageArea()
        ];
    }
    
    public function getCoverageArea()
    {
        // Implementation depends on your business logic
        return $this->serviceAreas()->count();
    }
}
```

### Geographic Search Trait

```php
// app/Traits/HasGeographicSearch.php
namespace App\Traits;

trait HasGeographicSearch
{
    public function scopeNearby($query, $latitude, $longitude, $radiusKm)
    {
        return $query->selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(latitude))
            )) AS distance
        ", [$latitude, $longitude, $latitude])
        ->having('distance', '<=', $radiusKm)
        ->orderBy('distance');
    }
}
```

## Integration Patterns

### User Model Integration

```php
class User extends Authenticatable
{
    use WithVillage, WithAddresses;
    
    protected $fillable = [
        'name',
        'email',
        'village_code'
    ];
    
    // Get user's full location
    public function getFullLocationAttribute()
    {
        if ($this->village) {
            return $this->village->full_address;
        }
        return null;
    }
}
```

### Business Model Integration

```php
class Business extends Model
{
    use WithRegency, WithCoordinate, HasLocationAnalytics;
    
    protected $fillable = [
        'name',
        'regency_code',
        'latitude',
        'longitude'
    ];
    
    // Get nearby businesses
    public function getNearbyBusinesses($radiusKm = 10)
    {
        return static::nearby($this->latitude, $this->longitude, $radiusKm)
            ->where('id', '!=', $this->id)
            ->get();
    }
}
```

## Advanced Customization

### Custom Validation Rules

```php
// app/Rules/ValidIndonesianLocation.php
class ValidIndonesianLocation implements Rule
{
    public function passes($attribute, $value)
    {
        // Custom validation logic
        return Village::where('code', $value)->exists();
    }
    
    public function message()
    {
        return 'The selected location is not valid.';
    }
}
```

### Custom API Resources

```php
// app/Http/Resources/LocationResource.php
class LocationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => [
                'village' => $this->village->name,
                'district' => $this->village->district->name,
                'regency' => $this->village->regency->name,
                'province' => $this->village->province->name,
                'postal_code' => $this->village->postal_code
            ],
            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ]
        ];
    }
}
```

## Performance Optimization

### Caching Location Data

```php
class CachedLocationService
{
    public function getProvinces()
    {
        return Cache::remember('provinces', 3600, function () {
            return Province::select('code', 'name')->get();
        });
    }
    
    public function getRegenciesByProvince($provinceCode)
    {
        return Cache::remember("regencies_{$provinceCode}", 3600, function () use ($provinceCode) {
            return Regency::where('province_code', $provinceCode)
                ->select('code', 'name')
                ->get();
        });
    }
}
```

### Database Optimization

```php
// Add indexes for better performance
Schema::table('your_table', function (Blueprint $table) {
    $table->index('village_code');
    $table->index(['latitude', 'longitude']);
});
```

## Langkah Selanjutnya

- **[RESTful API](/id/guide/api)** - API customization and endpoints
- **[Custom Models](/id/examples/custom-models)** - Advanced model customization examples
- **[Address Management](/id/guide/addresses)** - Address system integration
- **[API Reference](/id/api/concerns/)** - Complete trait documentation
