# WithCoordinate Trait

The `WithCoordinate` trait adds latitude and longitude coordinate functionality to your models, enabling geographic features and location-based queries.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithCoordinate
```

## Usage

### Basic Implementation

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithCoordinate;

class Store extends Model
{
    use WithCoordinate;
    
    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude'
    ];
}
```

### Database Migration

```php
Schema::create('stores', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->timestamps();
    
    // Optional: Add spatial index for better performance
    $table->spatialIndex(['latitude', 'longitude']);
});
```

## Features

### Automatic Configuration

The trait automatically:
- Casts `latitude` and `longitude` to `float`
- Adds both fields to the `$fillable` array

```php
// Automatically configured
protected $casts = [
    'latitude' => 'float',
    'longitude' => 'float'
];

protected $fillable = ['latitude', 'longitude'];
```

### Basic Coordinate Usage

```php
$store = Store::create([
    'name' => 'Main Store',
    'latitude' => -6.2088,
    'longitude' => 106.8456
]);

echo "Store location: {$store->latitude}, {$store->longitude}";
```

## Common Use Cases

### 1. Store Locator

```php
class Store extends Model
{
    use WithCoordinate;
    
    protected $fillable = [
        'name',
        'address',
        'phone',
        'latitude',
        'longitude'
    ];
    
    public function scopeNearby($query, $latitude, $longitude, $radiusKm = 10)
    {
        return $query->selectRaw('*, (
            6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))
            )
        ) AS distance', [$latitude, $longitude, $latitude])
        ->having('distance', '<', $radiusKm)
        ->orderBy('distance');
    }
    
    public function getDistanceFromAttribute()
    {
        return $this->attributes['distance'] ?? null;
    }
    
    public function distanceTo($latitude, $longitude)
    {
        $earthRadius = 6371; // km
        
        $latDelta = deg2rad($latitude - $this->latitude);
        $lonDelta = deg2rad($longitude - $this->longitude);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}

// Usage
$userLat = -6.2088;
$userLng = 106.8456;

$nearbyStores = Store::nearby($userLat, $userLng, 5)->get();

foreach ($nearbyStores as $store) {
    echo "{$store->name} - {$store->distance_from} km away";
}
```

### 2. Event Locations

```php
class Event extends Model
{
    use WithCoordinate;
    
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'venue_name',
        'latitude',
        'longitude'
    ];
    
    protected $casts = [
        'event_date' => 'datetime'
    ];
    
    public function scopeInBounds($query, $northLat, $southLat, $eastLng, $westLng)
    {
        return $query->whereBetween('latitude', [$southLat, $northLat])
                    ->whereBetween('longitude', [$westLng, $eastLng]);
    }
    
    public function getMapUrlAttribute()
    {
        if (!$this->latitude || !$this->longitude) return null;
        
        return "https://maps.google.com/maps?q={$this->latitude},{$this->longitude}";
    }
    
    public function getLocationDescriptionAttribute()
    {
        if (!$this->latitude || !$this->longitude) {
            return $this->venue_name ?? 'Location TBA';
        }
        
        return $this->venue_name . " ({$this->latitude}, {$this->longitude})";
    }
}

// Usage
$events = Event::inBounds(-6.0, -6.5, 107.0, 106.5)->get(); // Jakarta area
```

### 3. Delivery Tracking

```php
class DeliveryPoint extends Model
{
    use WithCoordinate;
    
    protected $fillable = [
        'order_id',
        'address',
        'latitude',
        'longitude',
        'status'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function getOptimalRoute(array $destinations)
    {
        // Simple nearest neighbor algorithm
        $route = [$this];
        $remaining = collect($destinations);
        $current = $this;
        
        while ($remaining->isNotEmpty()) {
            $nearest = $remaining->sortBy(function ($destination) use ($current) {
                return $current->distanceTo($destination->latitude, $destination->longitude);
            })->first();
            
            $route[] = $nearest;
            $remaining = $remaining->reject(fn($item) => $item->id === $nearest->id);
            $current = $nearest;
        }
        
        return $route;
    }
}
```

### 4. Geographic Analytics

```php
class CustomerLocation extends Model
{
    use WithCoordinate;
    
    protected $fillable = [
        'customer_id',
        'latitude',
        'longitude',
        'recorded_at'
    ];
    
    protected $casts = [
        'recorded_at' => 'datetime'
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public static function getHeatmapData($bounds = null)
    {
        $query = static::select('latitude', 'longitude', 
                               \DB::raw('COUNT(*) as intensity'));
        
        if ($bounds) {
            $query->inBounds(
                $bounds['north'], $bounds['south'],
                $bounds['east'], $bounds['west']
            );
        }
        
        return $query->groupBy('latitude', 'longitude')
                    ->having('intensity', '>', 1)
                    ->get();
    }
    
    public static function getClusterCenters($k = 5)
    {
        // Simple k-means clustering
        $points = static::select('latitude', 'longitude')->get();
        
        if ($points->count() < $k) {
            return $points;
        }
        
        // Initialize random centers
        $centers = $points->random($k);
        
        for ($iteration = 0; $iteration < 10; $iteration++) {
            $clusters = collect();
            
            // Assign points to nearest center
            foreach ($points as $point) {
                $nearestCenter = $centers->sortBy(function ($center) use ($point) {
                    return sqrt(
                        pow($point->latitude - $center->latitude, 2) +
                        pow($point->longitude - $center->longitude, 2)
                    );
                })->first();
                
                $clusters->push([
                    'point' => $point,
                    'center' => $nearestCenter
                ]);
            }
            
            // Update centers
            $newCenters = collect();
            foreach ($centers as $center) {
                $clusterPoints = $clusters->where('center.id', $center->id)
                                         ->pluck('point');
                
                if ($clusterPoints->isNotEmpty()) {
                    $newCenters->push((object)[
                        'latitude' => $clusterPoints->avg('latitude'),
                        'longitude' => $clusterPoints->avg('longitude')
                    ]);
                }
            }
            
            $centers = $newCenters;
        }
        
        return $centers;
    }
}
```

## Advanced Geographic Queries

### Bounding Box Queries

```php
class LocationService
{
    public static function findWithinBounds($model, $bounds)
    {
        return $model::whereBetween('latitude', [$bounds['south'], $bounds['north']])
                    ->whereBetween('longitude', [$bounds['west'], $bounds['east']])
                    ->get();
    }
    
    public static function findNearPoint($model, $lat, $lng, $radiusKm)
    {
        return $model::selectRaw('*, (
            6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))
            )
        ) AS distance', [$lat, $lng, $lat])
        ->having('distance', '<', $radiusKm)
        ->orderBy('distance')
        ->get();
    }
}
```

### Polygon Containment

```php
class GeofenceService
{
    public static function isPointInPolygon($lat, $lng, array $polygon)
    {
        $x = $lng;
        $y = $lat;
        $inside = false;
        
        $j = count($polygon) - 1;
        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];
            
            if ((($yi > $y) !== ($yj > $y)) && 
                ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }
        
        return $inside;
    }
    
    public static function findInGeofence($model, array $polygon)
    {
        return $model::get()->filter(function ($item) use ($polygon) {
            return static::isPointInPolygon(
                $item->latitude, 
                $item->longitude, 
                $polygon
            );
        });
    }
}
```

## Validation

### Coordinate Validation

```php
class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ];
    }
    
    public function messages()
    {
        return [
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.'
        ];
    }
}
```

### Indonesia Bounds Validation

```php
class IndonesiaLocationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'latitude' => [
                'required',
                'numeric',
                'between:-11,6' // Indonesia latitude bounds
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:95,141' // Indonesia longitude bounds
            ]
        ];
    }
}
```

## Performance Tips

### 1. Spatial Indexing

```php
// In migration
Schema::table('stores', function (Blueprint $table) {
    $table->spatialIndex(['latitude', 'longitude']);
});
```

### 2. Bounding Box Pre-filtering

```php
// More efficient for large datasets
public function scopeNearbyOptimized($query, $lat, $lng, $radiusKm)
{
    $latDelta = $radiusKm / 111; // Rough km to degree conversion
    $lngDelta = $radiusKm / (111 * cos(deg2rad($lat)));
    
    return $query->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
                ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta])
                ->selectRaw('*, (
                    6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) * 
                        cos(radians(longitude) - radians(?)) + 
                        sin(radians(?)) * sin(radians(latitude))
                    )
                ) AS distance', [$lat, $lng, $lat])
                ->having('distance', '<', $radiusKm)
                ->orderBy('distance');
}
```

## Related Documentation

- **[Geographic Queries Example](/examples/geographic-queries)** - Advanced geographic query examples
- **[WithAddress Trait](/api/concerns/with-address)** - For address-based locations
- **[Models & Relationships](/guide/models)** - Understanding Laravel Nusa models
