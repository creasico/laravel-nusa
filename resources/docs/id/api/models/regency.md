# Model Kabupaten/Kota

Model `Regency` merepresentasikan kabupaten dan kota Indonesia dan menyediakan akses ke semua 514 wilayah administratif tingkat kedua.

## Referensi Class

```php
namespace Creasi\Nusa\Models;

class Regency extends Model
{
    // Implementasi model
}
```

## Atribut

### Atribut Utama

| Atribut | Type | Deskripsi | Contoh |
|---------|------|-----------|--------|
| `code` | `string` | Kode kabupaten/kota dalam format xx.xx (Primary Key) | `"33.75"` |
| `province_code` | `string` | Kode provinsi induk (Foreign Key) | `"33"` |
| `name` | `string` | Nama kabupaten/kota dalam bahasa Indonesia | `"Kota Pekalongan"` |
| `latitude` | `float` | Lintang pusat geografis | `-6.8969497174987` |
| `longitude` | `float` | Bujur pusat geografis | `109.66208089654` |
| `coordinates` | `array\|null` | Koordinat polygon batas wilayah | `[[-6.789, 109.567], ...]` |

### Atribut Computed

| Atribut | Type | Deskripsi |
|---------|------|-----------|
| `postal_codes` | `array` | Semua kode pos dalam kabupaten/kota |
| `type` | `string` | Tipe wilayah ("Kabupaten" atau "Kota") |
| `is_city` | `boolean` | Apakah ini adalah kota |

## Relasi

### Belongs To

```php
// Dapatkan provinsi induk
$regency->province; // Model Province
```

### One-to-Many

```php
// Dapatkan semua kecamatan dalam kabupaten/kota
$regency->districts; // Collection<District>

// Dapatkan semua kelurahan/desa dalam kabupaten/kota
$regency->villages; // Collection<Village>

// Dengan eager loading
$regency = Regency::with(['districts', 'villages'])->find('33.75');
```

## Scope

### Pencarian

```php
// Pencarian berdasarkan nama
$regencies = Regency::search('semarang')->get();

// Pencarian dengan multiple terms
$regencies = Regency::search('kota semarang')->get();
```

### Filter Berdasarkan Tipe

```php
// Hanya kota
$cities = Regency::cities()->get();

// Hanya kabupaten
$regencies = Regency::regencies()->get();

// Filter berdasarkan provinsi
$centralJavaRegencies = Regency::where('province_code', '33')->get();
```

### Scope Geografis

```php
// Kabupaten/kota dalam radius tertentu
$nearbyRegencies = Regency::nearbyCoordinates(-6.8969, 109.6621, 50)->get();

// Kabupaten/kota dengan koordinat
$regenciesWithCoords = Regency::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();
```

## Metode

### Informasi Tipe

```php
$regency = Regency::find('33.75');

// Cek apakah ini kota
$isCity = $regency->isCity(); // true untuk "Kota Pekalongan"

// Dapatkan tipe
$type = $regency->getType(); // "Kota" atau "Kabupaten"

// Dapatkan nama tanpa prefix
$cleanName = $regency->getCleanName(); // "Pekalongan" dari "Kota Pekalongan"
```

### Informasi Geografis

```php
$regency = Regency::find('33.75');

// Dapatkan koordinat pusat
$coordinates = $regency->getCoordinates(); // [lat, lng]

// Hitung jarak ke titik lain
$distance = $regency->distanceTo(-6.9, 109.7); // dalam kilometer

// Dapatkan bounding box
$bounds = $regency->getBoundingBox(); // [min_lat, min_lng, max_lat, max_lng]
```

### Statistik

```php
$regency = Regency::find('33.75');

// Hitung jumlah kecamatan
$districtCount = $regency->districts()->count();

// Hitung jumlah kelurahan/desa
$villageCount = $regency->villages()->count();

// Dapatkan kode pos unik
$postalCodes = $regency->getPostalCodes();

// Statistik lengkap
$stats = $regency->getStatistics();
```

## Contoh Penggunaan

### Dasar

```php
use Creasi\Nusa\Models\Regency;

// Dapatkan semua kabupaten/kota
$regencies = Regency::all();

// Dapatkan kabupaten/kota berdasarkan kode
$regency = Regency::find('33.75');

// Pencarian kabupaten/kota
$searchResults = Regency::search('semarang')->get();
```

### Filter Berdasarkan Tipe

```php
// Dapatkan semua kota
$cities = Regency::cities()->get();

foreach ($cities as $city) {
    echo $city->name . "\n"; // Kota Semarang, Kota Surakarta, dll.
}

// Dapatkan semua kabupaten
$regencies = Regency::regencies()->get();

foreach ($regencies as $regency) {
    echo $regency->name . "\n"; // Kabupaten Semarang, Kabupaten Boyolali, dll.
}
```

### Dengan Relasi

```php
// Dapatkan kabupaten/kota dengan kecamatan
$regency = Regency::with('districts')->find('33.75');

foreach ($regency->districts as $district) {
    echo $district->name . "\n";
}

// Dapatkan kabupaten/kota dengan provinsi
$regency = Regency::with('province')->find('33.75');

echo "Lokasi: {$regency->name}, {$regency->province->name}";
```

### Pagination dan Sorting

```php
// Pagination sederhana
$regencies = Regency::paginate(20);

// Pagination dengan pencarian
$regencies = Regency::search('jawa')->paginate(10);

// Sorting berdasarkan nama
$regencies = Regency::orderBy('name')->get();

// Sorting berdasarkan tipe (kota dulu)
$regencies = Regency::orderByRaw("CASE WHEN name LIKE 'Kota%' THEN 0 ELSE 1 END, name")->get();
```

### Agregasi

```php
// Hitung kabupaten/kota per provinsi
$regencyCounts = Regency::selectRaw('province_code, COUNT(*) as count')
    ->groupBy('province_code')
    ->get();

// Hitung total kecamatan per kabupaten/kota
$stats = Regency::withCount('districts')->get();

foreach ($stats as $regency) {
    echo "{$regency->name}: {$regency->districts_count} kecamatan\n";
}

// Statistik kota vs kabupaten
$cityCount = Regency::cities()->count();
$regencyCount = Regency::regencies()->count();

echo "Kota: {$cityCount}, Kabupaten: {$regencyCount}";
```

### Query Geografis

```php
// Cari kabupaten/kota terdekat dari koordinat
$nearestRegencies = Regency::nearbyCoordinates(-6.9, 109.7, 100)
    ->limit(5)
    ->get();

// Kabupaten/kota dalam provinsi dengan jarak
$regenciesWithDistance = Regency::selectRaw("
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
->where('province_code', '33')
->whereNotNull('latitude')
->whereNotNull('longitude')
->orderBy('distance')
->get();
```

## Accessor dan Mutator

### Accessor

```php
// Dapatkan tipe wilayah
public function getTypeAttribute(): string
{
    return str_starts_with($this->name, 'Kota') ? 'Kota' : 'Kabupaten';
}

// Cek apakah ini kota
public function getIsCityAttribute(): bool
{
    return str_starts_with($this->name, 'Kota');
}

// Dapatkan nama bersih tanpa prefix
public function getCleanNameAttribute(): string
{
    return preg_replace('/^(Kota|Kabupaten)\s+/', '', $this->name);
}

// Dapatkan alamat lengkap
public function getFullAddressAttribute(): string
{
    return "{$this->name}, {$this->province->name}";
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
// Normalisasi nama kabupaten/kota
public function setNameAttribute($value): void
{
    // Pastikan format yang benar
    if (!str_starts_with($value, 'Kota') && !str_starts_with($value, 'Kabupaten')) {
        // Tentukan tipe berdasarkan konvensi atau data lain
        $value = 'Kabupaten ' . $value;
    }
    
    $this->attributes['name'] = $value;
}
```

## Scope Kustom

```php
// Scope untuk filter berdasarkan tipe
public function scopeCities($query)
{
    return $query->where('name', 'LIKE', 'Kota%');
}

public function scopeRegencies($query)
{
    return $query->where('name', 'LIKE', 'Kabupaten%');
}

// Scope untuk filter berdasarkan provinsi
public function scopeInProvince($query, string $provinceCode)
{
    return $query->where('province_code', $provinceCode);
}

// Scope untuk wilayah dengan koordinat
public function scopeWithCoordinates($query)
{
    return $query->whereNotNull('latitude')
                 ->whereNotNull('longitude');
}

// Scope untuk pencarian geografis
public function scopeNearbyCoordinates($query, float $lat, float $lng, int $radiusKm = 50)
{
    return $query->selectRaw("
        *, (
            6371 * acos(
                cos(radians(?)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(latitude))
            )
        ) AS distance
    ", [$lat, $lng, $lat])
    ->having('distance', '<', $radiusKm)
    ->orderBy('distance');
}
```

Model Regency menyediakan akses komprehensif ke data kabupaten dan kota Indonesia dengan fitur pencarian, filtering berdasarkan tipe, dan analisis geografis yang lengkap.
