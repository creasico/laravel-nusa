# WithRegency

Trait `WithRegency` memungkinkan model Anda memiliki relasi ke satu kabupaten/kota, memberikan akses ke data kabupaten/kota dan kemampuan untuk mengelompokkan data berdasarkan tingkat kabupaten/kota.

The `WithRegency` trait allows your model to have a relationship to a single regency/city, providing access to regency data and the ability to group data by regency level.

## Overview

The `WithRegency` trait is ideal for models that need to be associated with a specific regency or city but don't require village-level precision. This is common for branch offices, service centers, or regional operations that cover an entire regency/city area.

### What You Get

- **Regency relationship** - Direct access to regency/city data
- **Province access** - Access to parent province through regency
- **District and village access** - Access to all districts and villages within the regency
- **Geographic coordinates** - Access to regency center coordinates

## Basic Usage

### Adding the Trait

```php
use Creasi\Nusa\Models\Concerns\WithRegency;

class Branch extends Model
{
    use WithRegency;
    
    protected $fillable = [
        'name',
        'regency_code',
        'manager_name',
        'phone'
    ];
}
```

### Database Requirements

Your model's table must have a `regency_code` column:

```php
// Migration
Schema::table('branches', function (Blueprint $table) {
    $table->string('regency_code', 5)->nullable();
    $table->foreign('regency_code')->references('code')->on('nusa.regencies');
});
```

### Creating Records

```php
// Create branch for Semarang City
$branch = Branch::create([
    'name' => 'Semarang Branch',
    'regency_code' => '33.74',
    'manager_name' => 'John Doe',
    'phone' => '0247654321'
]);

// Access regency and province data
echo $branch->regency->name; // "Kota Semarang"
echo $branch->regency->province->name; // "Jawa Tengah"
```

## Accessing Regency Data

### Basic Regency Access

```php
$branch = Branch::with(['regency.province'])->first();

echo $branch->regency->name; // Regency name
echo $branch->regency->province->name; // Province name
echo $branch->regency->latitude; // Regency center latitude
echo $branch->regency->longitude; // Regency center longitude
```

### Accessing Sub-regions

```php
$branch = Branch::with(['regency.districts.villages'])->first();

// Access all districts in the regency
foreach ($branch->regency->districts as $district) {
    echo $district->name;
}

// Get coverage statistics
echo "Districts: " . $branch->regency->districts->count();
echo "Villages: " . $branch->regency->villages->count();
```

### Helper Methods

```php
class Branch extends Model
{
    use WithRegency;
    
    // Get full location display
    public function getFullLocationAttribute()
    {
        if ($this->regency) {
            return "{$this->regency->name}, {$this->regency->province->name}";
        }
        return null;
    }
    
    // Check if branch is in a city (not regency)
    public function isInCity()
    {
        return $this->regency && str_contains($this->regency->name, 'Kota');
    }
    
    // Get service area coverage
    public function getServiceAreaStats()
    {
        if (!$this->regency) {
            return null;
        }
        
        return [
            'regency' => $this->regency->name,
            'province' => $this->regency->province->name,
            'type' => $this->isInCity() ? 'City' : 'Regency',
            'districts' => $this->regency->districts->count(),
            'villages' => $this->regency->villages->count()
        ];
    }
}
```

## Querying with Regency Relationships

### Basic Queries

```php
// Get branches with their regencies
$branches = Branch::with(['regency.province'])->get();

// Get branches in specific regency
$branches = Branch::where('regency_code', '33.74')->get();

// Get branches in cities only
$cityBranches = Branch::whereHas('regency', function ($query) {
    $query->where('name', 'like', '%Kota%');
})->get();
```

### Advanced Filtering

```php
// Branches in specific province
$branches = Branch::whereHas('regency', function ($query) {
    $query->where('province_code', '33');
})->get();

// Branches in Java provinces
$javaBranches = Branch::whereHas('regency.province', function ($query) {
    $query->whereIn('code', ['31', '32', '33', '34', '35', '36']);
})->get();

// Branches in regencies with many districts
$largeBranches = Branch::whereHas('regency', function ($query) {
    $query->has('districts', '>=', 15);
})->get();
```

### Custom Scopes

```php
class Branch extends Model
{
    use WithRegency;
    
    // Scope for branches in cities
    public function scopeInCities($query)
    {
        return $query->whereHas('regency', function ($q) {
            $q->where('name', 'like', '%Kota%');
        });
    }
    
    // Scope for branches in regencies (not cities)
    public function scopeInRegencies($query)
    {
        return $query->whereHas('regency', function ($q) {
            $q->where('name', 'like', '%Kabupaten%');
        });
    }
    
    // Scope for branches in specific province
    public function scopeInProvince($query, $provinceCode)
    {
        return $query->whereHas('regency', function ($q) use ($provinceCode) {
            $q->where('province_code', $provinceCode);
        });
    }
    
    // Scope for branches in Java
    public function scopeInJava($query)
    {
        return $query->whereHas('regency.province', function ($q) {
            $q->whereIn('code', ['31', '32', '33', '34', '35', '36']);
        });
    }
}

// Usage
$cityBranches = Branch::inCities()->get();
$regencyBranches = Branch::inRegencies()->get();
$centralJavaBranches = Branch::inProvince('33')->get();
$javaBranches = Branch::inJava()->get();
```

## Business Applications

### Service Center Management

```php
class ServiceCenter extends Model
{
    use WithRegency;
    
    protected $fillable = ['name', 'regency_code', 'service_types'];
    
    // Get all customers in service area
    public function getCustomersInArea()
    {
        return Customer::whereHas('village', function ($query) {
            $query->where('regency_code', $this->regency_code);
        })->get();
    }
    
    // Calculate service coverage
    public function getCoverageReport()
    {
        $customers = $this->getCustomersInArea();
        $totalVillages = $this->regency->villages->count();
        $coveredVillages = $customers->pluck('village_code')->unique()->count();
        
        return [
            'service_center' => $this->name,
            'regency' => $this->regency->name,
            'province' => $this->regency->province->name,
            'total_customers' => $customers->count(),
            'total_villages' => $totalVillages,
            'covered_villages' => $coveredVillages,
            'coverage_percentage' => ($coveredVillages / $totalVillages) * 100
        ];
    }
}
```

### Distribution Center

```php
class DistributionCenter extends Model
{
    use WithRegency;
    
    protected $fillable = ['name', 'regency_code', 'capacity', 'warehouse_type'];
    
    // Get delivery zones within regency
    public function getDeliveryZones()
    {
        return $this->regency->districts->map(function ($district) {
            return [
                'district' => $district->name,
                'villages_count' => $district->villages->count(),
                'estimated_delivery_time' => $this->calculateDeliveryTime($district)
            ];
        });
    }
    
    // Calculate logistics efficiency
    public function getLogisticsMetrics()
    {
        return [
            'center' => $this->name,
            'coverage_area' => $this->regency->name,
            'districts_served' => $this->regency->districts->count(),
            'villages_served' => $this->regency->villages->count(),
            'capacity_utilization' => $this->calculateCapacityUtilization(),
            'average_delivery_distance' => $this->calculateAverageDeliveryDistance()
        ];
    }
}
```

## Geographic Analysis

### Distance Calculations

```php
class RegencyAnalyzer
{
    public function findNearestBranches($targetRegencyCode, $limit = 5)
    {
        $targetRegency = Regency::find($targetRegencyCode);
        
        if (!$targetRegency) {
            return collect();
        }
        
        return Branch::whereHas('regency', function ($query) use ($targetRegency) {
            $query->selectRaw("
                *,
                (6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )) AS distance
            ", [$targetRegency->latitude, $targetRegency->longitude, $targetRegency->latitude])
            ->orderBy('distance');
        })
        ->limit($limit)
        ->get();
    }
}
```

### Market Analysis

```php
class MarketAnalyzer
{
    public function getRegencyMarketData()
    {
        return Branch::join('nusa.regencies', 'branches.regency_code', '=', 'regencies.code')
            ->join('nusa.provinces', 'regencies.province_code', '=', 'provinces.code')
            ->groupBy('regencies.code', 'regencies.name', 'provinces.name')
            ->selectRaw('
                regencies.code,
                regencies.name as regency_name,
                provinces.name as province_name,
                count(branches.id) as branches_count,
                avg(branches.performance_score) as avg_performance
            ')
            ->orderBy('branches_count', 'desc')
            ->get();
    }
}
```

## Next Steps

- **[WithDistrict](/id/api/concerns/with-district)** - District-level relationships
- **[WithVillage](/id/api/concerns/with-village)** - Village-level relationships
- **[Regency Model](/id/api/models/regency)** - Complete regency model documentation
- **[WithProvince](/id/api/concerns/with-province)** - Province-level relationships
