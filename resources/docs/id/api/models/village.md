# Model Desa/Kelurahan

Model `Village` merepresentasikan desa dan kelurahan di Indonesia dan menyediakan akses ke semua 83.762 wilayah administratif tingkat keempat.

## Referensi Kelas

```php
namespace Creasi\Nusa\Models;

class Village extends Model
{
    // Implementasi model
}
```

## Atribut

### Atribut Utama

| Atribut | Tipe | Deskripsi | Contoh |
|-----------|------|-------------|---------|
| `code` | `string` | Kode desa/kelurahan dalam format xx.xx.xx.xxxx (Kunci Utama) | `"33.75.01.1002"` |
| `district_code` | `string` | Kode kecamatan induk (Kunci Asing) | `"33.75.01"` |
| `regency_code` | `string` | Kode kabupaten/kota induk (Kunci Asing) | `"33.75"` |
| `province_code` | `string` | Kode provinsi induk (Kunci Asing) | `"33"` |
| `name` | `string` | Nama desa/kelurahan dalam bahasa Indonesia | `"Medono"` |
| `latitude` | `float\|null` | Lintang pusat geografis | `-6.8969497174987` |
| `longitude` | `float\|null` | Bujur pusat geografis | `109.66208089654` |
| `postal_code` | `string\|null` | Kode pos 5 digit | `"51111"` |

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
// Relasi District
public function district(): BelongsTo
{
    return $this->belongsTo(District::class, 'district_code', 'code');
}

// Relasi Regency
public function regency(): BelongsTo
{
    return $this->belongsTo(Regency::class, 'regency_code', 'code');
}

// Relasi Province
public function province(): BelongsTo
{
    return $this->belongsTo(Province::class, 'province_code', 'code');
}
```

## Scope

### Scope Pencarian

```php
// Cari berdasarkan nama atau kode (tidak peka huruf besar/kecil)
Village::search('medono')->get();
Village::search('33.75.01.1002')->first();
Village::search('desa')->get();
```

## Contoh Penggunaan

### Kueri Dasar

```php
use Creasi\Nusa\Models\Village;

// Dapatkan desa/kelurahan dengan paginasi (direkomendasikan untuk kinerja)
$villages = Village::paginate(50);

// Temukan desa/kelurahan tertentu
$village = Village::find('33.75.01.1002');

// Cari desa/kelurahan
$medonos = Village::search('medono')->get();
$villages = Village::search('kelurahan')->get();
```

### Kueri Hirarkis

```php
// Dapatkan desa/kelurahan di kecamatan tertentu
$districtVillages = Village::where('district_code', '33.75.01')->get();

// Dapatkan desa/kelurahan di kabupaten/kota tertentu
$regencyVillages = Village::where('regency_code', '33.75')->get();

// Dapatkan desa/kelurahan di provinsi tertentu
$provinceVillages = Village::where('province_code', '33')->paginate(100);

// Dapatkan desa/kelurahan dengan hierarki lengkapnya
$villages = Village::with(['district', 'regency', 'province'])->get();
```

### Kueri Kode Pos

```php
// Temukan desa/kelurahan berdasarkan kode pos
$villages = Village::where('postal_code', '51111')->get();

// Temukan desa/kelurahan dengan kode pos yang dimulai dengan 511
$villages = Village::where('postal_code', 'like', '511%')->get();

// Dapatkan desa/kelurahan tanpa kode pos
$villagesWithoutPostal = Village::whereNull('postal_code')->get();

// Kelompokkan desa/kelurahan berdasarkan kode pos
$villagesByPostal = Village::whereNotNull('postal_code')
    ->get()
    ->groupBy('postal_code');
```

### Dengan Relasi

```php
// Muat desa/kelurahan dengan hierarki lengkap
$village = Village::with(['district', 'regency', 'province'])->find('33.75.01.1002');

// Muat kolom tertentu dari relasi
$villages = Village::with([
    'district:code,name',
    'regency:code,name',
    'province:code,name'
])->get();

// Dapatkan hierarki alamat lengkap
$village = Village::with(['district', 'regency', 'province'])->find('33.75.01.1002');
$fullAddress = implode(', ', [
    $village->name,
    $village->district->name,
    $village->regency->name,
    $village->province->name,
    $village->postal_code
]);
```

### Operasi Geografis

```php
// Temukan desa/kelurahan dalam rentang koordinat
$villages = Village::whereBetween('latitude', [-7, -6])
    ->whereBetween('longitude', [109, 111])
    ->get();

// Dapatkan desa/kelurahan dengan koordinat
$villagesWithCoords = Village::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();

// Temukan desa/kelurahan terdekat dari koordinat
function findNearestVillage($lat, $lon) {
    $villages = Village::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();
        
    $nearest = null;
    $minDistance = PHP_FLOAT_MAX;
    
    foreach ($villages as $village) {
        $distance = calculateDistance($lat, $lon, $village->latitude, $village->longitude);
        if ($distance < $minDistance) {
            $minDistance = $distance;
            $nearest = $village;
        }
        
    }
    
    return $nearest;
}
```

## Struktur Kode

### Kode Desa/Kelurahan

Kode desa/kelurahan mengikuti pola: `XX.YY.ZZ.VVVV`
- `XX` = Kode provinsi (2 digit)
- `YY` = Kode kabupaten/kota di dalam provinsi (2 digit)
- `ZZ` = Kode kecamatan di dalam kabupaten/kota (2 digit)
- `VVVV` = Kode desa/kelurahan di dalam kecamatan (4 digit)

```php
$village = Village::find('33.75.01.1002');
echo $village->province_code; // "33" (Jawa Tengah)
echo $village->regency_code;  // "33.75" (Kota Pekalongan)
echo $village->district_code; // "33.75.01" (Pekalongan Barat)
echo explode('.', $village->code)[3]; // "1002" (Desa/Kelurahan di dalam kecamatan)
```

### Membangun Alamat Lengkap

```php
function buildFullAddress($villageCode) {
    $village = Village::with(['district', 'regency', 'province'])
        ->find($villageCode);
        
    if (!$village) {
        return null;
    }
    
    return [
        'village' => $village->name,
        'district' => $village->district->name,
        'regency' => $village->regency->name,
        'province' => $village->province->name,
        'postal_code' => $village->postal_code,
        'full_address' => implode(', ', array_filter([
            $village->name,
            $village->district->name,
            $village->regency->name,
            $village->province->name,
            $village->postal_code
        ]))
    ];
}
```

## Operasi Kode Pos

```php
// Dapatkan semua kode pos di provinsi
$postalCodes = Village::where('province_code', '33')
    ->whereNotNull('postal_code')
    ->distinct()
    ->pluck('postal_code')
    ->sort()
    ->values();

// Temukan desa/kelurahan yang berbagi kode pos
$sharedPostalVillages = Village::where('postal_code', '51111')->get();

// Validasi kode pos untuk desa/kelurahan
function validatePostalCode($villageCode, $postalCode) {
    $village = Village::find($villageCode);
    return $village && $village->postal_code === $postalCode;
}

// Dapatkan statistik kode pos
$postalStats = Village::selectRaw('postal_code, count(*) as village_count')
    ->whereNotNull('postal_code')
    ->groupBy('postal_code')
    ->orderBy('village_count', 'desc')
    ->get();
```

## Tips Kinerja

### Kueri yang Efisien

```php
// Baik: Selalu gunakan paginasi untuk desa/kelurahan
$villages = Village::paginate(50);

// Baik: Filter berdasarkan wilayah induk terlebih dahulu
$villages = Village::where('district_code', '33.75.01')
    ->select('code', 'name', 'postal_code')
    ->get();

// Baik: Gunakan kolom tertentu
$villages = Village::select('code', 'name', 'postal_code')->get();

// Hindari: Memuat semua desa/kelurahan sekaligus
$villages = Village::all(); // 83.762 catatan - akan menyebabkan masalah memori!
```

### Chunking untuk Operasi Besar

```php
// Proses desa/kelurahan dalam potongan
Village::chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Proses setiap desa/kelurahan
        processVillage($village);
    }
});

// Potongan dengan pemfilteran
Village::where('province_code', '33')
    ->chunk(1000, function ($villages) {
        // Proses desa/kelurahan Jawa Tengah
    });
```

### Strategi Caching

```php
use Illuminate\Support\Facades\Cache;

// Cache desa/kelurahan berdasarkan kecamatan
function getVillagesByDistrict($districtCode) {
    $cacheKey = "villages.district.{$districtCode}";
    
    return Cache::remember($cacheKey, 3600, function () use ($districtCode) {
        return Village::where('district_code', $districtCode)
            ->orderBy('name')
            ->get(['code', 'name', 'postal_code']);
    });
}

// Cache desa/kelurahan dengan hierarki lengkap
function getVillageWithHierarchy($code) {
    $cacheKey = "village.hierarchy.{$code}";
    
    return Cache::remember($cacheKey, 3600, function () use ($code) {
        return Village::with(['district', 'regency', 'province'])
            ->find($code);
    });
}
```

## Validasi

### Validasi Formulir

```php
// Validasi desa/kelurahan ada dan termasuk dalam kecamatan
'village_code' => [
    'required',
    'exists:nusa.villages,code',
    function ($attribute, $value, $fail) {
        $village = Village::find($value);
        if (!$village || $village->district_code !== request('district_code')) {
            $fail('Desa/kelurahan yang dipilih tidak valid untuk kecamatan ini.');
        }
    }
]

// Validasi hierarki alamat lengkap
'address' => [
    'required',
    'array',
    function ($attribute, $value, $fail) {
        $village = Village::find($value['village_code']);
        if (!$village ||
            $village->district_code !== $value['district_code'] ||
            $village->regency_code !== $value['regency_code'] ||
            $village->province_code !== $value['province_code']) {
            $fail('Komponen alamat tidak konsisten.');
        }
    }
]
```

### Aturan Validasi Kustom

```php
use Illuminate\Contracts\Validation\Rule;

class ValidVillageForDistrict implements Rule
{
    private $districtCode;
    
    public function __construct($districtCode)
    {
        $this->districtCode = $districtCode;
    }
    
    public function passes($attribute, $value)
    {
        $village = Village::find($value);
        return $village && $village->district_code === $this->districtCode;
    }
    
    public function message()
    {
        return 'Desa/kelurahan yang dipilih bukan milik kecamatan yang ditentukan.';
    }
}

// Penggunaan
'village_code' => ['required', new ValidVillageForDistrict($districtCode)]
```

## Skema Database

```sql
CREATE TABLE villages (
    code VARCHAR(13) PRIMARY KEY,
    district_code VARCHAR(8) NOT NULL,
    regency_code VARCHAR(5) NOT NULL,
    province_code VARCHAR(2) NOT NULL,
    name VARCHAR(255) NOT NULL,
    latitude DOUBLE NULL,
    longitude DOUBLE NULL,
    postal_code VARCHAR(5) NULL,
    FOREIGN KEY (district_code) REFERENCES districts(code),
    FOREIGN KEY (regency_code) REFERENCES regencies(code),
    FOREIGN KEY (province_code) REFERENCES provinces(code)
);

-- Indeks
CREATE INDEX idx_villages_district ON villages(district_code);
CREATE INDEX idx_villages_regency ON villages(regency_code);
CREATE INDEX idx_villages_province ON villages(province_code);
CREATE INDEX idx_villages_postal ON villages(postal_code);
CREATE INDEX idx_villages_name ON villages(name);
CREATE INDEX idx_villages_coordinates ON villages(latitude, longitude);
```

## Konstanta

```php
// Jumlah total desa/kelurahan di Indonesia
Village::count(); // 83.762

// Rata-rata desa/kelurahan per kecamatan
$avgVillagesPerDistrict = Village::count() / District::count(); // ~11.5

// Desa/kelurahan dengan kode pos
$villagesWithPostal = Village::whereNotNull('postal_code')->count();
```

## Model Terkait

- **[Model Province](/id/api/models/province)** - Divisi administratif kakek-buyut
- **[Model Regency](/id/api/models/regency)** - Divisi administratif kakek
- **[Model District](/id/api/models/district)** - Divisi administratif induk
- **[Model Address](/id/api/models/address)** - Manajemen alamat dengan referensi desa/kelurahan