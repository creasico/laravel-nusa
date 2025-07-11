# Model Kecamatan

Model `District` merepresentasikan kecamatan Indonesia dan menyediakan akses ke semua 7,285 wilayah administratif tingkat ketiga.

## Referensi Class

```php
namespace Creasi\Nusa\Models;

class District extends Model
{
    // Implementasi model
}
```

## Atribut

### Atribut Utama

| Atribut | Type | Deskripsi | Contoh |
|---------|------|-----------|--------|
| `code` | `string` | Kode kecamatan dalam format xx.xx.xx (Primary Key) | `"33.75.01"` |
| `regency_code` | `string` | Kode kabupaten/kota induk (Foreign Key) | `"33.75"` |
| `province_code` | `string` | Kode provinsi induk (Foreign Key) | `"33"` |
| `name` | `string` | Nama kecamatan dalam bahasa Indonesia | `"Pekalongan Barat"` |
| `latitude` | `float\|null` | Lintang pusat geografis | `-6.8969497174987` |
| `longitude` | `float\|null` | Bujur pusat geografis | `109.66208089654` |

### Atribut Computed

| Atribut | Type | Deskripsi |
|---------|------|-----------|
| `postal_codes` | `array` | Semua kode pos dalam kecamatan |

## Relasi

### Belongs To

```php
// Dapatkan kabupaten/kota induk
$district->regency; // Model Regency

// Dapatkan provinsi induk
$district->province; // Model Province
```

### One-to-Many

```php
// Dapatkan semua kelurahan/desa dalam kecamatan
$district->villages; // Collection of Village models

// Dengan eager loading
$district = District::with('villages')->find('33.75.01');
```

## Scope

### Pencarian

```php
// Pencarian berdasarkan nama
$districts = District::search('tengah')->get();

// Pencarian dengan multiple terms
$districts = District::search('semarang tengah')->get();
```

### Filter Berdasarkan Wilayah

```php
// Kecamatan dalam kabupaten/kota tertentu
$districts = District::where('regency_code', '33.75')->get();

// Kecamatan dalam provinsi tertentu
$districts = District::where('province_code', '33')->get();

// Kecamatan dengan koordinat
$districtsWithCoords = District::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();
```

### Scope Geografis

```php
// Kecamatan dalam radius tertentu
$nearbyDistricts = District::nearbyCoordinates(-6.8969, 109.6621, 10)->get();

// Kecamatan dalam bounding box
$districtsInArea = District::withinBounds(-7.0, 109.0, -6.5, 110.0)->get();
```

## Metode

### Pencarian dan Filter

```php
// Cari kecamatan berdasarkan nama
$districts = District::search('pekalongan')->get();

// Filter berdasarkan kabupaten/kota
$semarangDistricts = District::inRegency('33.74')->get();

// Filter berdasarkan provinsi
$centralJavaDistricts = District::inProvince('33')->get();
```

### Informasi Geografis

```php
$district = District::find('33.75.01');

// Dapatkan koordinat pusat
$coordinates = $district->getCoordinates(); // [lat, lng]

// Hitung jarak ke titik lain
$distance = $district->distanceTo(-6.9, 109.7); // dalam kilometer

// Cek apakah titik berada dalam kecamatan
$isInside = $district->containsPoint(-6.8969, 109.6621);
```

### Statistik

```php
$district = District::find('33.75.01');

// Hitung jumlah kelurahan/desa
$villageCount = $district->villages()->count();

// Dapatkan kode pos unik
$postalCodes = $district->getPostalCodes();

// Statistik populasi (jika tersedia)
$stats = $district->getStatistics();
```

## Contoh Penggunaan

### Dasar

```php
use Creasi\Nusa\Models\District;

// Dapatkan semua kecamatan
$districts = District::all();

// Dapatkan kecamatan berdasarkan kode
$district = District::find('33.75.01');

// Pencarian kecamatan
$searchResults = District::search('pekalongan')->get();
```

### Dengan Relasi

```php
// Dapatkan kecamatan dengan kelurahan/desa
$district = District::with('villages')->find('33.75.01');

foreach ($district->villages as $village) {
    echo $village->name . "\n";
}

// Dapatkan kecamatan dengan kabupaten/kota dan provinsi
$district = District::with(['regency.province'])->find('33.75.01');

echo "Alamat: {$district->name}, {$district->regency->name}, {$district->regency->province->name}";
```

### Pagination

```php
// Pagination sederhana
$districts = District::paginate(50);

// Pagination dengan pencarian
$districts = District::search('tengah')->paginate(20);

// Pagination dengan filter
$districts = District::where('regency_code', '33.74')->paginate(10);
```

### Agregasi

```php
// Hitung kecamatan per kabupaten/kota
$districtCounts = District::selectRaw('regency_code, COUNT(*) as count')
    ->groupBy('regency_code')
    ->get();

// Hitung total kelurahan/desa per kecamatan
$stats = District::withCount('villages')->get();

foreach ($stats as $district) {
    echo "{$district->name}: {$district->villages_count} kelurahan/desa\n";
}
```

### Query Geografis

```php
// Cari kecamatan terdekat dari koordinat
$nearestDistricts = District::nearbyCoordinates(-6.9, 109.7, 20)
    ->limit(5)
    ->get();

// Kecamatan dalam bounding box
$districtsInArea = District::withinBounds(-7.0, 109.0, -6.5, 110.0)->get();

// Kecamatan dengan jarak
$districtsWithDistance = District::selectRaw("
    *, (
        6371 * acos(
            cos(radians(?)) * 
            cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + 
            sin(radians(?)) * 
            sin(radians(latitude))
        )
    ) AS distance
", [-6.9, 109.7, -6.9])
->whereNotNull('latitude')
->whereNotNull('longitude')
->orderBy('distance')
->get();
```

## Accessor dan Mutator

### Accessor

```php
// Dapatkan alamat lengkap
public function getFullAddressAttribute(): string
{
    return "{$this->name}, {$this->regency->name}, {$this->regency->province->name}";
}

// Dapatkan koordinat sebagai array
public function getCoordinatesAttribute(): ?array
{
    if ($this->latitude && $this->longitude) {
        return [$this->latitude, $this->longitude];
    }
    return null;
}

// Dapatkan kode pos unik
public function getPostalCodesAttribute(): array
{
    return $this->villages()
        ->whereNotNull('postal_code')
        ->pluck('postal_code')
        ->unique()
        ->values()
        ->toArray();
}
```

### Mutator

```php
// Normalisasi nama kecamatan
public function setNameAttribute($value): void
{
    $this->attributes['name'] = ucwords(strtolower($value));
}

// Validasi koordinat
public function setLatitudeAttribute($value): void
{
    if ($value !== null && ($value < -90 || $value > 90)) {
        throw new InvalidArgumentException('Latitude harus antara -90 dan 90');
    }
    $this->attributes['latitude'] = $value;
}
```

## Event dan Observer

```php
// District Observer
class DistrictObserver
{
    public function creating(District $district): void
    {
        // Validasi kode kecamatan
        if (!preg_match('/^\d{2}\.\d{2}\.\d{2}$/', $district->code)) {
            throw new InvalidArgumentException('Format kode kecamatan tidak valid');
        }
    }
    
    public function updating(District $district): void
    {
        // Log perubahan
        if ($district->isDirty('name')) {
            Log::info("District name changed: {$district->getOriginal('name')} -> {$district->name}");
        }
    }
}
```

## Validasi

```php
// Validasi input kecamatan
$rules = [
    'code' => 'required|string|size:8|regex:/^\d{2}\.\d{2}\.\d{2}$/',
    'regency_code' => 'required|string|size:5|exists:regencies,code',
    'province_code' => 'required|string|size:2|exists:provinces,code',
    'name' => 'required|string|max:255',
    'latitude' => 'nullable|numeric|between:-90,90',
    'longitude' => 'nullable|numeric|between:-180,180',
];

// Custom validation
Validator::extend('valid_district_hierarchy', function ($attribute, $value, $parameters, $validator) {
    $data = $validator->getData();
    
    // Validasi bahwa regency_code sesuai dengan province_code
    $regency = Regency::find($data['regency_code']);
    return $regency && $regency->province_code === $data['province_code'];
});
```

Model District menyediakan akses komprehensif ke data kecamatan Indonesia dengan fitur pencarian, filtering, dan analisis geografis yang lengkap.
