# Geographic Queries

Laravel Nusa provides geographic data including coordinates, boundaries, and postal codes. This guide shows how to work with geographic information for mapping, location services, and spatial analysis.

## Working with Coordinates

### Basic Coordinate Access

```php
use Creasi\Nusa\Models\Province;

$province = Province::find('33'); // Central Java

// Get center coordinates
$latitude = $province->latitude;   // -6.9934809206806
$longitude = $province->longitude; // 110.42024335421

echo "Central Java center: {$latitude}, {$longitude}";
```

### Boundary Coordinates

```php
use Creasi\Nusa\Models\{Province, Regency};

$province = Province::find('33');

// Get boundary coordinates (if available)
$boundaries = $province->coordinates;

if ($boundaries) {
    echo "Province has " . count($boundaries) . " boundary points";
    
    // Convert to GeoJSON format
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
    
    // Use with mapping libraries
    return response()->json($geoJson);
}
```

## Postal Code Queries

### Find by Postal Code

```php
use Creasi\Nusa\Models\Village;

// Find villages by postal code
$villages = Village::where('postal_code', '51111')->get();

foreach ($villages as $village) {
    echo "{$village->name}, {$village->district->name}";
}

// Find all postal codes in a province
$province = Province::find('33');
$postalCodes = $province->postal_codes;

echo "Postal codes in {$province->name}: " . implode(', ', $postalCodes);
```

### Postal Code Validation

```php
function validatePostalCode(string $postalCode, string $villageCode): bool
{
    $village = Village::find($villageCode);
    
    return $village && $village->postal_code === $postalCode;
}

// Usage
$isValid = validatePostalCode('51111', '3375011002');
```

## Distance Calculations

### Haversine Distance Formula

```php
class GeographicHelper
{
    /**
     * Calculate distance between two points using Haversine formula
     * 
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in kilometers
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Find nearest administrative region to given coordinates
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

// Usage
$userLat = -6.200000;
$userLon = 106.816666;

$nearestProvince = GeographicHelper::findNearestProvince($userLat, $userLon);
echo "Nearest province: {$nearestProvince->name}";

// Calculate distance between two cities
$jakarta = Regency::search('jakarta pusat')->first();
$semarang = Regency::search('kota semarang')->first();

$distance = GeographicHelper::calculateDistance(
    $jakarta->latitude, $jakarta->longitude,
    $semarang->latitude, $semarang->longitude
);

echo "Distance from Jakarta to Semarang: " . round($distance, 2) . " km";
```

## Mapping Integration

### Leaflet.js Integration

```javascript
// Initialize map
const map = L.map('map').setView([-2.5, 118], 5); // Center on Indonesia

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Load and display provinces
async function loadProvinces() {
    try {
        const response = await fetch('/nusa/provinces');
        const data = await response.json();
        
        data.data.forEach(province => {
            if (province.latitude && province.longitude) {
                // Add province marker
                const marker = L.marker([province.latitude, province.longitude])
                    .addTo(map)
                    .bindPopup(`
                        <strong>${province.name}</strong><br>
                        Code: ${province.code}<br>
                        Coordinates: ${province.latitude}, ${province.longitude}
                    `);
                
                // Add province boundary if available
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
        console.error('Error loading provinces:', error);
    }
}

// Load regencies when province is clicked
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
                    Province: ${regency.province_code}<br>
                    Code: ${regency.code}
                `);
            }
        });
    } catch (error) {
        console.error('Error loading regencies:', error);
    }
}

// Initialize
loadProvinces();
```

### Google Maps Integration

```javascript
class IndonesiaMap {
    constructor(mapElementId) {
        this.map = new google.maps.Map(document.getElementById(mapElementId), {
            zoom: 5,
            center: { lat: -2.5, lng: 118 }, // Center on Indonesia
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
            console.error('Error loading provinces:', error);
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
                    <p>Code: ${province.code}</p>
                    <p>Coordinates: ${province.latitude}, ${province.longitude}</p>
                    <button onclick="loadRegencies('${province.code}')">
                        Load Regencies
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

// Initialize map
const indonesiaMap = new IndonesiaMap('map');
indonesiaMap.loadProvinces();
```

## Spatial Queries

### Point-in-Polygon Checks

```php
class SpatialHelper
{
    /**
     * Check if a point is inside a polygon using ray casting algorithm
     */
    public static function pointInPolygon(float $lat, float $lon, array $polygon): bool
    {
        $x = $lon;
        $y = $lat;
        $inside = false;
        
        $j = count($polygon) - 1;
        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i][1]; // longitude
            $yi = $polygon[$i][0]; // latitude
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
     * Find which province contains the given coordinates
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

// Usage
$userLat = -6.200000;
$userLon = 106.816666;

$province = SpatialHelper::findProvinceByCoordinates($userLat, $userLon);
if ($province) {
    echo "You are in: {$province->name}";
} else {
    echo "Location not found in any province";
}
```

### Bounding Box Queries

```php
class BoundingBoxHelper
{
    /**
     * Calculate bounding box for a set of coordinates
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
     * Find administrative regions within bounding box
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

// Usage - Find regions in Jakarta area
$jakartaBounds = BoundingBoxHelper::findRegionsInBoundingBox(
    -6.4, -6.0,  // Latitude range
    106.6, 107.0 // Longitude range
);

echo "Found {$jakartaBounds['provinces']->count()} provinces";
echo "Found {$jakartaBounds['regencies']->count()} regencies";
```

## Location Services

### Address Geocoding

```php
class LocationService
{
    /**
     * Get coordinates for an address
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
     * Reverse geocoding - find address from coordinates
     */
    public function reverseGeocode(float $lat, float $lon): ?array
    {
        // Find nearest village
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
        // This is a simplified version - in production, you'd use spatial indexes
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

// Usage
$locationService = new LocationService();

// Geocode an address
$coordinates = $locationService->geocodeAddress([
    'village_code' => '3375011002',
    'district_code' => '337501',
    'regency_code' => '3375',
    'province_code' => '33'
]);

if ($coordinates) {
    echo "Address coordinates: {$coordinates['latitude']}, {$coordinates['longitude']}";
    echo "Accuracy: {$coordinates['accuracy']}";
}

// Reverse geocode coordinates
$address = $locationService->reverseGeocode(-6.8969497174987, 109.66208089654);
if ($address) {
    echo "Address: {$address['full_address']}";
}
```

These examples demonstrate how to work with geographic data in Laravel Nusa for mapping, location services, and spatial analysis. The geographic features enable building sophisticated location-based applications with accurate Indonesian administrative data.
