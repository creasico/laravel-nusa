# Model Kelurahan/Desa

Model `Village` merepresentasikan kelurahan dan desa Indonesia dan menyediakan akses ke semua 83,762 wilayah administratif tingkat keempat.

## Referensi Class

```php
namespace Creasi\Nusa\Models;

class Village extends Model
{
    // Implementasi model
}
```

## Atribut

### Atribut Utama

| Atribut | Type | Deskripsi | Contoh |
|---------|------|-----------|--------|
| `code` | `string` | Kode kelurahan/desa dalam format xx.xx.xx.xxxx (Primary Key) | `"33.75.01.1002"` |
| `district_code` | `string` | Kode kecamatan induk (Foreign Key) | `"33.75.01"` |
| `regency_code` | `string` | Kode kabupaten/kota induk (Foreign Key) | `"33.75"` |
| `province_code` | `string` | Kode provinsi induk (Foreign Key) | `"33"` |
| `name` | `string` | Nama kelurahan/desa dalam bahasa Indonesia | `"Medono"` |
| `latitude` | `float\|null` | Lintang pusat geografis | `-6.8969497174987` |
| `longitude` | `float\|null` | Bujur pusat geografis | `109.66208089654` |
| `postal_code` | `string\|null` | Kode pos 5 digit | `"51111"` |

### Atribut Computed

| Atribut | Type | Deskripsi |
|---------|------|-----------|
| `type` | `string` | Tipe wilayah ("Kelurahan" atau "Desa") |
| `is_urban` | `boolean` | Apakah ini adalah kelurahan (urban) |
| `full_address` | `string` | Alamat lengkap dengan hierarki |

## Relasi

### Belongs To

```php
// Dapatkan kecamatan induk
$village->district; // Model District

// Dapatkan kabupaten/kota induk
$village->regency; // Model Regency

// Dapatkan provinsi induk
$village->province; // Model Province
```

### Metode Relasi

```php
// Relasi kecamatan
public function district(): BelongsTo
{
    return $this->belongsTo(District::class, 'district_code', 'code');
}

// Relasi kabupaten/kota
public function regency(): BelongsTo
{
    return $this->belongsTo(Regency::class, 'regency_code', 'code');
}

// Relasi provinsi
public function province(): BelongsTo
{
    return $this->belongsTo(Province::class, 'province_code', 'code');
}
```

## Scope

### Pencarian

```php
// Pencarian berdasarkan nama
$villages = Village::search('medono')->get();

// Pencarian dengan multiple terms
$villages = Village::search('kelurahan medono')->get();
```

### Filter Berdasarkan Tipe

```php
// Hanya kelurahan (urban)
$kelurahan = Village::urban()->get();

// Hanya desa (rural)
$desa = Village::rural()->get();

// Filter berdasarkan kode pos
$villages = Village::where('postal_code', '51111')->get();
```

### Filter Berdasarkan Wilayah

```php
// Kelurahan/desa dalam kecamatan tertentu
$villages = Village::where('district_code', '33.75.01')->get();

// Kelurahan/desa dalam kabupaten/kota tertentu
$villages = Village::where('regency_code', '33.75')->get();

// Kelurahan/desa dalam provinsi tertentu
$villages = Village::where('province_code', '33')->get();
```

### Scope Geografis

```php
// Kelurahan/desa dalam radius tertentu
$nearbyVillages = Village::nearbyCoordinates(-6.8969, 109.6621, 5)->get();

// Kelurahan/desa dengan koordinat
$villagesWithCoords = Village::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();

// Kelurahan/desa dengan kode pos
$villagesWithPostal = Village::whereNotNull('postal_code')->get();
```

## Metode

### Informasi Tipe

```php
$village = Village::find('33.75.01.1002');

// Cek apakah ini kelurahan
$isUrban = $village->isUrban(); // true untuk kelurahan

// Dapatkan tipe
$type = $village->getType(); // "Kelurahan" atau "Desa"

// Dapatkan nama bersih tanpa prefix
$cleanName = $village->getCleanName(); // "Medono" dari "Kelurahan Medono"
```

### Informasi Geografis

```php
$village = Village::find('33.75.01.1002');

// Dapatkan koordinat pusat
$coordinates = $village->getCoordinates(); // [lat, lng]

// Hitung jarak ke titik lain
$distance = $village->distanceTo(-6.9, 109.7); // dalam kilometer

// Validasi kode pos
$isValidPostal = $village->validatePostalCode('51111');
```

### Alamat Lengkap

```php
$village = Village::find('33.75.01.1002');

// Dapatkan alamat lengkap
$fullAddress = $village->getFullAddress();
// "Medono, Pekalongan Barat, Kota Pekalongan, Jawa Tengah"

// Dapatkan alamat dengan kode pos
$addressWithPostal = $village->getFullAddressWithPostal();
// "Medono, Pekalongan Barat, Kota Pekalongan, Jawa Tengah 51111"
```

## Contoh Penggunaan

### Dasar

```php
use Creasi\Nusa\Models\Village;

// Dapatkan semua kelurahan/desa
$villages = Village::all();

// Dapatkan kelurahan/desa berdasarkan kode
$village = Village::find('33.75.01.1002');

// Pencarian kelurahan/desa
$searchResults = Village::search('medono')->get();
```

### Filter Berdasarkan Tipe

```php
// Dapatkan semua kelurahan
$kelurahan = Village::urban()->get();

foreach ($kelurahan as $village) {
    echo $village->name . "\n"; // Kelurahan Medono, Kelurahan Panjang, dll.
}

// Dapatkan semua desa
$desa = Village::rural()->get();

foreach ($desa as $village) {
    echo $village->name . "\n"; // Desa Sumberejo, Desa Karanganyar, dll.
}
```

### Dengan Relasi

```php
// Dapatkan kelurahan/desa dengan kecamatan
$village = Village::with('district')->find('33.75.01.1002');

echo "Lokasi: {$village->name}, {$village->district->name}";

// Dapatkan kelurahan/desa dengan semua hierarki
$village = Village::with(['district.regency.province'])->find('33.75.01.1002');

echo "Alamat lengkap: {$village->name}, {$village->district->name}, {$village->district->regency->name}, {$village->district->regency->province->name}";
```

### Pagination dan Sorting

```php
// Pagination sederhana
$villages = Village::paginate(100);

// Pagination dengan pencarian
$villages = Village::search('kelurahan')->paginate(50);

// Sorting berdasarkan nama
$villages = Village::orderBy('name')->get();

// Sorting berdasarkan tipe (kelurahan dulu)
$villages = Village::orderByRaw("CASE WHEN name LIKE 'Kelurahan%' THEN 0 ELSE 1 END, name")->get();
```

### Filter Berdasarkan Kode Pos

```php
// Kelurahan/desa dengan kode pos tertentu
$villages = Village::where('postal_code', '51111')->get();

// Kelurahan/desa dalam range kode pos
$jakartaVillages = Village::whereBetween('postal_code', ['10000', '19999'])->get();

// Kelurahan/desa tanpa kode pos
$villagesWithoutPostal = Village::whereNull('postal_code')->get();

echo "Kelurahan/desa tanpa kode pos: " . $villagesWithoutPostal->count();
```

### Query Geografis

```php
// Cari kelurahan/desa terdekat dari koordinat
$nearestVillages = Village::nearbyCoordinates(-6.9, 109.7, 10)
    ->limit(10)
    ->get();

// Kelurahan/desa dalam kecamatan dengan jarak
$villagesWithDistance = Village::selectRaw("
    *, (
        6371 * acos(
            cos(radians(?)) * 
            cos(radians(COALESCE(latitude, 0))) * 
            cos(radians(COALESCE(longitude, 0)) - radians(?)) + 
            sin(radians(?)) * 
            sin(radians(COALESCE(latitude, 0)))
        )
    ) AS distance
", [-6.9, 109.7, -6.9])
->where('district_code', '33.75.01')
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
    return str_starts_with($this->name, 'Kelurahan') ? 'Kelurahan' : 'Desa';
}

// Cek apakah ini kelurahan
public function getIsUrbanAttribute(): bool
{
    return str_starts_with($this->name, 'Kelurahan');
}

// Dapatkan nama bersih tanpa prefix
public function getCleanNameAttribute(): string
{
    return preg_replace('/^(Kelurahan|Desa)\s+/', '', $this->name);
}

// Dapatkan alamat lengkap
public function getFullAddressAttribute(): string
{
    $parts = [
        $this->name,
        $this->district?->name,
        $this->regency?->name,
        $this->province?->name
    ];
    
    return implode(', ', array_filter($parts));
}

// Dapatkan koordinat sebagai array
public function getCoordinatesAttribute(): ?array
{
    if ($this->latitude && $this->longitude) {
        return [$this->latitude, $this->longitude];
    }
    return null;
}
```

### Mutator

```php
// Normalisasi nama kelurahan/desa
public function setNameAttribute($value): void
{
    // Pastikan format yang benar
    if (!str_starts_with($value, 'Kelurahan') && !str_starts_with($value, 'Desa')) {
        // Tentukan tipe berdasarkan konvensi atau data lain
        $value = 'Desa ' . $value;
    }
    
    $this->attributes['name'] = $value;
}

// Validasi kode pos
public function setPostalCodeAttribute($value): void
{
    if ($value !== null && !preg_match('/^\d{5}$/', $value)) {
        throw new InvalidArgumentException('Kode pos harus 5 digit angka');
    }
    
    $this->attributes['postal_code'] = $value;
}
```

## Scope Kustom

```php
// Scope untuk filter berdasarkan tipe
public function scopeUrban($query)
{
    return $query->where('name', 'LIKE', 'Kelurahan%');
}

public function scopeRural($query)
{
    return $query->where('name', 'NOT LIKE', 'Kelurahan%');
}

// Scope untuk filter berdasarkan wilayah
public function scopeInDistrict($query, string $districtCode)
{
    return $query->where('district_code', $districtCode);
}

public function scopeInRegency($query, string $regencyCode)
{
    return $query->where('regency_code', $regencyCode);
}

public function scopeInProvince($query, string $provinceCode)
{
    return $query->where('province_code', $provinceCode);
}

// Scope untuk wilayah dengan data lengkap
public function scopeWithCoordinates($query)
{
    return $query->whereNotNull('latitude')
                 ->whereNotNull('longitude');
}

public function scopeWithPostalCode($query)
{
    return $query->whereNotNull('postal_code');
}
```

Model Village menyediakan akses komprehensif ke data kelurahan dan desa Indonesia dengan fitur pencarian, filtering berdasarkan tipe, validasi kode pos, dan analisis geografis yang lengkap.
