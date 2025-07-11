# WithVillages Trait

The `WithVillages` trait adds a `hasMany` relationship to villages and provides postal code functionality for models that contain multiple villages.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithVillages
```

## Usage

### Basic Implementation

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithVillages;

class District extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'code',
        'name',
        'regency_code'
    ];
}
```

## Features

- Provides `villages()` hasMany relationship
- Automatically appends `postal_codes` attribute
- Provides `postal_codes` accessor for unique postal codes
- Includes `distinctVillagesByPostalCodes()` relationship

### Basic Usage

```php
$district = District::with('villages')->first();

echo "District: {$district->name}";
echo "Villages: {$district->villages->count()}";
echo "Postal codes: " . $district->postal_codes->implode(', ');
```

## Common Use Cases

### Administrative Management

```php
class AdministrativeRegion extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'name',
        'type',
        'parent_code'
    ];
    
    public function getVillageCountAttribute()
    {
        return $this->villages()->count();
    }
    
    public function getUniquePostalCodesAttribute()
    {
        return $this->postal_codes->unique()->sort()->values();
    }
    
    public function getVillagesByPostalCodeAttribute()
    {
        return $this->villages->groupBy('postal_code');
    }
}

// Usage
$region = AdministrativeRegion::first();
echo "Total villages: {$region->village_count}";
echo "Postal codes: " . $region->unique_postal_codes->implode(', ');
```

### Service Coverage Areas

```php
class ServiceArea extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'name',
        'service_type'
    ];
    
    public function getCoverageStatsAttribute()
    {
        return [
            'total_villages' => $this->villages->count(),
            'postal_codes' => $this->postal_codes->count(),
            'regencies' => $this->villages->pluck('regency_code')->unique()->count(),
            'provinces' => $this->villages->pluck('province_code')->unique()->count()
        ];
    }
    
    public function scopeWithMinimumVillages($query, $count)
    {
        return $query->has('villages', '>=', $count);
    }
}
```

### Delivery Zones

```php
class DeliveryZone extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'name',
        'delivery_cost',
        'estimated_days'
    ];
    
    protected $casts = [
        'delivery_cost' => 'decimal:2'
    ];
    
    public function addVillage($villageCode)
    {
        $village = Village::find($villageCode);
        
        if ($village) {
            return $this->villages()->save($village);
        }
        
        return false;
    }
    
    public function removeVillage($villageCode)
    {
        return $this->villages()->where('code', $villageCode)->delete();
    }
    
    public function canDeliverTo($villageCode)
    {
        return $this->villages()->where('code', $villageCode)->exists();
    }
    
    public function getDeliveryInfoAttribute()
    {
        return [
            'cost' => $this->delivery_cost,
            'estimated_days' => $this->estimated_days,
            'coverage' => $this->villages->count() . ' villages',
            'postal_codes' => $this->postal_codes->count() . ' postal codes'
        ];
    }
}

// Usage
$zone = DeliveryZone::first();
$canDeliver = $zone->canDeliverTo('33.74.01.1001');
$info = $zone->delivery_info;
```

## Advanced Usage

### Postal Code Analysis

```php
class PostalCodeAnalyzer
{
    public static function analyzeRegion($model)
    {
        $villages = $model->villages()->with(['district', 'regency', 'province'])->get();
        
        return [
            'total_villages' => $villages->count(),
            'postal_codes' => $villages->pluck('postal_code')->unique()->count(),
            'districts' => $villages->pluck('district.name')->unique()->count(),
            'regencies' => $villages->pluck('regency.name')->unique()->count(),
            'provinces' => $villages->pluck('province.name')->unique()->count(),
            'postal_code_distribution' => $villages->groupBy('postal_code')
                ->map(function ($group, $postalCode) {
                    return [
                        'postal_code' => $postalCode,
                        'village_count' => $group->count(),
                        'villages' => $group->pluck('name')->toArray()
                    ];
                })->values()
        ];
    }
}
```

### Geographic Clustering

```php
class RegionalCluster extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'name',
        'cluster_type'
    ];
    
    public function addVillagesByPostalCode($postalCode)
    {
        $villages = Village::where('postal_code', $postalCode)->get();
        
        foreach ($villages as $village) {
            $this->villages()->save($village);
        }
        
        return $villages->count();
    }
    
    public function addVillagesByRegency($regencyCode)
    {
        $villages = Village::where('regency_code', $regencyCode)->get();
        
        foreach ($villages as $village) {
            $this->villages()->save($village);
        }
        
        return $villages->count();
    }
    
    public function getGeographicSummaryAttribute()
    {
        $villages = $this->villages;
        
        return [
            'total_villages' => $villages->count(),
            'regencies' => $villages->groupBy('regency_code')->map(function ($group, $code) {
                return [
                    'code' => $code,
                    'name' => $group->first()->regency->name,
                    'village_count' => $group->count()
                ];
            })->values(),
            'postal_codes' => $this->postal_codes->sort()->values()
        ];
    }
}
```

## Performance Tips

### 1. Eager Loading

```php
// Good
$districts = District::with(['villages.regency.province'])->get();

// Bad - N+1 queries
$districts = District::all();
foreach ($districts as $district) {
    echo $district->villages->count(); // N+1 query
}
```

### 2. Counting Villages

```php
$districts = District::withCount('villages')->get();

foreach ($districts as $district) {
    echo "{$district->name}: {$district->villages_count} villages";
}
```

### 3. Postal Code Optimization

```php
// Cache postal codes for better performance
class District extends Model
{
    use WithVillages;
    
    public function getCachedPostalCodesAttribute()
    {
        return Cache::remember(
            "district_postal_codes_{$this->id}",
            3600,
            fn() => $this->postal_codes
        );
    }
}
```

## Related Documentation

- **[Village Model](/en/api/models/village)** - Complete Village model documentation
- **[WithVillage Trait](/en/api/concerns/with-village)** - For single village associations
- **[WithDistricts Trait](/en/api/concerns/with-districts)** - For multiple district relationships
- **[Models & Relationships](/en/guide/models)** - Understanding Laravel Nusa models
