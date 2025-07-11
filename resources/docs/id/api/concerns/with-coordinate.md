# Trait WithCoordinate

Trait `WithCoordinate` menambahkan fungsionalitas koordinat lintang dan bujur ke model Anda, memungkinkan fitur geografis dan kueri berbasis lokasi.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithCoordinate
```

## Penggunaan

### Implementasi Dasar

```php
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

### Migrasi Database

```php
Schema::create('stores', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->timestamps();
    
    // Opsional: Tambahkan indeks spasial untuk kinerja yang lebih baik
    $table->spatialIndex(['latitude', 'longitude']);
});
```

## Fitur

### Konfigurasi Otomatis

Trait ini secara otomatis:
- Melakukan *casting* `latitude` dan `longitude` ke `float`
- Menambahkan kedua kolom ke *array* `$fillable`

```php
// Dikonfigurasi secara otomatis
protected $casts = [
    'latitude' => 'float',
    'longitude' => 'float'
];

protected $fillable = ['latitude', 'longitude'];
```

### Penggunaan Koordinat Dasar

```php
$store = Store::create([
    'name' => 'Main Store',
    'latitude' => -6.2088,
    'longitude' => 106.8456
]);

echo "Lokasi toko: {$store->latitude}, {$store->longitude}";
```

## Contoh Penggunaan Umum

### 1. Pencari Toko

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

// Penggunaan
$userLat = -6.2088;
$userLng = 106.8456;

$nearbyStores = Store::nearby($userLat, $userLng, 5)->get();

foreach ($nearbyStores as $store) {
    echo "{$store->name} - {$store->distance_from} km jauhnya";
}
```

### 2. Lokasi Acara

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
            return $this->venue_name ?? 'Lokasi Akan Diumumkan';
        }
        
        return $this->venue_name . " ({$this->latitude}, {$this->longitude})";
    }
}

// Penggunaan
$events = Event::inBounds(-6.0, -6.5, 107.0, 106.5)->get(); // area Jakarta
```

### 3. Pelacakan Pengiriman

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
        // Algoritma tetangga terdekat sederhana
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

### 4. Analitik Geografis

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
        // Klastering k-means sederhana
        $points = static::select('latitude', 'longitude')->get();
        
        if ($points->count() < $k) {
            return $points;
        }
        
        // Inisialisasi pusat acak
        $centers = $points->random($k);
        
        for ($iteration = 0; $iteration < 10; $iteration++) {
            $clusters = collect();
            
            // Tetapkan titik ke pusat terdekat
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
            
            // Perbarui pusat
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

## Kueri Geografis Lanjutan

### Kueri Bounding Box

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

### Penahanan Poligon

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

## Validasi

### Validasi Koordinat

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
            'latitude.between' => 'Lintang harus antara -90 dan 90 derajat.',
            'longitude.between' => 'Bujur harus antara -180 dan 180 derajat.'
        ];
    }
}
```

### Validasi Batas Wilayah Indonesia

```php
class IndonesiaLocationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'latitude' => [
                'required',
                'numeric',
                'between:-11,6' // Batas lintang Indonesia
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:95,141' // Batas bujur Indonesia
            ]
        ];
    }
}
```

## Tips Kinerja

### 1. Pengindeksan Spasial

```php
// Dalam migrasi
Schema::table('stores', function (Blueprint $table) {
    $table->spatialIndex(['latitude', 'longitude']);
});
```

### 2. Pra-pemfilteran Bounding Box

```php
// Lebih efisien untuk dataset besar
public function scopeNearbyOptimized($query, $lat, $lng, $radiusKm)
{
    $latDelta = $radiusKm / 111; // Konversi kasar km ke derajat
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

## Dokumentasi Terkait

- **[Contoh Kueri Geografis](/id/examples/geographic-queries)** - Contoh kueri geografis lanjutan
- **[Trait WithAddress](/id/api/concerns/with-address)** - Untuk lokasi berbasis alamat
- **[Model & Relasi](/id/guide/models)** - Memahami model-model Laravel Nusa