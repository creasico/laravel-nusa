# WithVillages

Trait `WithVillages` memungkinkan model Anda memiliki relasi ke banyak kelurahan/desa, ideal untuk mengelola zona layanan yang sangat detail, cakupan area spesifik, atau operasional yang memerlukan presisi tingkat desa.

The `WithVillages` trait allows your model to have relationships to multiple villages, ideal for managing highly detailed service zones, specific area coverage, or operations requiring village-level precision.

## Overview

The `WithVillages` trait provides the most granular level of location management in Laravel Nusa. It's perfect for models that need to manage multiple villages, such as detailed delivery zones, micro-service areas, or any entity requiring precise village-level coverage.

### What You Get

- **Multiple villages relationship** - Many-to-many relationship with villages
- **Precise area management** - Village-level precision for coverage areas
- **Complete hierarchy access** - Access to districts, regencies, and provinces through villages
- **Postal code management** - Automatic postal code grouping and validation
- **Advanced geographic operations** - Detailed distance and coverage calculations

## Basic Usage

### Adding the Trait

```php
use Creasi\Nusa\Models\Concerns\WithVillages;

class MicroDeliveryZone extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'name',
        'description',
        'delivery_fee',
        'max_delivery_time'
    ];
}
```

### Database Requirements

You need a pivot table to connect your model with villages:

```php
// Migration for micro_delivery_zone_villages pivot table
Schema::create('micro_delivery_zone_villages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('micro_delivery_zone_id')->constrained()->onDelete('cascade');
    $table->string('village_code', 13);
    $table->foreign('village_code')->references('code')->on('nusa.villages');
    $table->timestamps();
    
    $table->unique(['micro_delivery_zone_id', 'village_code']);
});
```

### Creating and Managing Villages

```php
// Create micro delivery zone
$zone = MicroDeliveryZone::create([
    'name' => 'Central Semarang Micro Zone',
    'description' => 'Covers specific villages in central Semarang',
    'delivery_fee' => 8000,
    'max_delivery_time' => 30 // minutes
]);

// Add villages to the zone
$zone->villages()->attach([
    '33.74.01.1001', // Medono
    '33.74.01.1002', // Karangkidul
    '33.74.01.1003'  // Kembangsari
]);

// Access villages with complete hierarchy
$zone = MicroDeliveryZone::with(['villages.district.regency.province'])->first();

foreach ($zone->villages as $village) {
    echo "Village: {$village->name}";
    echo "District: {$village->district->name}";
    echo "Regency: {$village->regency->name}";
    echo "Province: {$village->province->name}";
    echo "Postal Code: {$village->postal_code}";
}
```

## Village Management

### Adding and Removing Villages

```php
// Add single village
$zone->villages()->attach('33.74.01.1004');

// Add multiple villages
$zone->villages()->attach([
    '33.74.01.1005',
    '33.74.01.1006',
    '33.74.02.1001'
]);

// Remove village
$zone->villages()->detach('33.74.01.1001');

// Remove multiple villages
$zone->villages()->detach(['33.74.01.1002', '33.74.01.1003']);

// Sync villages (replace all with new list)
$zone->villages()->sync([
    '33.74.01.1007',
    '33.74.01.1008',
    '33.74.02.1002'
]);

// Add villages with additional data
$zone->villages()->attach([
    '33.74.01.1009' => ['priority' => 'high', 'delivery_fee_override' => 10000],
    '33.74.01.1010' => ['priority' => 'medium', 'delivery_fee_override' => 8500]
]);
```

### Bulk Operations

```php
// Add all villages in a district
$zone->addVillagesByDistrict('33.74.01');

// Add villages by postal code
$zone->addVillagesByPostalCode('50132');

// Add villages by regency
$zone->addVillagesByRegency('33.74');
```

### Helper Methods

```php
class MicroDeliveryZone extends Model
{
    use WithVillages;
    
    // Add villages by district
    public function addVillagesByDistrict($districtCode)
    {
        $villages = Village::where('district_code', $districtCode)->pluck('code');
        $this->villages()->attach($villages);
        
        return $villages->count();
    }
    
    // Add villages by postal code
    public function addVillagesByPostalCode($postalCode)
    {
        $villages = Village::where('postal_code', $postalCode)->pluck('code');
        $this->villages()->attach($villages);
        
        return $villages->count();
    }
    
    // Add villages by regency
    public function addVillagesByRegency($regencyCode)
    {
        $villages = Village::where('regency_code', $regencyCode)->pluck('code');
        $this->villages()->attach($villages);
        
        return $villages->count();
    }
    
    // Get coverage statistics
    public function getCoverageStats()
    {
        $villages = $this->villages()->with(['district.regency.province'])->get();
        
        return [
            'zone_name' => $this->name,
            'villages_count' => $villages->count(),
            'districts_covered' => $villages->pluck('district.name')->unique()->values(),
            'regencies_covered' => $villages->pluck('regency.name')->unique()->values(),
            'provinces_covered' => $villages->pluck('province.name')->unique()->values(),
            'postal_codes' => $villages->pluck('postal_code')->unique()->sort()->values()
        ];
    }
    
    // Check if village is in zone
    public function coversVillage($villageCode)
    {
        return $this->villages()->where('code', $villageCode)->exists();
    }
    
    // Get zone boundaries
    public function getZoneBoundaries()
    {
        $villages = $this->villages;
        
        if ($villages->isEmpty()) {
            return null;
        }
        
        return [
            'north' => $villages->max('latitude'),
            'south' => $villages->min('latitude'),
            'east' => $villages->max('longitude'),
            'west' => $villages->min('longitude'),
            'center' => [
                'latitude' => $villages->avg('latitude'),
                'longitude' => $villages->avg('longitude')
            ]
        ];
    }
    
    // Get customers in zone
    public function getCustomersInZone()
    {
        $villageCodes = $this->villages->pluck('code');
        
        return Customer::whereIn('village_code', $villageCodes)->get();
    }
}
```

## Querying with Villages

### Basic Queries

```php
// Get zones with their villages
$zones = MicroDeliveryZone::with(['villages.district.regency.province'])->get();

// Get zones that cover specific village
$zones = MicroDeliveryZone::whereHas('villages', function ($query) {
    $query->where('code', '33.74.01.1001');
})->get();

// Get zones with many villages
$largeZones = MicroDeliveryZone::has('villages', '>=', 20)->get();
```

### Advanced Filtering

```php
// Zones covering villages in specific district
$zones = MicroDeliveryZone::whereHas('villages', function ($query) {
    $query->where('district_code', '33.74.01');
})->get();

// Zones covering villages in specific regency
$zones = MicroDeliveryZone::whereHas('villages', function ($query) {
    $query->where('regency_code', '33.74');
})->get();

// Zones covering villages with specific postal code
$zones = MicroDeliveryZone::whereHas('villages', function ($query) {
    $query->where('postal_code', '50132');
})->get();

// Zones covering villages in Java
$javaZones = MicroDeliveryZone::whereHas('villages', function ($query) {
    $query->whereIn('province_code', ['31', '32', '33', '34', '35', '36']);
})->get();
```

### Custom Scopes

```php
class MicroDeliveryZone extends Model
{
    use WithVillages;
    
    // Scope for zones covering specific district
    public function scopeCoveringDistrict($query, $districtCode)
    {
        return $query->whereHas('villages', function ($q) use ($districtCode) {
            $q->where('district_code', $districtCode);
        });
    }
    
    // Scope for zones covering specific postal code
    public function scopeCoveringPostalCode($query, $postalCode)
    {
        return $query->whereHas('villages', function ($q) use ($postalCode) {
            $q->where('postal_code', $postalCode);
        });
    }
    
    // Scope for large zones
    public function scopeLargeZones($query, $minVillages = 50)
    {
        return $query->has('villages', '>=', $minVillages);
    }
    
    // Scope for zones in urban areas
    public function scopeInUrbanAreas($query)
    {
        return $query->whereHas('villages.regency', function ($q) {
            $q->where('name', 'like', '%Kota%');
        });
    }
}

// Usage
$centralDistrictZones = MicroDeliveryZone::coveringDistrict('33.74.01')->get();
$postalZones = MicroDeliveryZone::coveringPostalCode('50132')->get();
$largeZones = MicroDeliveryZone::largeZones(100)->get();
$urbanZones = MicroDeliveryZone::inUrbanAreas()->get();
```

## Business Applications

### Last-Mile Delivery Management

```php
class LastMileDeliveryZone extends Model
{
    use WithVillages;
    
    protected $fillable = ['name', 'courier_id', 'vehicle_type', 'max_capacity'];
    
    // Get delivery performance metrics
    public function getDeliveryMetrics()
    {
        $villages = $this->villages()->with(['district.regency'])->get();
        $customers = $this->getCustomersInZone();
        $orders = $this->getOrdersInZone();
        
        return [
            'zone' => $this->name,
            'coverage' => [
                'villages' => $villages->count(),
                'districts' => $villages->pluck('district.name')->unique()->count(),
                'regencies' => $villages->pluck('regency.name')->unique()->count()
            ],
            'performance' => [
                'total_customers' => $customers->count(),
                'total_orders' => $orders->count(),
                'average_delivery_time' => $orders->avg('delivery_time_minutes'),
                'success_rate' => ($orders->where('status', 'delivered')->count() / $orders->count()) * 100
            ],
            'efficiency' => [
                'orders_per_village' => $orders->count() / $villages->count(),
                'customers_per_village' => $customers->count() / $villages->count(),
                'capacity_utilization' => $this->calculateCapacityUtilization()
            ]
        ];
    }
    
    // Optimize delivery routes
    public function optimizeDeliveryRoutes()
    {
        $villages = $this->villages()->with('addresses')->get();
        
        return $villages->map(function ($village) {
            $addresses = $village->addresses;
            
            return [
                'village' => $village->name,
                'postal_code' => $village->postal_code,
                'addresses_count' => $addresses->count(),
                'coordinates' => [
                    'latitude' => $village->latitude,
                    'longitude' => $village->longitude
                ],
                'delivery_priority' => $this->calculateDeliveryPriority($village, $addresses),
                'estimated_delivery_time' => $this->estimateDeliveryTime($village)
            ];
        })->sortByDesc('delivery_priority');
    }
}
```

### Micro-Service Area Management

```php
class MicroServiceArea extends Model
{
    use WithVillages;
    
    protected $fillable = ['name', 'service_type', 'technician_id'];
    
    // Get service coverage analysis
    public function getServiceCoverageAnalysis()
    {
        $villages = $this->villages()->with(['district.regency.province'])->get();
        $serviceRequests = $this->getServiceRequestsInArea();
        
        return [
            'area' => $this->name,
            'service_type' => $this->service_type,
            'coverage_details' => [
                'villages' => $villages->count(),
                'postal_codes' => $villages->pluck('postal_code')->unique()->count(),
                'geographic_spread' => $this->calculateGeographicSpread($villages)
            ],
            'service_metrics' => [
                'total_requests' => $serviceRequests->count(),
                'completed_requests' => $serviceRequests->where('status', 'completed')->count(),
                'average_response_time' => $serviceRequests->avg('response_time_hours'),
                'customer_satisfaction' => $serviceRequests->avg('satisfaction_score')
            ],
            'workload_distribution' => $this->analyzeWorkloadDistribution($villages, $serviceRequests)
        ];
    }
    
    // Find underserved villages
    public function findUnderservedVillages()
    {
        $villages = $this->villages;
        $serviceRequests = $this->getServiceRequestsInArea();
        
        return $villages->map(function ($village) use ($serviceRequests) {
            $villageRequests = $serviceRequests->where('village_code', $village->code);
            
            return [
                'village' => $village->name,
                'postal_code' => $village->postal_code,
                'requests_count' => $villageRequests->count(),
                'last_service_date' => $villageRequests->max('completed_at'),
                'service_gap_days' => $this->calculateServiceGap($villageRequests),
                'priority_score' => $this->calculateServicePriority($village, $villageRequests)
            ];
        })->where('service_gap_days', '>', 30)->sortByDesc('priority_score');
    }
}
```

## Geographic Analysis

### Detailed Coverage Analysis

```php
class VillageCoverageAnalyzer
{
    public function analyzeDetailedCoverage($zoneId)
    {
        $zone = MicroDeliveryZone::with(['villages.district.regency.province'])->find($zoneId);
        
        if (!$zone) {
            return null;
        }
        
        $villages = $zone->villages;
        $boundaries = $zone->getZoneBoundaries();
        
        return [
            'zone' => $zone->name,
            'detailed_coverage' => [
                'villages' => $villages->count(),
                'unique_postal_codes' => $villages->pluck('postal_code')->unique()->count(),
                'districts_spanned' => $villages->pluck('district_code')->unique()->count(),
                'regencies_spanned' => $villages->pluck('regency_code')->unique()->count(),
                'provinces_spanned' => $villages->pluck('province_code')->unique()->count()
            ],
            'geographic_analysis' => [
                'boundaries' => $boundaries,
                'area_estimation_km2' => $this->estimateAreaFromVillages($villages),
                'population_density' => $this->estimatePopulationDensity($villages),
                'village_density_per_km2' => $villages->count() / $this->estimateAreaFromVillages($villages)
            ],
            'postal_code_distribution' => $this->analyzePostalCodeDistribution($villages),
            'administrative_distribution' => $this->analyzeAdministrativeDistribution($villages)
        ];
    }
    
    private function analyzePostalCodeDistribution($villages)
    {
        return $villages->groupBy('postal_code')->map(function ($villageGroup, $postalCode) {
            return [
                'postal_code' => $postalCode,
                'villages_count' => $villageGroup->count(),
                'districts' => $villageGroup->pluck('district.name')->unique()->values(),
                'coordinates' => [
                    'center_lat' => $villageGroup->avg('latitude'),
                    'center_lng' => $villageGroup->avg('longitude')
                ]
            ];
        })->values();
    }
}
```

### Distance-based Village Clustering

```php
class VillageClusterAnalyzer
{
    public function clusterVillagesByDistance($centerCoordinates, $radiusKm = 5)
    {
        return Village::selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(latitude))
            )) AS distance
        ", [$centerCoordinates['lat'], $centerCoordinates['lng'], $centerCoordinates['lat']])
        ->having('distance', '<=', $radiusKm)
        ->with(['district.regency.province'])
        ->orderBy('distance')
        ->get()
        ->groupBy(function ($village) {
            // Create distance-based clusters
            if ($village->distance <= 1) return 'Core Zone (0-1km)';
            if ($village->distance <= 2.5) return 'Inner Zone (1-2.5km)';
            return 'Outer Zone (2.5-5km)';
        });
    }
    
    public function optimizeVillageZones($maxVillagesPerZone = 25, $maxDistanceKm = 10)
    {
        $allVillages = Village::with(['district.regency'])->get();
        $zones = [];
        $processedVillages = collect();
        
        foreach ($allVillages as $seedVillage) {
            if ($processedVillages->contains('code', $seedVillage->code)) {
                continue;
            }
            
            $nearbyVillages = $this->findNearbyVillages($seedVillage, $maxDistanceKm, $maxVillagesPerZone);
            
            if ($nearbyVillages->count() >= 5) { // Minimum viable zone size
                $zones[] = [
                    'seed_village' => $seedVillage->name,
                    'villages' => $nearbyVillages->pluck('name'),
                    'villages_count' => $nearbyVillages->count(),
                    'coverage_area' => $this->calculateCoverageArea($nearbyVillages),
                    'administrative_units' => [
                        'districts' => $nearbyVillages->pluck('district.name')->unique()->count(),
                        'regencies' => $nearbyVillages->pluck('regency.name')->unique()->count()
                    ]
                ];
                
                $processedVillages = $processedVillages->merge($nearbyVillages);
            }
        }
        
        return collect($zones);
    }
}
```

## Testing

### Villages Relationship Tests

```php
class MicroDeliveryZoneVillagesTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_zone_can_have_multiple_villages()
    {
        $zone = MicroDeliveryZone::factory()->create();
        $villages = Village::factory()->count(5)->create();
        
        $zone->villages()->attach($villages->pluck('code'));
        
        $this->assertCount(5, $zone->villages);
        $this->assertTrue($zone->villages->contains($villages->first()));
    }
    
    public function test_zone_can_add_villages_by_postal_code()
    {
        $zone = MicroDeliveryZone::factory()->create();
        $villages = Village::factory()->count(3)->create(['postal_code' => '50132']);
        
        $addedCount = $zone->addVillagesByPostalCode('50132');
        
        $this->assertEquals(3, $addedCount);
        $this->assertCount(3, $zone->villages);
    }
    
    public function test_zone_coverage_statistics()
    {
        $zone = MicroDeliveryZone::factory()->create(['name' => 'Test Zone']);
        $district = District::factory()->create();
        $villages = Village::factory()->count(10)->create([
            'district_code' => $district->code,
            'postal_code' => '50132'
        ]);
        
        $zone->villages()->attach($villages->pluck('code'));
        
        $stats = $zone->getCoverageStats();
        
        $this->assertEquals('Test Zone', $stats['zone_name']);
        $this->assertEquals(10, $stats['villages_count']);
        $this->assertContains('50132', $stats['postal_codes']);
    }
    
    public function test_zone_boundaries_calculation()
    {
        $zone = MicroDeliveryZone::factory()->create();
        $villages = Village::factory()->count(4)->create([
            'latitude' => [-6.1, -6.2, -6.3, -6.4],
            'longitude' => [106.8, 106.9, 107.0, 107.1]
        ]);
        
        $zone->villages()->attach($villages->pluck('code'));
        
        $boundaries = $zone->getZoneBoundaries();
        
        $this->assertEquals(-6.1, $boundaries['north']);
        $this->assertEquals(-6.4, $boundaries['south']);
        $this->assertEquals(107.1, $boundaries['east']);
        $this->assertEquals(106.8, $boundaries['west']);
    }
}
```

## Next Steps

- **[WithDistricts](/id/api/concerns/with-districts)** - Multiple districts relationships
- **[WithVillage](/id/api/concerns/with-village)** - Single village relationship
- **[Village Model](/id/api/models/village)** - Complete village model documentation
- **[Geographic Queries](/id/examples/geographic-queries)** - Advanced geographic operations
