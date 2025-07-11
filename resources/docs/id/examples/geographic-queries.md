# Kueri Geografis

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
    
    // Gunakan dengan pustaka pemetaan
    return response()->json($geoJson);
}
```

## Kueri Kode Pos

### Cari berdasarkan Kode Pos

```php
use Creasi\Nusa\Models\Village;

// Temukan desa/kelurahan berdasarkan kode pos
$villages = Village::where('postal_code', '51111')->get();

foreach ($villages as $village) {
    echo "{$village->name}, {$village->district->name}";
}

// Temukan semua kode pos di provinsi
$province = Province::find('33');
$postalCodes = $province->postal_codes;

echo "Kode pos di {$province->name}: " . implode(', ', $postalCodes);
```

### Validasi Kode Pos

```php
function validatePostalCode(string $postalCode, string $villageCode): bool
{
    $village = Village::find($villageCode);
    
    return $village && $village->postal_code === $postalCode;
}

// Penggunaan
$isValid = validatePostalCode('51111', '3375011002');
```

## Perhitungan Jarak

### Formula Jarak Haversine

```php
class GeographicHelper
{
    /**
     * Hitung jarak antara dua titik menggunakan formula Haversine
     * 
     * @param float $lat1 Lintang titik pertama
     * @param float $lon1 Bujur titik pertama
     * @param float $lat2 Lintang titik kedua
     * @param float $lon2 Bujur titik kedua
     * @return float Jarak dalam kilometer
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Radius bumi dalam kilometer
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Temukan wilayah administratif terdekat dari koordinat yang diberikan
     */
    public static function findNearestProvince(float $lat, float $lon): ?Province
    {
        $provinces = Province::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
            
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($provinces as $province) {
            $distance = self::calculateDistance(
                $lat, $lon,
                $province->latitude, $province->longitude
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $province;
            }
        }
        
        return $nearest;
    }
}

// Penggunaan
$userLat = -6.200000;
$userLon = 106.816666;

$nearestProvince = GeographicHelper::findNearestProvince($userLat, $userLon);
echo "Provinsi terdekat: {$nearestProvince->name}";

// Hitung jarak antara dua kota
$jakarta = Regency::search('jakarta pusat')->first();
$semarang = Regency::search('kota semarang')->first();

$distance = GeographicHelper::calculateDistance(
    $jakarta->latitude, $jakarta->longitude,
    $semarang->latitude, $semarang->longitude
);

echo "Jarak dari Jakarta ke Semarang: " . round($distance, 2) . " km";
```

## Integrasi Pemetaan

### Integrasi Leaflet.js

```js
// Inisialisasi peta
const map = L.map('map').setView([-2.5, 118], 5); // Pusat di Indonesia

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© Kontributor OpenStreetMap'
}).addTo(map);

// Muat dan tampilkan provinsi
async function loadProvinces() {
    try {
        const response = await fetch('/nusa/provinces');
        const data = await response.json();
        
        data.data.forEach(province => {
            if (province.latitude && province.longitude) {
                // Tambahkan penanda provinsi
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
                    }).addTo(map);
                    
                    polygon.bindPopup(`<strong>${province.name}</strong>`);
                }
            }
        });
    } catch (error) {
        console.error('Error memuat provinsi:', error);
    }
}

// Muat kabupaten/kota saat provinsi diklik
async function loadRegencies(provinceCode) {
    try {
        const response = await fetch(`/nusa/provinces/${provinceCode}/regencies`);
        const data = await response.json();
        
        data.data.forEach(regency => {
            if (regency.latitude && regency.longitude) {
                L.circleMarker([regency.latitude, regency.longitude], {
                    radius: 5,
                    color: 'red',
                    fillColor: 'orange',
                    fillOpacity: 0.8
                }).addTo(map)
                .bindPopup(`
                    <strong>${regency.name}</strong><br>
                    Provinsi: ${regency.province_code}<br>
                    Kode: ${regency.code}
                `);
            }
        });
    } catch (error) {
        console.error('Error memuat kabupaten/kota:', error);
    }
}

// Inisialisasi
loadProvinces();
```

### Integrasi Google Maps

```js
class IndonesiaMap {
    constructor(mapElementId) {
        this.map = new google.maps.Map(document.getElementById(mapElementId), {
            zoom: 5,
            center: { lat: -2.5, lng: 118 }, // Pusat di Indonesia
            mapTypeId: 'terrain'
        });
        
        this.infoWindow = new google.maps.InfoWindow();
        this.markers = [];
        this.polygons = [];
    }
    
    async loadProvinces() {
        try {
            const response = await fetch('/nusa/provinces');
            const data = await response.json();
            
            data.data.forEach(province => {
                this.addProvinceMarker(province);
                this.addProvinceBoundary(province);
            });
        } catch (error) {
            console.error('Error memuat provinsi:', error);
        }
    }
    
    addProvinceMarker(province) {
        if (!province.latitude || !province.longitude) return;
        
        const marker = new google.maps.Marker({
            position: { lat: province.latitude, lng: province.longitude },
            map: this.map,
            title: province.name,
            icon: {
                url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
            }
        });
        
        marker.addListener('click', () => {
            this.infoWindow.setContent(`
                <div>
                    <h3>${province.name}</h3>
                    <p>Kode: ${province.code}</p>
                    <p>Koordinat: ${province.latitude}, ${province.longitude}</p>
                    <button onclick="loadRegencies('${province.code}')">
                        Muat Kabupaten/Kota
                    </button>
                </div>
            `);
            this.infoWindow.open(this.map, marker);
        });
        
        this.markers.push(marker);
    }
    
    addProvinceBoundary(province) {
        if (!province.coordinates || province.coordinates.length === 0) return;
        
        const coordinates = province.coordinates.map(coord => ({
            lat: coord[0],
            lng: coord[1]
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
    }
    
    clearMarkers() {
        this.markers.forEach(marker => marker.setMap(null));
        this.markers = [];
    }
    
    clearPolygons() {
        this.polygons.forEach(polygon => polygon.setMap(null));
        this.polygons = [];
    }
}

// Inisialisasi peta
const indonesiaMap = new IndonesiaMap('map');
indonesiaMap.loadProvinces();
```

## Kueri Spasial

### Pemeriksaan Titik-dalam-Poligon

```php
class SpatialHelper
{
    /**
     * Periksa apakah suatu titik berada di dalam poligon menggunakan algoritma ray casting
     */
    public static function pointInPolygon(float $lat, float $lon, array $polygon): bool
    {
        $x = $lon;
        $y = $lat;
        $inside = false;
        
        $j = count($polygon) - 1;
        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i][1]; // bujur
            $yi = $polygon[$i][0]; // lintang
            $xj = $polygon[$j][1];
            $yj = $polygon[$j][0];
            
            if ((($yi > $y) !== ($yj > $y)) && 
                ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }
        
        return $inside;
    }
    
    /**
     * Temukan provinsi mana yang mengandung koordinat yang diberikan
     */
    public static function findProvinceByCoordinates(float $lat, float $lon): ?Province
    {
        $provinces = Province::whereNotNull('coordinates')->get();
        
        foreach ($provinces as $province) {
            if ($province->coordinates && 
                self::pointInPolygon($lat, $lon, $province->coordinates)) {
                return $province;
            }
        }
        
        return null;
    }
}

// Penggunaan
$userLat = -6.200000;
$userLon = 106.816666;

$province = SpatialHelper::findProvinceByCoordinates($userLat, $userLon);
if ($province) {
    echo "Anda berada di: {$province->name}";
} else {
    echo "Lokasi tidak ditemukan di provinsi mana pun";
}
```

### Kueri Bounding Box

```php
class BoundingBoxHelper
{
    /**
     * Hitung bounding box untuk sekumpulan koordinat
     */
    public static function calculateBoundingBox(array $coordinates): array
    {
        $minLat = $maxLat = $coordinates[0][0];
        $minLon = $maxLon = $coordinates[0][1];
        
        foreach ($coordinates as $coord) {
            $lat = $coord[0];
            $lon = $coord[1];
            
            $minLat = min($minLat, $lat);
            $maxLat = max($maxLat, $lat);
            $minLon = min($minLon, $lon);
            $maxLon = max($maxLon, $lon);
        }
        
        return [
            'min_lat' => $minLat,
            'max_lat' => $maxLat,
            'min_lon' => $minLon,
            'max_lon' => $maxLon
        ];
    }
    
    /**
     * Temukan wilayah administratif dalam bounding box
     */
    public static function findRegionsInBoundingBox(
        float $minLat, float $maxLat, 
        float $minLon, float $maxLon
    ): array {
        $provinces = Province::whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLon, $maxLon])
            ->get();
            
        $regencies = Regency::whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLon, $maxLon])
            ->get();
            
        return [
            'provinces' => $provinces,
            'regencies' => $regencies
        ];
    }
}

// Penggunaan - Temukan wilayah di area Jakarta
$jakartaBounds = BoundingBoxHelper::findRegionsInBoundingBox(
    -6.4, -6.0,  // Rentang lintang
    106.6, 107.0 // Rentang bujur
);

echo "Ditemukan {$jakartaBounds['provinces']->count()} provinsi";
echo "Ditemukan {$jakartaBounds['regencies']->count()} kabupaten/kota";
```

## Layanan Lokasi

### Geocoding Alamat

```php
class LocationService
{
    /**
     * Dapatkan koordinat untuk suatu alamat
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
     * Reverse geocoding - temukan alamat dari koordinat
     */
    public function reverseGeocode(float $lat, float $lon): ?array
    {
        // Temukan desa/kelurahan terdekat
        $nearestVillage = $this->findNearestVillage($lat, $lon);
        
        if ($nearestVillage) {
            return [
                'village' => $nearestVillage,
                'district' => $nearestVillage->district,
                'regency' => $nearestVillage->regency,
                'province' => $nearestVillage->province,
                'full_address' => $this->buildFullAddress($nearestVillage)
            ];
        }
        
        return null;
    }
    
    private function findNearestVillage(float $lat, float $lon): ?Village
    {
        // Ini adalah versi sederhana - dalam produksi, Anda akan menggunakan indeks spasial
        $villages = Village::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
            
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($villages as $village) {
            $distance = GeographicHelper::calculateDistance(
                $lat, $lon,
                $village->latitude, $village->longitude
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $village;
            }
        }
        
        return $nearest;
    }
    
    private function buildFullAddress(Village $village): string
    {
        return implode(', ', [
            $village->name,
            $village->district->name,
            $village->regency->name,
            $village->province->name,
            $village->postal_code
        ]);
    }
}

// Penggunaan
$locationService = new LocationService();

// Geocode alamat
$coordinates = $locationService->geocodeAddress([
    'village_code' => '3375011002',
    'district_code' => '337501',
    'regency_code' => '3375',
    'province_code' => '33'
]);

if ($coordinates) {
    echo "Koordinat alamat: {$coordinates['latitude']}, {$coordinates['longitude']}";
    echo "Akurasi: {$coordinates['accuracy']}";
}

// Reverse geocode koordinat
$address = $locationService->reverseGeocode(-6.8969497174987, 109.66208089654);
if ($address) {
    echo "Alamat: {$address['full_address']}";
}
```

Contoh-contoh ini menunjukkan cara bekerja dengan data geografis di Laravel Nusa untuk pemetaan, layanan lokasi, dan analisis spasial. Fitur geografis memungkinkan pembangunan aplikasi berbasis lokasi yang canggih dengan data administratif Indonesia yang akurat.