# Query Geografis

Laravel Nusa menyediakan data geografis termasuk koordinat, batas wilayah, dan kode pos. Panduan ini menunjukkan cara bekerja dengan informasi geografis untuk pemetaan, layanan lokasi, dan analisis spasial.

## Bekerja dengan Koordinat

### Akses Koordinat Dasar

```php
use Creasi\Nusa\Models\Province;

$province = Province::find('33'); // Jawa Tengah

// Dapatkan koordinat pusat
$latitude = $province->latitude;   // -6.9934809206806
$longitude = $province->longitude; // 110.42024335421

echo "Pusat Jawa Tengah: {$latitude}, {$longitude}";
```

### Koordinat Batas Wilayah

```php
use Creasi\Nusa\Models\{Province, Regency};

$province = Province::find('33');

// Dapatkan koordinat batas wilayah (jika tersedia)
$boundaries = $province->coordinates;

if ($boundaries) {
    echo "Provinsi memiliki " . count($boundaries) . " titik batas";
    
    // Konversi ke format GeoJSON
    $geoJson = [
        'type' => 'Feature',
        'properties' => [
            'name' => $province->name,
            'code' => $province->code
        ],
        'geometry' => [
            'type' => 'Polygon',
            'coordinates' => [$boundaries]
        ]
    ];
    
    // Gunakan dengan library pemetaan
    return response()->json($geoJson);
}
```

## Pencarian Berdasarkan Lokasi

### Mencari Wilayah Terdekat

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class LocationService
{
    public function findNearestProvince(float $latitude, float $longitude): ?Province
    {
        return Province::selectRaw("
            *, (
                6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->orderBy('distance')
        ->first();
    }
    
    public function findRegenciesInRadius(float $latitude, float $longitude, int $radiusKm = 50): Collection
    {
        return Regency::selectRaw("
            *, (
                6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->having('distance', '<', $radiusKm)
        ->orderBy('distance')
        ->get();
    }
}

// Penggunaan
$locationService = new LocationService();

// Cari provinsi terdekat dari Jakarta
$nearestProvince = $locationService->findNearestProvince(-6.2088, 106.8456);
echo "Provinsi terdekat: {$nearestProvince->name}";

// Cari kabupaten/kota dalam radius 50km
$nearbyRegencies = $locationService->findRegenciesInRadius(-6.2088, 106.8456, 50);
foreach ($nearbyRegencies as $regency) {
    echo "{$regency->name}: {$regency->distance} km\n";
}
```

### Point-in-Polygon Queries

```php
use Creasi\Nusa\Models\Province;

class GeographicService
{
    public function findProvinceByCoordinates(float $latitude, float $longitude): ?Province
    {
        $provinces = Province::whereNotNull('coordinates')->get();
        
        foreach ($provinces as $province) {
            if ($this->isPointInPolygon($latitude, $longitude, $province->coordinates)) {
                return $province;
            }
        }
        
        return null;
    }
    
    private function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $vertices = count($polygon);
        $inside = false;
        
        for ($i = 0, $j = $vertices - 1; $i < $vertices; $j = $i++) {
            $xi = $polygon[$i][0]; // longitude
            $yi = $polygon[$i][1]; // latitude
            $xj = $polygon[$j][0]; // longitude
            $yj = $polygon[$j][1]; // latitude
            
            if ((($yi > $lat) !== ($yj > $lat)) && 
                ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }
        
        return $inside;
    }
}

// Penggunaan
$geoService = new GeographicService();
$province = $geoService->findProvinceByCoordinates(-6.2088, 106.8456);

if ($province) {
    echo "Koordinat berada di: {$province->name}";
} else {
    echo "Koordinat tidak ditemukan dalam wilayah Indonesia";
}
```

## Bekerja dengan Kode Pos

### Pencarian Berdasarkan Kode Pos

```php
use Creasi\Nusa\Models\Village;

// Cari semua desa dengan kode pos tertentu
$villages = Village::where('postal_code', '50241')->get();

foreach ($villages as $village) {
    echo "Desa: {$village->name}, Kecamatan: {$village->district->name}\n";
}

// Cari desa dalam range kode pos
$jakartaVillages = Village::whereBetween('postal_code', ['10000', '19999'])->get();

// Cari desa tanpa kode pos
$villagesWithoutPostal = Village::whereNull('postal_code')->count();
echo "Desa tanpa kode pos: {$villagesWithoutPostal}";
```

### Validasi Kode Pos

```php
class PostalCodeService
{
    public function validatePostalCode(string $villageCode, string $postalCode): bool
    {
        $village = Village::find($villageCode);
        
        if (!$village) {
            return false;
        }
        
        return $village->postal_code === $postalCode;
    }
    
    public function findVillagesByPostalCode(string $postalCode): Collection
    {
        return Village::where('postal_code', $postalCode)
            ->with(['district.regency.province'])
            ->get();
    }
    
    public function suggestPostalCode(string $villageCode): ?string
    {
        $village = Village::find($villageCode);
        
        if (!$village || $village->postal_code) {
            return $village?->postal_code;
        }
        
        // Cari kode pos dari desa terdekat di kecamatan yang sama
        $nearbyVillage = Village::where('district_code', $village->district_code)
            ->whereNotNull('postal_code')
            ->first();
            
        return $nearbyVillage?->postal_code;
    }
}
```

## Analisis Spasial

### Menghitung Jarak

```php
class DistanceCalculator
{
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    public function findNearestVillages(float $latitude, float $longitude, int $limit = 10): Collection
    {
        return Village::selectRaw("
            *, (
                6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(COALESCE(latitude, 0))) * 
                    cos(radians(COALESCE(longitude, 0)) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(COALESCE(latitude, 0)))
                )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->orderBy('distance')
        ->limit($limit)
        ->get();
    }
}

// Penggunaan
$calculator = new DistanceCalculator();

// Hitung jarak antara Jakarta dan Surabaya
$distance = $calculator->calculateDistance(-6.2088, 106.8456, -7.2575, 112.7521);
echo "Jarak Jakarta-Surabaya: {$distance} km";

// Cari 10 desa terdekat dari koordinat tertentu
$nearestVillages = $calculator->findNearestVillages(-6.2088, 106.8456, 10);
foreach ($nearestVillages as $village) {
    echo "{$village->name}: {$village->distance} km\n";
}
```

### Clustering Geografis

```php
class GeographicCluster
{
    public function clusterRegenciesByDistance(float $maxDistance = 100): array
    {
        $regencies = Regency::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
            
        $clusters = [];
        $visited = [];
        
        foreach ($regencies as $regency) {
            if (in_array($regency->id, $visited)) {
                continue;
            }
            
            $cluster = [$regency];
            $visited[] = $regency->id;
            
            foreach ($regencies as $otherRegency) {
                if (in_array($otherRegency->id, $visited)) {
                    continue;
                }
                
                $distance = $this->calculateDistance(
                    $regency->latitude, $regency->longitude,
                    $otherRegency->latitude, $otherRegency->longitude
                );
                
                if ($distance <= $maxDistance) {
                    $cluster[] = $otherRegency;
                    $visited[] = $otherRegency->id;
                }
            }
            
            $clusters[] = $cluster;
        }
        
        return $clusters;
    }
    
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}
```

## Integrasi Pemetaan

### Integrasi Leaflet.js

```js
// Inisialisasi peta
const map = L.map('map').setView([-2.5, 118], 5); // Pusat di Indonesia

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Muat dan tampilkan provinsi
async function loadProvinces() {
    try {
        const response = await fetch('/nusa/provinces');
        const data = await response.json();

        data.data.forEach(province => {
            if (province.latitude && province.longitude) {
                // Tambahkan marker provinsi
                const marker = L.marker([province.latitude, province.longitude])
                    .addTo(map)
                    .bindPopup(`
                        <strong>${province.name}</strong><br>
                        Kode: ${province.code}<br>
                        Koordinat: ${province.latitude}, ${province.longitude}
                    `);

                // Tambahkan batas provinsi jika tersedia
                if (province.coordinates && province.coordinates.length > 0) {
                    const polygon = L.polygon(province.coordinates, {
                        color: 'blue',
                        fillColor: 'lightblue',
                        fillOpacity: 0.3
                    }).addTo(map)
                    .bindPopup(`<strong>${province.name}</strong>`);
                }
            }
        });
    } catch (error) {
        console.error('Error loading provinces:', error);
    }
}

// Muat kabupaten/kota berdasarkan provinsi
async function loadRegencies(provinceCode) {
    try {
        const response = await fetch(`/nusa/provinces/${provinceCode}/regencies`);
        const data = await response.json();

        // Clear existing regency markers
        map.eachLayer(layer => {
            if (layer.options && layer.options.type === 'regency') {
                map.removeLayer(layer);
            }
        });

        data.data.forEach(regency => {
            if (regency.latitude && regency.longitude) {
                const marker = L.circleMarker([regency.latitude, regency.longitude], {
                    type: 'regency',
                    color: 'red',
                    fillColor: 'pink',
                    fillOpacity: 0.7,
                    radius: 5
                }).addTo(map)
                .bindPopup(`
                    <strong>${regency.name}</strong><br>
                    Kode: ${regency.code}<br>
                    Provinsi: ${regency.province_name}
                `);
            }
        });
    } catch (error) {
        console.error('Error loading regencies:', error);
    }
}

// Event listener untuk klik provinsi
map.on('click', async function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;

    // Cari provinsi berdasarkan koordinat
    try {
        const response = await fetch(`/api/find-province?lat=${lat}&lng=${lng}`);
        const province = await response.json();

        if (province) {
            loadRegencies(province.code);
        }
    } catch (error) {
        console.error('Error finding province:', error);
    }
});

// Inisialisasi
loadProvinces();
```

### Google Maps Integration

```js
class NusaGoogleMaps {
    constructor(mapElementId, options = {}) {
        this.map = new google.maps.Map(document.getElementById(mapElementId), {
            center: { lat: -2.5, lng: 118 }, // Pusat Indonesia
            zoom: 5,
            ...options
        });

        this.markers = [];
        this.polygons = [];
    }

    async loadProvinces() {
        try {
            const response = await fetch('/nusa/provinces');
            const data = await response.json();

            data.data.forEach(province => {
                if (province.latitude && province.longitude) {
                    this.addProvinceMarker(province);
                }
            });
        } catch (error) {
            console.error('Error loading provinces:', error);
        }
    }

    addProvinceMarker(province) {
        const marker = new google.maps.Marker({
            position: { lat: province.latitude, lng: province.longitude },
            map: this.map,
            title: province.name,
            icon: {
                url: '/images/province-marker.png',
                scaledSize: new google.maps.Size(30, 30)
            }
        });

        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div>
                    <h3>${province.name}</h3>
                    <p>Kode: ${province.code}</p>
                    <p>Koordinat: ${province.latitude}, ${province.longitude}</p>
                    <button onclick="loadRegencies('${province.code}')">
                        Lihat Kabupaten/Kota
                    </button>
                </div>
            `
        });

        marker.addListener('click', () => {
            infoWindow.open(this.map, marker);
        });

        this.markers.push(marker);

        // Tambahkan polygon jika ada data batas
        if (province.coordinates && province.coordinates.length > 0) {
            this.addProvincePolygon(province);
        }
    }

    addProvincePolygon(province) {
        const coordinates = province.coordinates.map(coord => ({
            lat: coord[1], // latitude
            lng: coord[0]  // longitude
        }));

        const polygon = new google.maps.Polygon({
            paths: coordinates,
            strokeColor: '#0000FF',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#0000FF',
            fillOpacity: 0.1
        });

        polygon.setMap(this.map);
        this.polygons.push(polygon);

        // Event listener untuk klik polygon
        polygon.addListener('click', (event) => {
            this.showProvinceInfo(province, event.latLng);
        });
    }

    showProvinceInfo(province, position) {
        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div>
                    <h3>${province.name}</h3>
                    <p>Kode: ${province.code}</p>
                    <p>Luas: ${province.area || 'N/A'} km²</p>
                </div>
            `,
            position: position
        });

        infoWindow.open(this.map);
    }

    clearMarkers() {
        this.markers.forEach(marker => marker.setMap(null));
        this.markers = [];
    }

    clearPolygons() {
        this.polygons.forEach(polygon => polygon.setMap(null));
        this.polygons = [];
    }

    fitBounds(coordinates) {
        const bounds = new google.maps.LatLngBounds();
        coordinates.forEach(coord => {
            bounds.extend(new google.maps.LatLng(coord[1], coord[0]));
        });
        this.map.fitBounds(bounds);
    }
}

// Penggunaan
const nusaMap = new NusaGoogleMaps('map');
nusaMap.loadProvinces();

// Function untuk memuat kabupaten/kota
async function loadRegencies(provinceCode) {
    try {
        const response = await fetch(`/nusa/provinces/${provinceCode}/regencies`);
        const data = await response.json();

        // Clear existing markers
        nusaMap.clearMarkers();

        data.data.forEach(regency => {
            if (regency.latitude && regency.longitude) {
                const marker = new google.maps.Marker({
                    position: { lat: regency.latitude, lng: regency.longitude },
                    map: nusaMap.map,
                    title: regency.name,
                    icon: {
                        url: '/images/regency-marker.png',
                        scaledSize: new google.maps.Size(20, 20)
                    }
                });

                nusaMap.markers.push(marker);
            }
        });
    } catch (error) {
        console.error('Error loading regencies:', error);
    }
}
```

### Mapbox Integration

```js
class NusaMapbox {
    constructor(containerId, accessToken) {
        mapboxgl.accessToken = accessToken;

        this.map = new mapboxgl.Map({
            container: containerId,
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [118, -2.5], // Pusat Indonesia
            zoom: 4
        });

        this.map.on('load', () => {
            this.loadProvinceData();
        });
    }

    async loadProvinceData() {
        try {
            const response = await fetch('/nusa/provinces');
            const data = await response.json();

            // Konversi ke GeoJSON
            const geojson = {
                type: 'FeatureCollection',
                features: data.data.map(province => ({
                    type: 'Feature',
                    properties: {
                        name: province.name,
                        code: province.code
                    },
                    geometry: {
                        type: 'Point',
                        coordinates: [province.longitude, province.latitude]
                    }
                })).filter(feature =>
                    feature.geometry.coordinates[0] &&
                    feature.geometry.coordinates[1]
                )
            };

            // Tambahkan source
            this.map.addSource('provinces', {
                type: 'geojson',
                data: geojson
            });

            // Tambahkan layer
            this.map.addLayer({
                id: 'provinces',
                type: 'circle',
                source: 'provinces',
                paint: {
                    'circle-radius': 8,
                    'circle-color': '#007cbf',
                    'circle-stroke-width': 2,
                    'circle-stroke-color': '#ffffff'
                }
            });

            // Tambahkan popup
            this.map.on('click', 'provinces', (e) => {
                const coordinates = e.features[0].geometry.coordinates.slice();
                const properties = e.features[0].properties;

                new mapboxgl.Popup()
                    .setLngLat(coordinates)
                    .setHTML(`
                        <h3>${properties.name}</h3>
                        <p>Kode: ${properties.code}</p>
                    `)
                    .addTo(this.map);
            });

            // Ubah cursor saat hover
            this.map.on('mouseenter', 'provinces', () => {
                this.map.getCanvas().style.cursor = 'pointer';
            });

            this.map.on('mouseleave', 'provinces', () => {
                this.map.getCanvas().style.cursor = '';
            });

        } catch (error) {
            console.error('Error loading province data:', error);
        }
    }

    async loadProvinceBoundaries() {
        try {
            const response = await fetch('/nusa/provinces?include=boundaries');
            const data = await response.json();

            const geojson = {
                type: 'FeatureCollection',
                features: data.data
                    .filter(province => province.coordinates && province.coordinates.length > 0)
                    .map(province => ({
                        type: 'Feature',
                        properties: {
                            name: province.name,
                            code: province.code
                        },
                        geometry: {
                            type: 'Polygon',
                            coordinates: [province.coordinates]
                        }
                    }))
            };

            this.map.addSource('province-boundaries', {
                type: 'geojson',
                data: geojson
            });

            this.map.addLayer({
                id: 'province-boundaries',
                type: 'fill',
                source: 'province-boundaries',
                paint: {
                    'fill-color': '#007cbf',
                    'fill-opacity': 0.1
                }
            });

            this.map.addLayer({
                id: 'province-boundaries-outline',
                type: 'line',
                source: 'province-boundaries',
                paint: {
                    'line-color': '#007cbf',
                    'line-width': 2
                }
            });

        } catch (error) {
            console.error('Error loading province boundaries:', error);
        }
    }
}

// Penggunaan
const nusaMapbox = new NusaMapbox('map', 'your-mapbox-access-token');
```

## Layanan Lokasi

### Geocoding Alamat

```php
class LocationService
{
    /**
     * Dapatkan koordinat untuk alamat
     */
    public function geocodeAddress(array $addressComponents): ?array
    {
        $village = Village::find($addressComponents['village_code']);

        if ($village && $village->latitude && $village->longitude) {
            return [
                'latitude' => $village->latitude,
                'longitude' => $village->longitude,
                'accuracy' => 'village'
            ];
        }

        $district = District::find($addressComponents['district_code']);
        if ($district && $district->latitude && $district->longitude) {
            return [
                'latitude' => $district->latitude,
                'longitude' => $district->longitude,
                'accuracy' => 'district'
            ];
        }

        $regency = Regency::find($addressComponents['regency_code']);
        if ($regency && $regency->latitude && $regency->longitude) {
            return [
                'latitude' => $regency->latitude,
                'longitude' => $regency->longitude,
                'accuracy' => 'regency'
            ];
        }

        $province = Province::find($addressComponents['province_code']);
        if ($province && $province->latitude && $province->longitude) {
            return [
                'latitude' => $province->latitude,
                'longitude' => $province->longitude,
                'accuracy' => 'province'
            ];
        }

        return null;
    }

    /**
     * Reverse geocoding - cari alamat dari koordinat
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        // Cari desa terdekat
        $village = Village::selectRaw("
            *, (
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(COALESCE(latitude, 0))) *
                    cos(radians(COALESCE(longitude, 0)) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(COALESCE(latitude, 0)))
                )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->orderBy('distance')
        ->first();

        if ($village) {
            return [
                'village' => $village->only(['code', 'name']),
                'district' => $village->district->only(['code', 'name']),
                'regency' => $village->district->regency->only(['code', 'name']),
                'province' => $village->district->regency->province->only(['code', 'name']),
                'distance' => $village->distance,
                'accuracy' => 'village'
            ];
        }

        return null;
    }

    /**
     * Cari area layanan berdasarkan koordinat
     */
    public function findServiceArea(float $latitude, float $longitude): array
    {
        $location = $this->reverseGeocode($latitude, $longitude);

        if (!$location) {
            return [];
        }

        // Cari area layanan yang mencakup lokasi ini
        $serviceAreas = ServiceArea::where('is_active', true)->get();
        $coveringAreas = [];

        foreach ($serviceAreas as $area) {
            if ($area->coversAddress(
                $location['province']['code'],
                $location['regency']['code'],
                $location['district']['code'],
                $location['village']['code']
            )) {
                $coveringAreas[] = $area;
            }
        }

        return [
            'location' => $location,
            'service_areas' => $coveringAreas
        ];
    }
}
```

### Layanan Routing

```php
class RoutingService
{
    /**
     * Hitung rute sederhana antara dua titik
     */
    public function calculateRoute(array $origin, array $destination): array
    {
        $distance = $this->calculateDistance(
            $origin['latitude'], $origin['longitude'],
            $destination['latitude'], $destination['longitude']
        );

        // Estimasi waktu berdasarkan jarak (asumsi kecepatan rata-rata)
        $averageSpeed = 50; // km/jam
        $estimatedTime = ($distance / $averageSpeed) * 60; // menit

        return [
            'distance_km' => round($distance, 2),
            'estimated_time_minutes' => round($estimatedTime),
            'origin' => $origin,
            'destination' => $destination
        ];
    }

    /**
     * Cari rute melalui beberapa titik
     */
    public function calculateMultiPointRoute(array $waypoints): array
    {
        if (count($waypoints) < 2) {
            throw new InvalidArgumentException('Minimal 2 waypoint diperlukan');
        }

        $totalDistance = 0;
        $totalTime = 0;
        $segments = [];

        for ($i = 0; $i < count($waypoints) - 1; $i++) {
            $segment = $this->calculateRoute($waypoints[$i], $waypoints[$i + 1]);
            $segments[] = $segment;
            $totalDistance += $segment['distance_km'];
            $totalTime += $segment['estimated_time_minutes'];
        }

        return [
            'total_distance_km' => round($totalDistance, 2),
            'total_time_minutes' => round($totalTime),
            'segments' => $segments,
            'waypoints' => $waypoints
        ];
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
```

### Geofencing Service

```php
class GeofencingService
{
    /**
     * Cek apakah titik berada dalam geofence
     */
    public function isPointInGeofence(float $latitude, float $longitude, array $geofence): bool
    {
        return $this->isPointInPolygon($latitude, $longitude, $geofence['coordinates']);
    }

    /**
     * Cari semua geofence yang mengandung titik
     */
    public function findContainingGeofences(float $latitude, float $longitude): array
    {
        $geofences = Geofence::where('is_active', true)->get();
        $containing = [];

        foreach ($geofences as $geofence) {
            if ($this->isPointInPolygon($latitude, $longitude, $geofence->coordinates)) {
                $containing[] = $geofence;
            }
        }

        return $containing;
    }

    /**
     * Hitung jarak ke batas geofence terdekat
     */
    public function distanceToNearestBoundary(float $latitude, float $longitude, array $geofence): float
    {
        $coordinates = $geofence['coordinates'];
        $minDistance = PHP_FLOAT_MAX;

        for ($i = 0; $i < count($coordinates); $i++) {
            $j = ($i + 1) % count($coordinates);

            $distance = $this->distanceToLineSegment(
                $latitude, $longitude,
                $coordinates[$i][1], $coordinates[$i][0], // lat1, lng1
                $coordinates[$j][1], $coordinates[$j][0]  // lat2, lng2
            );

            $minDistance = min($minDistance, $distance);
        }

        return $minDistance;
    }

    private function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $vertices = count($polygon);
        $inside = false;

        for ($i = 0, $j = $vertices - 1; $i < $vertices; $j = $i++) {
            $xi = $polygon[$i][0]; // longitude
            $yi = $polygon[$i][1]; // latitude
            $xj = $polygon[$j][0]; // longitude
            $yj = $polygon[$j][1]; // latitude

            if ((($yi > $lat) !== ($yj > $lat)) &&
                ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    private function distanceToLineSegment(float $px, float $py, float $x1, float $y1, float $x2, float $y2): float
    {
        $A = $px - $x1;
        $B = $py - $y1;
        $C = $x2 - $x1;
        $D = $y2 - $y1;

        $dot = $A * $C + $B * $D;
        $lenSq = $C * $C + $D * $D;

        if ($lenSq == 0) {
            return sqrt($A * $A + $B * $B);
        }

        $param = $dot / $lenSq;

        if ($param < 0) {
            $xx = $x1;
            $yy = $y1;
        } elseif ($param > 1) {
            $xx = $x2;
            $yy = $y2;
        } else {
            $xx = $x1 + $param * $C;
            $yy = $y1 + $param * $D;
        }

        $dx = $px - $xx;
        $dy = $py - $yy;

        return sqrt($dx * $dx + $dy * $dy) * 111.32; // Convert to km
    }
}
```

Contoh-contoh query geografis ini memberikan fondasi yang kuat untuk membangun aplikasi berbasis lokasi dengan Laravel Nusa. Anda dapat menggunakan data koordinat, kode pos, dan batas wilayah untuk berbagai keperluan seperti pemetaan, analisis spasial, dan layanan berbasis lokasi.
