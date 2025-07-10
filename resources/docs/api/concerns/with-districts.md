# WithDistricts Trait

The `WithDistricts` trait adds a `hasMany` relationship to districts, allowing models to have multiple associated districts.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithDistricts
```

## Usage

### Basic Implementation

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithDistricts;

class Regency extends Model
{
    use WithDistricts;
    
    protected $fillable = [
        'code',
        'name',
        'province_code'
    ];
}
```

## Features

- Provides `districts()` hasMany relationship
- Automatically loads all districts associated with the model

### Basic Usage

```php
$regency = Regency::with('districts')->first();

echo "Regency: {$regency->name}";
echo "Districts: {$regency->districts->count()}";

foreach ($regency->districts as $district) {
    echo "- {$district->name}";
}
```

## Common Use Cases

### Regional Administration

```php
class RegionalOffice extends Model
{
    use WithDistricts;
    
    protected $fillable = [
        'name',
        'office_type',
        'regency_code'
    ];
    
    public function getDistrictCountAttribute()
    {
        return $this->districts()->count();
    }
    
    public function getServiceAreaAttribute()
    {
        return $this->districts->pluck('name')->implode(', ');
    }
    
    public function scopeWithMinimumDistricts($query, $count)
    {
        return $query->has('districts', '>=', $count);
    }
}

// Usage
$offices = RegionalOffice::withMinimumDistricts(5)->get();
```

### Service Coverage

```php
class ServiceProvider extends Model
{
    use WithDistricts;
    
    protected $fillable = [
        'name',
        'service_type'
    ];
    
    public function addDistrict($districtCode)
    {
        $district = District::find($districtCode);
        
        if ($district) {
            return $this->districts()->save($district);
        }
        
        return false;
    }
    
    public function removeDistrict($districtCode)
    {
        return $this->districts()->where('code', $districtCode)->delete();
    }
    
    public function coversDistrict($districtCode)
    {
        return $this->districts()->where('code', $districtCode)->exists();
    }
    
    public function getCoverageStatsAttribute()
    {
        return [
            'districts' => $this->districts->count(),
            'regencies' => $this->districts->pluck('regency_code')->unique()->count(),
            'provinces' => $this->districts->pluck('province_code')->unique()->count()
        ];
    }
}
```

### Electoral Districts

```php
class ElectoralDistrict extends Model
{
    use WithDistricts;
    
    protected $fillable = [
        'name',
        'election_year',
        'representative_count'
    ];
    
    public function getVoterEstimateAttribute()
    {
        // Estimate based on districts
        return $this->districts->sum(function ($district) {
            return $district->villages()->count() * 1000; // Rough estimate
        });
    }
    
    public function getGeographicSpreadAttribute()
    {
        $regencies = $this->districts->groupBy('regency_code');
        
        return $regencies->map(function ($districts, $regencyCode) {
            return [
                'regency_code' => $regencyCode,
                'regency_name' => $districts->first()->regency->name,
                'district_count' => $districts->count(),
                'districts' => $districts->pluck('name')->toArray()
            ];
        })->values();
    }
}
```

## Performance Tips

### 1. Eager Loading

```php
// Good
$regencies = Regency::with('districts')->get();

// Bad - N+1 queries
$regencies = Regency::all();
foreach ($regencies as $regency) {
    echo $regency->districts->count(); // N+1 query
}
```

### 2. Counting Districts

```php
$regencies = Regency::withCount('districts')->get();

foreach ($regencies as $regency) {
    echo "{$regency->name}: {$regency->districts_count} districts";
}
```

## Related Documentation

- **[District Model](/api/models/district)** - Complete District model documentation
- **[WithDistrict Trait](/api/concerns/with-district)** - For single district associations
- **[WithVillages Trait](/api/concerns/with-villages)** - For multiple village relationships
- **[Models & Relationships](/guide/models)** - Understanding Laravel Nusa models
