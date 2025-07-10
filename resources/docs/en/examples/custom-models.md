# Custom Models

This guide shows how to extend Laravel Nusa with custom models and integrate Indonesian administrative data into your application's domain models.

## Extending Base Models

### Custom Province Model

```php
<?php

namespace App\Models;

use Creasi\Nusa\Models\Province as BaseProvince;

class Province extends BaseProvince
{
    // Add custom attributes
    protected $appends = ['region_name', 'is_java'];
    
    // Custom accessor
    public function getRegionNameAttribute(): string
    {
        $regions = [
            'Sumatra' => ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21'],
            'Java' => ['31', '32', '33', '34', '35', '36'],
            'Kalimantan' => ['61', '62', '63', '64', '65'],
            'Sulawesi' => ['71', '72', '73', '74', '75', '76'],
            'Eastern Indonesia' => ['81', '82', '91', '94', '95', '96'],
        ];
        
        foreach ($regions as $region => $codes) {
            if (in_array($this->code, $codes)) {
                return $region;
            }
        }
        
        return 'Unknown';
    }
    
    // Custom accessor
    public function getIsJavaAttribute(): bool
    {
        return in_array($this->code, ['31', '32', '33', '34', '35', '36']);
    }
    
    // Custom scope
    public function scopeJava($query)
    {
        return $query->whereIn('code', ['31', '32', '33', '34', '35', '36']);
    }
    
    // Custom scope
    public function scopeOutsideJava($query)
    {
        return $query->whereNotIn('code', ['31', '32', '33', '34', '35', '36']);
    }
    
    // Custom method
    public function getPopulationDensityCategory(): string
    {
        // This would require additional population data
        if ($this->is_java) {
            return 'High';
        }
        
        return 'Medium'; // Simplified logic
    }
}

// Usage
$javaProvinces = Province::java()->get();
$outsideJava = Province::outsideJava()->get();

foreach ($javaProvinces as $province) {
    echo "{$province->name} is in {$province->region_name}";
}
```

### Custom Address Model

```php
<?php

namespace App\Models;

use Creasi\Nusa\Models\Address as BaseAddress;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends BaseAddress
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'address_line',
        'postal_code',
        'is_default',
        // Custom fields
        'label',           // 'Home', 'Office', 'Other'
        'notes',           // Additional notes
        'latitude',        // Custom coordinates
        'longitude',
        'is_verified',     // Address verification status
        'delivery_notes',  // Special delivery instructions
    ];
    
    protected $casts = [
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
    
    protected $appends = ['formatted_label', 'distance_from_center'];
    
    // Custom accessor
    public function getFormattedLabelAttribute(): string
    {
        return $this->label ? ucfirst($this->label) : 'Address';
    }
    
    // Custom accessor with calculation
    public function getDistanceFromCenterAttribute(): ?float
    {
        if (!$this->latitude || !$this->longitude || !$this->village) {
            return null;
        }
        
        return $this->calculateDistance(
            $this->latitude,
            $this->longitude,
            $this->village->latitude,
            $this->village->longitude
        );
    }
    
    // Custom scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
    
    public function scopeByLabel($query, $label)
    {
        return $query->where('label', $label);
    }
    
    public function scopeWithinRadius($query, $lat, $lon, $radiusKm)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("
                (6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )) <= ?
            ", [$lat, $lon, $lat, $radiusKm]);
    }
    
    // Custom methods
    public function markAsVerified(): void
    {
        $this->update(['is_verified' => true]);
    }
    
    public function updateCoordinates(float $lat, float $lon): void
    {
        $this->update([
            'latitude' => $lat,
            'longitude' => $lon,
        ]);
    }
    
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}

// Usage
$homeAddresses = Address::byLabel('home')->get();
$verifiedAddresses = Address::verified()->get();
$nearbyAddresses = Address::withinRadius(-6.200000, 106.816666, 10)->get();
```

## Domain-Specific Models

### Store Model with Location

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Contracts\HasAddress;
use Creasi\Nusa\Models\Concerns\WithAddress;

class Store extends Model implements HasAddress
{
    use WithAddress;
    
    protected $fillable = [
        'name',
        'description',
        'category',
        'phone',
        'email',
        'website',
        'opening_hours',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'address_line',
        'postal_code',
        'latitude',
        'longitude',
        'is_active',
    ];
    
    protected $casts = [
        'opening_hours' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
    
    protected $appends = ['full_location', 'region'];
    
    // Custom accessor
    public function getFullLocationAttribute(): string
    {
        return $this->full_address;
    }
    
    public function getRegionAttribute(): string
    {
        if (!$this->province) {
            return 'Unknown';
        }
        
        $javaProvinces = ['31', '32', '33', '34', '35', '36'];
        return in_array($this->province_code, $javaProvinces) ? 'Java' : 'Outside Java';
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeInProvince($query, $provinceCode)
    {
        return $query->where('province_code', $provinceCode);
    }
    
    public function scopeInRegency($query, $regencyCode)
    {
        return $query->where('regency_code', $regencyCode);
    }
    
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    public function scopeNearby($query, $lat, $lon, $radiusKm = 10)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("
                (6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )) <= ?
            ", [$lat, $lon, $lat, $radiusKm]);
    }
    
    // Methods
    public function getDistanceFrom(float $lat, float $lon): ?float
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }
        
        return $this->calculateDistance($lat, $lon, $this->latitude, $this->longitude);
    }
    
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371;
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}

// Usage
$stores = Store::active()
    ->inProvince('33')
    ->byCategory('restaurant')
    ->with(['province', 'regency'])
    ->get();

$nearbyStores = Store::nearby(-6.200000, 106.816666, 5)
    ->active()
    ->get();
```

### Delivery Zone Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class DeliveryZone extends Model
{
    protected $fillable = [
        'name',
        'type', // 'province', 'regency', 'district', 'village'
        'code', // Administrative code
        'delivery_fee',
        'estimated_days',
        'is_active',
        'notes',
    ];
    
    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'estimated_days' => 'integer',
        'is_active' => 'boolean',
    ];
    
    // Dynamic relationships based on type
    public function administrativeRegion()
    {
        switch ($this->type) {
            case 'province':
                return $this->belongsTo(Province::class, 'code', 'code');
            case 'regency':
                return $this->belongsTo(Regency::class, 'code', 'code');
            case 'district':
                return $this->belongsTo(District::class, 'code', 'code');
            case 'village':
                return $this->belongsTo(Village::class, 'code', 'code');
            default:
                return null;
        }
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    // Methods
    public function covers(string $villageCode): bool
    {
        $village = Village::find($villageCode);
        if (!$village) {
            return false;
        }
        
        switch ($this->type) {
            case 'province':
                return $village->province_code === $this->code;
            case 'regency':
                return $village->regency_code === $this->code;
            case 'district':
                return $village->district_code === $this->code;
            case 'village':
                return $village->code === $this->code;
            default:
                return false;
        }
    }
    
    public static function findForAddress(string $villageCode): ?self
    {
        $village = Village::find($villageCode);
        if (!$village) {
            return null;
        }
        
        // Check in order of specificity: village -> district -> regency -> province
        $zones = [
            ['type' => 'village', 'code' => $village->code],
            ['type' => 'district', 'code' => $village->district_code],
            ['type' => 'regency', 'code' => $village->regency_code],
            ['type' => 'province', 'code' => $village->province_code],
        ];
        
        foreach ($zones as $zone) {
            $deliveryZone = self::active()
                ->where('type', $zone['type'])
                ->where('code', $zone['code'])
                ->first();
                
            if ($deliveryZone) {
                return $deliveryZone;
            }
        }
        
        return null;
    }
}

// Usage
$deliveryZone = DeliveryZone::findForAddress('33.75.01.1002');
if ($deliveryZone) {
    echo "Delivery fee: Rp " . number_format($deliveryZone->delivery_fee);
    echo "Estimated delivery: {$deliveryZone->estimated_days} days";
}
```

## Service Classes

### Location Service

```php
<?php

namespace App\Services;

use Creasi\Nusa\Models\{Province, Regency, District, Village};
use Illuminate\Support\Collection;

class LocationService
{
    public function getLocationHierarchy(string $villageCode): ?array
    {
        $village = Village::with(['district', 'regency', 'province'])
            ->find($villageCode);
            
        if (!$village) {
            return null;
        }
        
        return [
            'village' => [
                'code' => $village->code,
                'name' => $village->name,
                'postal_code' => $village->postal_code,
            ],
            'district' => [
                'code' => $village->district->code,
                'name' => $village->district->name,
            ],
            'regency' => [
                'code' => $village->regency->code,
                'name' => $village->regency->name,
            ],
            'province' => [
                'code' => $village->province->code,
                'name' => $village->province->name,
            ],
            'full_address' => $this->buildFullAddress($village),
        ];
    }
    
    public function findNearestRegency(float $lat, float $lon): ?Regency
    {
        $regencies = Regency::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
            
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($regencies as $regency) {
            $distance = $this->calculateDistance(
                $lat, $lon,
                $regency->latitude, $regency->longitude
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $regency;
            }
        }
        
        return $nearest;
    }
    
    public function getRegionStatistics(): array
    {
        return [
            'provinces' => Province::count(),
            'regencies' => Regency::count(),
            'districts' => District::count(),
            'villages' => Village::count(),
            'java_provinces' => Province::whereIn('code', ['31', '32', '33', '34', '35', '36'])->count(),
            'cities' => Regency::where('name', 'like', '%Kota%')->count(),
            'regencies_proper' => Regency::where('name', 'like', '%Kabupaten%')->count(),
        ];
    }
    
    public function validateAddressHierarchy(array $addressData): bool
    {
        $village = Village::find($addressData['village_code']);
        
        if (!$village) {
            return false;
        }
        
        return $village->district_code === $addressData['district_code'] &&
               $village->regency_code === $addressData['regency_code'] &&
               $village->province_code === $addressData['province_code'];
    }
    
    private function buildFullAddress(Village $village): string
    {
        return implode(', ', array_filter([
            $village->name,
            $village->district->name,
            $village->regency->name,
            $village->province->name,
            $village->postal_code,
        ]));
    }
    
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}

// Usage
$locationService = new LocationService();

$hierarchy = $locationService->getLocationHierarchy('33.75.01.1002');
$nearestRegency = $locationService->findNearestRegency(-6.200000, 106.816666);
$stats = $locationService->getRegionStatistics();
```

## Configuration

### Custom Model Binding

```php
// In your AppServiceProvider
use Creasi\Nusa\Contracts;
use App\Models\{Province, Address};

public function register()
{
    // Bind custom models
    $this->app->bind(Contracts\Province::class, Province::class);
    $this->app->bind(Contracts\Address::class, Address::class);
}
```

### Custom Configuration

```php
// config/creasi/nusa.php
return [
    'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),
    'addressable' => \App\Models\Address::class,
    'routes_enable' => env('CREASI_NUSA_ROUTES_ENABLE', true),
    'routes_prefix' => env('CREASI_NUSA_ROUTES_PREFIX', 'nusa'),
    
    // Custom settings
    'custom_models' => [
        'province' => \App\Models\Province::class,
        'store' => \App\Models\Store::class,
    ],
    
    'delivery' => [
        'default_fee' => 10000,
        'default_days' => 3,
    ],
];
```

These examples show how to extend Laravel Nusa with custom models that add business logic, additional attributes, and domain-specific functionality while maintaining the core administrative data relationships.
