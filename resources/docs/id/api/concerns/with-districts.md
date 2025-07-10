# WithDistricts

Trait `WithDistricts` memungkinkan model Anda memiliki relasi ke banyak kecamatan, ideal untuk mengelola zona layanan, wilayah operasional, atau cakupan area yang meliputi beberapa kecamatan.

The `WithDistricts` trait allows your model to have relationships to multiple districts, ideal for managing service zones, operational areas, or coverage areas that span multiple districts.

## Overview

The `WithDistricts` trait is perfect for models that need to manage multiple districts, such as delivery zones, service areas, sales territories, or any entity that operates across several district boundaries.

### What You Get

- **Multiple districts relationship** - Many-to-many relationship with districts
- **Flexible zone management** - Add/remove districts dynamically
- **Complete hierarchy access** - Access to regencies and provinces through districts
- **Coverage analysis** - Built-in methods for analyzing coverage areas
- **Geographic operations** - Distance and area calculations

## Basic Usage

### Adding the Trait

```php
use Creasi\Nusa\Models\Concerns\WithDistricts;

class DeliveryZone extends Model
{
    use WithDistricts;
    
    protected $fillable = [
        'name',
        'description',
        'max_delivery_time'
    ];
}
```

### Database Requirements

You need a pivot table to connect your model with districts:

```php
// Migration for delivery_zone_districts pivot table
Schema::create('delivery_zone_districts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('delivery_zone_id')->constrained()->onDelete('cascade');
    $table->string('district_code', 8);
    $table->foreign('district_code')->references('code')->on('nusa.districts');
    $table->timestamps();
    
    $table->unique(['delivery_zone_id', 'district_code']);
});
```

### Creating and Managing Districts

```php
// Create delivery zone
$zone = DeliveryZone::create([
    'name' => 'Central Semarang Zone',
    'description' => 'Covers central districts of Semarang',
    'max_delivery_time' => 60 // minutes
]);

// Add districts to the zone
$zone->districts()->attach([
    '33.74.01', // Semarang Tengah
    '33.74.02', // Semarang Utara
    '33.74.03'  // Semarang Timur
]);

// Access districts
foreach ($zone->districts as $district) {
    echo $district->name;
    echo $district->regency->name;
    echo $district->province->name;
}
```

## District Management

### Adding and Removing Districts

```php
// Add single district
$zone->districts()->attach('33.74.04');

// Add multiple districts
$zone->districts()->attach(['33.74.05', '33.74.06']);

// Remove district
$zone->districts()->detach('33.74.01');

// Remove multiple districts
$zone->districts()->detach(['33.74.02', '33.74.03']);

// Sync districts (replace all with new list)
$zone->districts()->sync(['33.74.01', '33.74.07', '33.74.08']);

// Add districts with additional data
$zone->districts()->attach([
    '33.74.09' => ['priority' => 'high', 'delivery_fee' => 15000],
    '33.74.10' => ['priority' => 'medium', 'delivery_fee' => 12000]
]);
```

### Accessing District Data

```php
// Get zone with districts and hierarchy
$zone = DeliveryZone::with(['districts.regency.province'])->first();

// Access district information
foreach ($zone->districts as $district) {
    echo "District: {$district->name}";
    echo "Regency: {$district->regency->name}";
    echo "Province: {$district->province->name}";
    
    // Access pivot data if available
    if ($district->pivot->priority) {
        echo "Priority: {$district->pivot->priority}";
        echo "Delivery Fee: {$district->pivot->delivery_fee}";
    }
}
```

### Helper Methods

```php
class DeliveryZone extends Model
{
    use WithDistricts;
    
    // Get coverage statistics
    public function getCoverageStats()
    {
        $districts = $this->districts()->with(['villages', 'regency.province'])->get();
        
        return [
            'zone_name' => $this->name,
            'districts_count' => $districts->count(),
            'villages_count' => $districts->sum(function ($district) {
                return $district->villages->count();
            }),
            'regencies_covered' => $districts->pluck('regency.name')->unique()->values(),
            'provinces_covered' => $districts->pluck('regency.province.name')->unique()->values()
        ];
    }
    
    // Check if district is in zone
    public function coversDistrict($districtCode)
    {
        return $this->districts()->where('code', $districtCode)->exists();
    }
    
    // Get all villages in the zone
    public function getAllVillages()
    {
        return Village::whereIn('district_code', $this->districts->pluck('code'))->get();
    }
    
    // Add districts by regency
    public function addDistrictsByRegency($regencyCode)
    {
        $districts = District::where('regency_code', $regencyCode)->pluck('code');
        $this->districts()->attach($districts);
        
        return $districts->count();
    }
    
    // Get zone boundaries (approximate)
    public function getZoneBoundaries()
    {
        $districts = $this->districts;
        
        if ($districts->isEmpty()) {
            return null;
        }
        
        return [
            'north' => $districts->max('latitude'),
            'south' => $districts->min('latitude'),
            'east' => $districts->max('longitude'),
            'west' => $districts->min('longitude'),
            'center' => [
                'latitude' => $districts->avg('latitude'),
                'longitude' => $districts->avg('longitude')
            ]
        ];
    }
}
```

## Querying with Districts

### Basic Queries

```php
// Get zones with their districts
$zones = DeliveryZone::with(['districts.regency.province'])->get();

// Get zones that cover specific district
$zones = DeliveryZone::whereHas('districts', function ($query) {
    $query->where('code', '33.74.01');
})->get();

// Get zones with many districts
$largeZones = DeliveryZone::has('districts', '>=', 5)->get();
```

### Advanced Filtering

```php
// Zones covering districts in specific regency
$zones = DeliveryZone::whereHas('districts', function ($query) {
    $query->where('regency_code', '33.74');
})->get();

// Zones covering districts in specific province
$zones = DeliveryZone::whereHas('districts', function ($query) {
    $query->where('province_code', '33');
})->get();

// Zones covering central districts
$centralZones = DeliveryZone::whereHas('districts', function ($query) {
    $query->where('name', 'like', '%Tengah%');
})->get();
```

### Custom Scopes

```php
class DeliveryZone extends Model
{
    use WithDistricts;
    
    // Scope for zones covering specific regency
    public function scopeCoveringRegency($query, $regencyCode)
    {
        return $query->whereHas('districts', function ($q) use ($regencyCode) {
            $q->where('regency_code', $regencyCode);
        });
    }
    
    // Scope for zones covering specific province
    public function scopeCoveringProvince($query, $provinceCode)
    {
        return $query->whereHas('districts', function ($q) use ($provinceCode) {
            $q->where('province_code', $provinceCode);
        });
    }
    
    // Scope for large zones
    public function scopeLargeZones($query, $minDistricts = 10)
    {
        return $query->has('districts', '>=', $minDistricts);
    }
    
    // Scope for zones in Java
    public function scopeInJava($query)
    {
        return $query->whereHas('districts', function ($q) {
            $q->whereIn('province_code', ['31', '32', '33', '34', '35', '36']);
        });
    }
}

// Usage
$semarangZones = DeliveryZone::coveringRegency('33.74')->get();
$centralJavaZones = DeliveryZone::coveringProvince('33')->get();
$largeZones = DeliveryZone::largeZones(15)->get();
$javaZones = DeliveryZone::inJava()->get();
```

## Business Applications

### Service Territory Management

```php
class ServiceTerritory extends Model
{
    use WithDistricts;
    
    protected $fillable = ['name', 'manager_id', 'target_revenue'];
    
    // Get customers in territory
    public function getCustomersInTerritory()
    {
        $districtCodes = $this->districts->pluck('code');
        
        return Customer::whereHas('village', function ($query) use ($districtCodes) {
            $query->whereIn('district_code', $districtCodes);
        })->get();
    }
    
    // Calculate territory performance
    public function getPerformanceMetrics()
    {
        $customers = $this->getCustomersInTerritory();
        $totalRevenue = $customers->sum('total_purchases');
        
        return [
            'territory' => $this->name,
            'districts_count' => $this->districts->count(),
            'customers_count' => $customers->count(),
            'total_revenue' => $totalRevenue,
            'target_revenue' => $this->target_revenue,
            'achievement_percentage' => ($totalRevenue / $this->target_revenue) * 100,
            'revenue_per_district' => $totalRevenue / $this->districts->count(),
            'customers_per_district' => $customers->count() / $this->districts->count()
        ];
    }
    
    // Optimize territory by balancing districts
    public function optimizeTerritory()
    {
        $districts = $this->districts()->with('villages')->get();
        
        return $districts->map(function ($district) {
            $customers = Customer::whereHas('village', function ($query) use ($district) {
                $query->where('district_code', $district->code);
            })->get();
            
            return [
                'district' => $district->name,
                'villages_count' => $district->villages->count(),
                'customers_count' => $customers->count(),
                'revenue' => $customers->sum('total_purchases'),
                'potential_score' => $this->calculatePotentialScore($district, $customers)
            ];
        })->sortByDesc('potential_score');
    }
}
```

### Logistics Hub Coverage

```php
class LogisticsHub extends Model
{
    use WithDistricts;
    
    protected $fillable = ['name', 'capacity', 'hub_type'];
    
    // Calculate delivery efficiency
    public function getDeliveryEfficiency()
    {
        $districts = $this->districts()->with(['villages'])->get();
        $totalVillages = $districts->sum(function ($district) {
            return $district->villages->count();
        });
        
        return [
            'hub' => $this->name,
            'coverage_area' => [
                'districts' => $districts->count(),
                'villages' => $totalVillages,
                'regencies' => $districts->pluck('regency_code')->unique()->count()
            ],
            'efficiency_metrics' => [
                'villages_per_district' => $totalVillages / $districts->count(),
                'capacity_utilization' => $this->calculateCapacityUtilization(),
                'average_delivery_distance' => $this->calculateAverageDeliveryDistance()
            ]
        ];
    }
    
    // Find optimal districts to add
    public function findOptimalExpansionDistricts($limit = 5)
    {
        $currentDistrictCodes = $this->districts->pluck('code');
        $currentRegencies = $this->districts->pluck('regency_code')->unique();
        
        // Find nearby districts not yet covered
        return District::whereNotIn('code', $currentDistrictCodes)
            ->whereIn('regency_code', $currentRegencies)
            ->with(['villages', 'regency'])
            ->get()
            ->map(function ($district) {
                return [
                    'district' => $district->name,
                    'regency' => $district->regency->name,
                    'villages_count' => $district->villages->count(),
                    'expansion_score' => $this->calculateExpansionScore($district)
                ];
            })
            ->sortByDesc('expansion_score')
            ->take($limit);
    }
}
```

## Geographic Analysis

### Coverage Area Calculation

```php
class CoverageAnalyzer
{
    public function analyzeTerritorialCoverage($zoneId)
    {
        $zone = DeliveryZone::with(['districts.villages'])->find($zoneId);
        
        if (!$zone) {
            return null;
        }
        
        $allVillages = $zone->getAllVillages();
        $boundaries = $zone->getZoneBoundaries();
        
        return [
            'zone' => $zone->name,
            'coverage_summary' => [
                'districts' => $zone->districts->count(),
                'villages' => $allVillages->count(),
                'postal_codes' => $allVillages->pluck('postal_code')->unique()->count()
            ],
            'geographic_bounds' => $boundaries,
            'coverage_density' => $this->calculateCoverageDensity($zone),
            'service_gaps' => $this->identifyServiceGaps($zone)
        ];
    }
    
    private function calculateCoverageDensity($zone)
    {
        $districts = $zone->districts;
        $totalArea = $this->estimateAreaFromBounds($zone->getZoneBoundaries());
        
        return [
            'districts_per_km2' => $districts->count() / $totalArea,
            'villages_per_km2' => $zone->getAllVillages()->count() / $totalArea,
            'estimated_area_km2' => $totalArea
        ];
    }
}
```

### Distance-based Optimization

```php
class ZoneOptimizer
{
    public function optimizeZonesByDistance($hubCoordinates, $maxDistanceKm = 50)
    {
        return District::selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(latitude))
            )) AS distance
        ", [$hubCoordinates['lat'], $hubCoordinates['lng'], $hubCoordinates['lat']])
        ->having('distance', '<=', $maxDistanceKm)
        ->with(['villages', 'regency.province'])
        ->orderBy('distance')
        ->get()
        ->groupBy(function ($district) {
            // Group by distance ranges
            if ($district->distance <= 15) return 'Zone A (0-15km)';
            if ($district->distance <= 30) return 'Zone B (15-30km)';
            return 'Zone C (30-50km)';
        });
    }
}
```

## Testing

### Districts Relationship Tests

```php
class DeliveryZoneDistrictsTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_zone_can_have_multiple_districts()
    {
        $zone = DeliveryZone::factory()->create();
        $districts = District::factory()->count(3)->create();
        
        $zone->districts()->attach($districts->pluck('code'));
        
        $this->assertCount(3, $zone->districts);
        $this->assertTrue($zone->districts->contains($districts->first()));
    }
    
    public function test_zone_coverage_statistics()
    {
        $zone = DeliveryZone::factory()->create(['name' => 'Test Zone']);
        $district = District::factory()->create();
        $villages = Village::factory()->count(5)->create(['district_code' => $district->code]);
        
        $zone->districts()->attach($district->code);
        
        $stats = $zone->getCoverageStats();
        
        $this->assertEquals('Test Zone', $stats['zone_name']);
        $this->assertEquals(1, $stats['districts_count']);
        $this->assertEquals(5, $stats['villages_count']);
    }
    
    public function test_zone_can_check_district_coverage()
    {
        $zone = DeliveryZone::factory()->create();
        $district = District::factory()->create();
        
        $this->assertFalse($zone->coversDistrict($district->code));
        
        $zone->districts()->attach($district->code);
        
        $this->assertTrue($zone->coversDistrict($district->code));
    }
}
```

## Next Steps

- **[WithVillages](/id/api/concerns/with-villages)** - Multiple villages relationships
- **[WithDistrict](/id/api/concerns/with-district)** - Single district relationship
- **[District Model](/id/api/models/district)** - Complete district model documentation
- **[Geographic Queries](/id/examples/geographic-queries)** - Advanced geographic operations
