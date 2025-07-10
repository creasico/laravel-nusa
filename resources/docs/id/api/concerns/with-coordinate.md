# WithCoordinate

Trait `WithCoordinate` memungkinkan model Anda memiliki koordinat geografis (latitude dan longitude) dengan berbagai metode untuk kalkulasi jarak, pencarian berdasarkan lokasi, dan operasi geografis lainnya.

The `WithCoordinate` trait allows your model to have geographic coordinates (latitude and longitude) with various methods for distance calculations, location-based searches, and other geographic operations.

## Overview

The `WithCoordinate` trait is essential for models that need precise geographic positioning and location-based functionality. It provides powerful methods for distance calculations, proximity searches, and geographic analysis.

### What You Get

- **Geographic coordinates** - Latitude and longitude storage
- **Distance calculations** - Calculate distances between points
- **Proximity searches** - Find nearby entities within a radius
- **Bounding box queries** - Search within geographic boundaries
- **Geographic utilities** - Various helper methods for location operations

## Basic Usage

### Adding the Trait

```php
use Creasi\Nusa\Models\Concerns\WithCoordinate;

class Store extends Model
{
    use WithCoordinate;
    
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'address'
    ];
    
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];
}
```

### Database Requirements

Your model's table must have `latitude` and `longitude` columns:

```php
// Migration
Schema::table('stores', function (Blueprint $table) {
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    
    // Optional: Add spatial index for better performance
    $table->spatialIndex(['latitude', 'longitude']);
});
```

### Creating Records with Coordinates

```php
// Create store with coordinates
$store = Store::create([
    'name' => 'Central Store',
    'latitude' => -6.2088,
    'longitude' => 106.8456,
    'address' => 'Jl. Sudirman No. 123'
]);

// Jakarta coordinates: -6.2088, 106.8456
// Semarang coordinates: -6.9667, 110.4167
// Surabaya coordinates: -7.2575, 112.7521
```

## Distance Calculations

### Calculate Distance Between Points

```php
// Calculate distance to another store
$store1 = Store::find(1);
$store2 = Store::find(2);

$distance = $store1->distanceTo($store2);
echo "Distance: {$distance} km";

// Calculate distance to specific coordinates
$distance = $store1->distanceToCoordinates(-6.9667, 110.4167);
echo "Distance to Semarang: {$distance} km";
```

### Distance Calculation Methods

```php
class Store extends Model
{
    use WithCoordinate;
    
    // Get distance to another model with coordinates
    public function distanceTo($otherModel)
    {
        if (!$this->hasCoordinates() || !$otherModel->hasCoordinates()) {
            return null;
        }
        
        return $this->distanceToCoordinates($otherModel->latitude, $otherModel->longitude);
    }
    
    // Get distance to specific coordinates
    public function distanceToCoordinates($latitude, $longitude)
    {
        if (!$this->hasCoordinates()) {
            return null;
        }
        
        $earthRadius = 6371; // km
        
        $lat1 = deg2rad($this->latitude);
        $lng1 = deg2rad($this->longitude);
        $lat2 = deg2rad($latitude);
        $lng2 = deg2rad($longitude);
        
        $deltaLat = $lat2 - $lat1;
        $deltaLng = $lng2 - $lng1;
        
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    // Check if model has valid coordinates
    public function hasCoordinates()
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
```

## Proximity Searches

### Find Nearby Entities

```php
// Find stores within 10km radius
$nearbyStores = Store::nearby(-6.2088, 106.8456, 10)->get();

// Find stores within 5km radius, ordered by distance
$nearbyStores = Store::nearby(-6.2088, 106.8456, 5)
    ->orderBy('distance')
    ->get();

// Find nearest 5 stores
$nearestStores = Store::nearest(-6.2088, 106.8456, 5)->get();
```

### Proximity Scope Methods

```php
class Store extends Model
{
    use WithCoordinate;
    
    // Scope for finding nearby entities
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
        ->having('distance', '<=', $radiusKm);
    }
    
    // Scope for finding nearest entities
    public function scopeNearest($query, $latitude, $longitude, $limit = 10)
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
        ->orderBy('distance')
        ->limit($limit);
    }
    
    // Scope for entities within bounding box
    public function scopeWithinBounds($query, $northEast, $southWest)
    {
        return $query->whereBetween('latitude', [$southWest['lat'], $northEast['lat']])
            ->whereBetween('longitude', [$southWest['lng'], $northEast['lng']]);
    }
}
```

## Geographic Utilities

### Bounding Box Calculations

```php
class Store extends Model
{
    use WithCoordinate;
    
    // Get bounding box for a radius around the point
    public function getBoundingBox($radiusKm)
    {
        if (!$this->hasCoordinates()) {
            return null;
        }
        
        $earthRadius = 6371; // km
        $lat = deg2rad($this->latitude);
        $lng = deg2rad($this->longitude);
        
        $deltaLat = $radiusKm / $earthRadius;
        $deltaLng = $radiusKm / ($earthRadius * cos($lat));
        
        return [
            'north' => rad2deg($lat + $deltaLat),
            'south' => rad2deg($lat - $deltaLat),
            'east' => rad2deg($lng + $deltaLng),
            'west' => rad2deg($lng - $deltaLng)
        ];
    }
    
    // Check if point is within radius
    public function isWithinRadius($latitude, $longitude, $radiusKm)
    {
        $distance = $this->distanceToCoordinates($latitude, $longitude);
        return $distance !== null && $distance <= $radiusKm;
    }
    
    // Get center point of multiple stores
    public static function getCenterPoint($stores)
    {
        $storesWithCoords = $stores->filter(function ($store) {
            return $store->hasCoordinates();
        });
        
        if ($storesWithCoords->isEmpty()) {
            return null;
        }
        
        return [
            'latitude' => $storesWithCoords->avg('latitude'),
            'longitude' => $storesWithCoords->avg('longitude')
        ];
    }
}
```

### Geographic Analysis

```php
class GeographicAnalyzer
{
    public function analyzeStoreDistribution()
    {
        $stores = Store::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        
        if ($stores->isEmpty()) {
            return null;
        }
        
        $centerPoint = Store::getCenterPoint($stores);
        $distances = $stores->map(function ($store) use ($centerPoint) {
            return $store->distanceToCoordinates($centerPoint['latitude'], $centerPoint['longitude']);
        });
        
        return [
            'total_stores' => $stores->count(),
            'center_point' => $centerPoint,
            'distribution_stats' => [
                'average_distance_from_center' => $distances->avg(),
                'max_distance_from_center' => $distances->max(),
                'min_distance_from_center' => $distances->min(),
                'standard_deviation' => $this->calculateStandardDeviation($distances)
            ],
            'coverage_area' => $this->calculateCoverageArea($stores)
        ];
    }
    
    public function findOptimalNewLocation($existingStores, $targetCustomers)
    {
        $customerClusters = $this->clusterCustomers($targetCustomers);
        
        return $customerClusters->map(function ($cluster) use ($existingStores) {
            $clusterCenter = [
                'latitude' => $cluster->avg('latitude'),
                'longitude' => $cluster->avg('longitude')
            ];
            
            $nearestStore = $existingStores->sortBy(function ($store) use ($clusterCenter) {
                return $store->distanceToCoordinates($clusterCenter['latitude'], $clusterCenter['longitude']);
            })->first();
            
            return [
                'cluster_center' => $clusterCenter,
                'customers_count' => $cluster->count(),
                'nearest_existing_store' => $nearestStore?->name,
                'distance_to_nearest_store' => $nearestStore?->distanceToCoordinates($clusterCenter['latitude'], $clusterCenter['longitude']),
                'opportunity_score' => $this->calculateOpportunityScore($cluster, $nearestStore)
            ];
        })->sortByDesc('opportunity_score');
    }
}
```

## Business Applications

### Store Locator Service

```php
class StoreLocatorService
{
    public function findNearestStores($customerLat, $customerLng, $maxDistance = 25)
    {
        return Store::nearby($customerLat, $customerLng, $maxDistance)
            ->with(['address', 'services'])
            ->get()
            ->map(function ($store) use ($customerLat, $customerLng) {
                return [
                    'store' => [
                        'id' => $store->id,
                        'name' => $store->name,
                        'address' => $store->address,
                        'phone' => $store->phone
                    ],
                    'location' => [
                        'latitude' => $store->latitude,
                        'longitude' => $store->longitude,
                        'distance_km' => round($store->distance, 2)
                    ],
                    'services' => $store->services->pluck('name'),
                    'estimated_travel_time' => $this->estimateTravelTime($store->distance)
                ];
            });
    }
    
    public function getStoresByRegion($bounds)
    {
        return Store::withinBounds($bounds['northEast'], $bounds['southWest'])
            ->with(['address'])
            ->get()
            ->groupBy(function ($store) {
                // Group by approximate regions
                $lat = round($store->latitude, 1);
                $lng = round($store->longitude, 1);
                return "{$lat},{$lng}";
            });
    }
}
```

### Delivery Zone Optimization

```php
class DeliveryZoneOptimizer
{
    public function optimizeDeliveryZones($stores, $maxDeliveryRadius = 15)
    {
        return $stores->map(function ($store) use ($maxDeliveryRadius) {
            $deliveryArea = $store->getBoundingBox($maxDeliveryRadius);
            $nearbyCustomers = Customer::withinBounds(
                ['lat' => $deliveryArea['north'], 'lng' => $deliveryArea['east']],
                ['lat' => $deliveryArea['south'], 'lng' => $deliveryArea['west']]
            )->get();
            
            return [
                'store' => $store->name,
                'coordinates' => [
                    'latitude' => $store->latitude,
                    'longitude' => $store->longitude
                ],
                'delivery_area' => $deliveryArea,
                'potential_customers' => $nearbyCustomers->count(),
                'coverage_efficiency' => $this->calculateCoverageEfficiency($store, $nearbyCustomers),
                'recommended_adjustments' => $this->getRecommendedAdjustments($store, $nearbyCustomers)
            ];
        });
    }
    
    public function findDeliveryGaps($stores, $customers, $maxDeliveryRadius = 15)
    {
        $uncoveredCustomers = $customers->filter(function ($customer) use ($stores, $maxDeliveryRadius) {
            return !$stores->contains(function ($store) use ($customer, $maxDeliveryRadius) {
                return $store->isWithinRadius($customer->latitude, $customer->longitude, $maxDeliveryRadius);
            });
        });
        
        return $uncoveredCustomers->map(function ($customer) use ($stores) {
            $nearestStore = $stores->sortBy(function ($store) use ($customer) {
                return $store->distanceToCoordinates($customer->latitude, $customer->longitude);
            })->first();
            
            return [
                'customer' => $customer->name,
                'coordinates' => [
                    'latitude' => $customer->latitude,
                    'longitude' => $customer->longitude
                ],
                'nearest_store' => $nearestStore?->name,
                'distance_to_nearest_store' => $nearestStore?->distanceToCoordinates($customer->latitude, $customer->longitude),
                'gap_severity' => $this->calculateGapSeverity($customer, $nearestStore)
            ];
        });
    }
}
```

## Performance Optimization

### Spatial Indexing

```php
// Add spatial index in migration
Schema::table('stores', function (Blueprint $table) {
    $table->spatialIndex(['latitude', 'longitude']);
});

// Use spatial queries for better performance (MySQL 5.7+)
class Store extends Model
{
    use WithCoordinate;
    
    public function scopeNearbyOptimized($query, $latitude, $longitude, $radiusKm)
    {
        // Use spatial functions for better performance
        return $query->selectRaw("
            *,
            ST_Distance_Sphere(
                POINT(longitude, latitude),
                POINT(?, ?)
            ) / 1000 AS distance
        ", [$longitude, $latitude])
        ->having('distance', '<=', $radiusKm);
    }
}
```

### Caching Geographic Queries

```php
class CachedLocationService
{
    public function getNearbyStores($lat, $lng, $radius, $cacheMinutes = 30)
    {
        $cacheKey = "nearby_stores_{$lat}_{$lng}_{$radius}";
        
        return Cache::remember($cacheKey, $cacheMinutes, function () use ($lat, $lng, $radius) {
            return Store::nearby($lat, $lng, $radius)->get();
        });
    }
    
    public function getStoreDistances($storeId, $cacheMinutes = 60)
    {
        $cacheKey = "store_distances_{$storeId}";
        
        return Cache::remember($cacheKey, $cacheMinutes, function () use ($storeId) {
            $store = Store::find($storeId);
            $otherStores = Store::where('id', '!=', $storeId)->get();
            
            return $otherStores->map(function ($otherStore) use ($store) {
                return [
                    'store_id' => $otherStore->id,
                    'store_name' => $otherStore->name,
                    'distance_km' => $store->distanceTo($otherStore)
                ];
            })->sortBy('distance_km');
        });
    }
}
```

## Testing

### Coordinate Tests

```php
class StoreCoordinateTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_store_can_calculate_distance_to_another_store()
    {
        $store1 = Store::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456
        ]);
        
        $store2 = Store::factory()->create([
            'latitude' => -6.9667,
            'longitude' => 110.4167
        ]);
        
        $distance = $store1->distanceTo($store2);
        
        $this->assertIsFloat($distance);
        $this->assertGreaterThan(0, $distance);
        $this->assertLessThan(500, $distance); // Should be less than 500km
    }
    
    public function test_nearby_scope_returns_stores_within_radius()
    {
        Store::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456
        ]);
        
        Store::factory()->create([
            'latitude' => -6.2100,
            'longitude' => 106.8500
        ]);
        
        Store::factory()->create([
            'latitude' => -7.0000,
            'longitude' => 110.0000
        ]);
        
        $nearbyStores = Store::nearby(-6.2088, 106.8456, 10)->get();
        
        $this->assertCount(2, $nearbyStores);
    }
    
    public function test_bounding_box_calculation()
    {
        $store = Store::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456
        ]);
        
        $boundingBox = $store->getBoundingBox(10);
        
        $this->assertArrayHasKey('north', $boundingBox);
        $this->assertArrayHasKey('south', $boundingBox);
        $this->assertArrayHasKey('east', $boundingBox);
        $this->assertArrayHasKey('west', $boundingBox);
        
        $this->assertGreaterThan($store->latitude, $boundingBox['north']);
        $this->assertLessThan($store->latitude, $boundingBox['south']);
    }
}
```

## Next Steps

- **[WithVillage](/id/api/concerns/with-village)** - Village relationship with coordinates
- **[WithAddress](/id/api/concerns/with-address)** - Address management with coordinates
- **[Geographic Queries](/id/examples/geographic-queries)** - Advanced geographic operations
- **[Custom Models](/id/examples/custom-models)** - Combining coordinates with location models
