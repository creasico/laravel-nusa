# Query Geografis

Panduan lengkap untuk melakukan query geografis dan analisis spasial menggunakan Laravel Nusa, termasuk pencarian berdasarkan koordinat, jarak, dan integrasi dengan layanan peta.

## Query Geografis Dasar

### Mencari Lokasi Terdekat

```php
use Creasi\Nusa\Models\Village;

// Cari desa dalam radius tertentu
$centerLat = -6.2088;
$centerLng = 106.8456;
$radiusKm = 10;

$nearbyVillages = Village::selectRaw("
    *,
    (6371 * acos(
        cos(radians(?)) *
        cos(radians(latitude)) *
        cos(radians(longitude) - radians(?)) +
        sin(radians(?)) *
        sin(radians(latitude))
    )) AS distance
", [$centerLat, $centerLng, $centerLat])
->having('distance', '<', $radiusKm)
->orderBy('distance')
->get();
```

### Pencarian Berdasarkan Koordinat

```php
// Cari desa terdekat dari koordinat yang diberikan
$closestVillage = Village::selectRaw("
    *,
    (6371 * acos(
        cos(radians(?)) *
        cos(radians(latitude)) *
        cos(radians(longitude) - radians(?)) +
        sin(radians(?)) *
        sin(radians(latitude))
    )) AS distance
", [$lat, $lng, $lat])
->orderBy('distance')
->first();
```

## Analisis Spasial Lanjutan

### Query Bounding Box

```php
// Cari semua lokasi dalam bounding box
$northEast = ['lat' => -6.1, 'lng' => 107.0];
$southWest = ['lat' => -6.3, 'lng' => 106.7];

$locationsInBounds = Village::whereBetween('latitude', [$southWest['lat'], $northEast['lat']])
    ->whereBetween('longitude', [$southWest['lng'], $northEast['lng']])
    ->get();
```

### Analisis Cakupan Regional

```php
// Analisis cakupan layanan berdasarkan wilayah
class CoverageAnalyzer
{
    public function analyzeProvinceCoverage($serviceLocations)
    {
        $provinces = Province::with('regencies')->get();

        return $provinces->map(function ($province) use ($serviceLocations) {
            $coverage = $this->calculateCoverage($province, $serviceLocations);

            return [
                'province' => $province->name,
                'total_regencies' => $province->regencies->count(),
                'covered_regencies' => $coverage['covered'],
                'coverage_percentage' => $coverage['percentage'],
                'gaps' => $coverage['gaps']
            ];
        });
    }
    
    private function calculateCoverage($province, $serviceLocations)
    {
        $covered = 0;
        $gaps = [];
        
        foreach ($province->regencies as $regency) {
            $hasService = $this->hasServiceInRegency($regency, $serviceLocations);
            
            if ($hasService) {
                $covered++;
            } else {
                $gaps[] = $regency->name;
            }
        }
        
        return [
            'covered' => $covered,
            'percentage' => ($covered / $province->regencies->count()) * 100,
            'gaps' => $gaps
        ];
    }
}
```

## Integrasi Peta

### Integrasi Leaflet.js

```javascript
// Inisialisasi peta dengan batas administratif Indonesia
class IndonesiaMap {
    constructor(containerId) {
        this.map = L.map(containerId).setView([-2.5, 118], 5);
        this.api = new NusaAPI();
        this.init();
    }
    
    init() {
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);
        
        this.loadProvinces();
    }
    
    async loadProvinces() {
        try {
            const response = await this.api.getProvinces();
            this.addProvinceMarkers(response.data);
        } catch (error) {
            console.error('Error loading provinces:', error);
        }
    }
    
    addProvinceMarkers(provinces) {
        provinces.forEach(province => {
            if (province.latitude && province.longitude) {
                const marker = L.marker([province.latitude, province.longitude])
                    .bindPopup(`
                        <strong>${province.name}</strong><br>
                        Kabupaten/Kota: ${province.regencies_count}<br>
                        Kecamatan: ${province.districts_count}<br>
                        Desa: ${province.villages_count}
                    `)
                    .addTo(this.map);
                
                marker.on('click', () => this.onProvinceClick(province));
            }
        });
    }
    
    async onProvinceClick(province) {
        // Load and display regencies when province is clicked
        const response = await this.api.getRegenciesByProvince(province.code);
        this.showRegencies(response.data);
    }
    
    showRegencies(regencies) {
        // Clear existing regency markers
        this.clearRegencyMarkers();
        
        regencies.forEach(regency => {
            if (regency.latitude && regency.longitude) {
                const marker = L.circleMarker([regency.latitude, regency.longitude], {
                    radius: 5,
                    fillColor: '#ff7800',
                    color: '#000',
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                })
                .bindPopup(`
                    <strong>${regency.name}</strong><br>
                    Kecamatan: ${regency.districts_count}<br>
                    Desa: ${regency.villages_count}
                `)
                .addTo(this.map);
                
                this.regencyMarkers.push(marker);
            }
        });
    }
}
```

## Distance Calculations

### Haversine Formula Implementation

```php
class DistanceCalculator
{
    const EARTH_RADIUS_KM = 6371;
    
    public static function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);
        
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;
        
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return self::EARTH_RADIUS_KM * $c;
    }
    
    public static function findNearestLocations($centerLat, $centerLng, $locations, $limit = 10)
    {
        $locationsWithDistance = $locations->map(function ($location) use ($centerLat, $centerLng) {
            $distance = self::calculateDistance(
                $centerLat, 
                $centerLng, 
                $location->latitude, 
                $location->longitude
            );
            
            $location->distance = $distance;
            return $location;
        });
        
        return $locationsWithDistance
            ->sortBy('distance')
            ->take($limit);
    }
}
```

### Service Area Analysis

```php
class ServiceAreaAnalyzer
{
    public function analyzeServiceCoverage($servicePoints, $maxDistanceKm = 50)
    {
        $villages = Village::all();
        $coverage = [];
        
        foreach ($villages as $village) {
            $nearestService = $this->findNearestService($village, $servicePoints);
            
            $coverage[] = [
                'village' => $village,
                'nearest_service' => $nearestService,
                'distance' => $nearestService['distance'],
                'is_covered' => $nearestService['distance'] <= $maxDistanceKm
            ];
        }
        
        return $this->generateCoverageReport($coverage);
    }
    
    private function findNearestService($village, $servicePoints)
    {
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($servicePoints as $service) {
            $distance = DistanceCalculator::calculateDistance(
                $village->latitude,
                $village->longitude,
                $service['latitude'],
                $service['longitude']
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $service;
            }
        }
        
        return [
            'service' => $nearest,
            'distance' => $minDistance
        ];
    }
}
```

## Performance Optimization

### Spatial Indexing

```php
// Add spatial indexes for better performance
Schema::table('nusa_villages', function (Blueprint $table) {
    $table->spatialIndex(['latitude', 'longitude']);
});

// Use spatial queries for better performance
$nearbyVillages = Village::whereRaw("
    ST_Distance_Sphere(
        POINT(longitude, latitude),
        POINT(?, ?)
    ) <= ?
", [$centerLng, $centerLat, $radiusMeters])
->get();
```

### Caching Geographic Queries

```php
class CachedGeoQuery
{
    public function findNearbyVillages($lat, $lng, $radiusKm)
    {
        $cacheKey = "nearby_villages_{$lat}_{$lng}_{$radiusKm}";
        
        return Cache::remember($cacheKey, 3600, function () use ($lat, $lng, $radiusKm) {
            return Village::selectRaw("
                *,
                (6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance')
            ->get();
        });
    }
}
```

## Next Steps

- **[Custom Models](/id/examples/custom-models)** - Extending Laravel Nusa models
- **[API Integration](/id/examples/api-integration)** - Advanced API usage patterns
- **[Address Forms](/id/examples/address-forms)** - Building interactive address forms
