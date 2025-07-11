# Model Kabupaten/Kota

Model `Regency` merepresentasikan kabupaten dan kota di Indonesia dan menyediakan akses ke semua 514 wilayah administratif tingkat kedua.

## Referensi Kelas

```php
namespace Creasi\Nusa\Models;

class Regency extends Model
{
    // Implementasi model
}
```

## Atribut

### Atribut Utama

| Atribut | Tipe | Deskripsi | Contoh |
|-----------|------|-------------|---------|
| `code` | `string` | Kode kabupaten/kota dalam format xx.xx (Kunci Utama) | `"33.75"` |
| `province_code` | `string` | Kode provinsi induk (Kunci Asing) | `"33"` |
| `name` | `string` | Nama kabupaten/kota dalam bahasa Indonesia | `"Kota Pekalongan"` |
| `latitude` | `float` | Lintang pusat geografis | `-6.8969497174987` |
| `longitude` | `float` | Bujur pusat geografis | `109.66208089654` |
| `coordinates` | `array\|null` | Koordinat poligon batas | `[[-6.789, 109.567], ...]` |

### Atribut Terkomputasi

| Atribut | Tipe | Deskripsi |
|-----------|------|-------------|
| `postal_codes` | `array` | Semua kode pos di dalam kabupaten/kota |

## Relasi

### Belongs To

```php
// Dapatkan provinsi induk
$regency->province; // Model Province
```

### One-to-Many

```php
// Dapatkan semua kecamatan di kabupaten/kota
$regency->districts; // Collection<District>

// Dapatkan semua desa/kelurahan di kabupaten/kota
$regency->villages; // Collection<Village>
```

### Metode Relasi

```php
// Relasi Province
public function province(): BelongsTo
{
    return $this->belongsTo(Province::class, 'province_code', 'code');
}

// Relasi Districts
public function districts(): HasMany
{
    return $this->hasMany(District::class, 'regency_code', 'code');
}

// Relasi Villages (melalui kecamatan)
public function villages(): HasMany
{
    return $this->hasMany(Village::class, 'regency_code', 'code');
}
```

## Scope

### Scope Pencarian

```php
// Cari berdasarkan nama atau kode (tidak peka huruf besar/kecil)
Regency::search('semarang')->get();
Regency::search('33.75')->first();
Regency::search('kota')->get();
```

## Contoh Penggunaan

### Kueri Dasar

```php
use Creasi\Nusa\Models\Regency;

// Dapatkan semua kabupaten/kota
$regencies = Regency::all();

// Temukan kabupaten/kota tertentu
$pekalongan = Regency::find('33.75');

// Cari kabupaten/kota
$semarangRegencies = Regency::search('semarang')->get();
$cities = Regency::search('kota')->get();
```

### Kueri Berbasis Provinsi

```php
// Dapatkan kabupaten/kota di provinsi tertentu
$centralJavaRegencies = Regency::where('province_code', '33')->get();

// Dapatkan kabupaten/kota dengan provinsinya
$regencies = Regency::with('province')->get();

// Dapatkan kabupaten/kota di beberapa provinsi
$javaRegencies = Regency::whereIn('province_code', ['32', '33', '34'])->get();
```

### Dengan Relasi

```php
// Muat kabupaten/kota dengan semua relasi
$regency = Regency::with(['province', 'districts', 'villages'])->find('33.75');

// Muat kolom tertentu dari relasi
$regencies = Regency::with([
    'province:code,name',
    'districts:code,regency_code,name'
])->get();

// Hitung catatan terkait
$regencies = Regency::withCount(['districts', 'villages'])->get();
```

### Penyaringan dan Pengurutan

```php
// Dapatkan hanya kota (mengandung "Kota")
$cities = Regency::where('name', 'like', '%Kota%')->get();

// Dapatkan hanya kabupaten (mengandung "Kabupaten")
$kabupaten = Regency::where('name', 'like', '%Kabupaten%')->get();

// Urutkan berdasarkan nama
$regencies = Regency::orderBy('name')->get();

// Dapatkan kabupaten/kota dengan koordinat
$regenciesWithCoords = Regency::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();
```

### Operasi Geografis

```php
// Temukan kabupaten/kota dalam rentang koordinat
$regencies = Regency::whereBetween('latitude', [-7, -6])
    ->whereBetween('longitude', [109, 111])
    ->get();

// Dapatkan batas kabupaten/kota untuk pemetaan
$regency = Regency::find('33.75');
if ($regency->coordinates) {
    $geoJson = [
        'type' => 'Feature',
        'properties' => [
            'name' => $regency->name,
            'code' => $regency->code,
            'province' => $regency->province->name
        ],
        'geometry' => [
            'type' => 'Polygon',
            'coordinates' => [$regency->coordinates]
        ]
    ];
}
```

## Struktur Kode

### Kode Kabupaten/Kota

Kode kabupaten/kota mengikuti pola: `XX.YY`
- `XX` = Kode provinsi (2 digit)
- `YY` = Kode kabupaten/kota di dalam provinsi (2 digit)

```php
$regency = Regency::find('33.75');
echo $regency->province_code; // "33" (Jawa Tengah)
echo explode('.', $regency->code)[1]; // "75" (Kota Pekalongan di Jawa Tengah)
```

### Jenis Kabupaten/Kota

```php
// Bedakan antara kota dan kabupaten
function getRegencyType($regency) {
    if (str_contains($regency->name, 'Kota')) {
        return 'Kota';
    } elseif (str_contains($regency->name, 'Kabupaten')) {
        return 'Kabupaten';
    }
    return 'Tidak Diketahui';
}

// Dapatkan semua kota
$cities = Regency::where('name', 'like', '%Kota%')->get();

// Dapatkan semua kabupaten
$kabupaten = Regency::where('name', 'like', '%Kabupaten%')->get();
```

## Operasi Kode Pos

```php
$regency = Regency::find('33.75');

// Dapatkan semua kode pos di kabupaten/kota
$postalCodes = $regency->postal_codes;
echo "Kode pos di {$regency->name}: " . implode(', ', $postalCodes);

// Temukan kabupaten/kota berdasarkan rentang kode pos
$regencies = Regency::whereHas('villages', function ($query) {
    $query->where('postal_code', 'like', '511%');
})->get();
```

## Agregasi dan Statistik

```php
// Hitung kecamatan per kabupaten/kota
$regenciesWithCounts = Regency::withCount('districts')->get();

// Dapatkan kabupaten/kota dengan kecamatan terbanyak
$topRegency = Regency::withCount('districts')
    ->orderBy('districts_count', 'desc')
    ->first();

// Kelompokkan berdasarkan provinsi
$regenciesByProvince = Regency::with('province')
    ->get()
    ->groupBy('province.name');

// Statistik berdasarkan jenis
$cityCount = Regency::where('name', 'like', '%Kota%')->count();
$regencyCount = Regency::where('name', 'like', '%Kabupaten%')->count();
```

## Tips Kinerja

### Kueri yang Efisien

```php
// Baik: Pilih kolom tertentu
$regencies = Regency::select('code', 'name', 'province_code')->get();

// Baik: Gunakan paginasi
$regencies = Regency::paginate(25);

// Baik: Filter berdasarkan provinsi terlebih dahulu
$regencies = Regency::where('province_code', '33')
    ->with('districts')
    ->get();

// Hindari: Memuat semua kabupaten/kota dengan semua relasi
$regencies = Regency::with(['province', 'districts.villages'])->get();
```

### Strategi Caching

```php
use Illuminate\Support\Facades\Cache;

// Cache kabupaten/kota berdasarkan provinsi
function getRegenciesByProvince($provinceCode) {
    $cacheKey = "regencies.province.{$provinceCode}";
    
    return Cache::remember($cacheKey, 3600, function () use ($provinceCode) {
        return Regency::where('province_code', $provinceCode)
            ->orderBy('name')
            ->get(['code', 'name']);
    });
}

// Cache kabupaten/kota dengan relasi
function getRegencyWithDetails($code) {
    $cacheKey = "regency.details.{$code}";
    
    return Cache::remember($cacheKey, 3600, function () use ($code) {
        return Regency::with(['province', 'districts'])
            ->find($code);
    });
}
```

## Validasi

### Validasi Formulir

```php
// Validasi kabupaten/kota ada dan termasuk dalam provinsi
'regency_code' => [
    'required',
    'exists:nusa.regencies,code',
    function ($attribute, $value, $fail) {
        $regency = Regency::find($value);
        if (!$regency || $regency->province_code !== request('province_code')) {
            $fail('Kabupaten/kota yang dipilih tidak valid untuk provinsi ini.');
        }
    }
]
```

### Aturan Validasi Kustom

```php
use Illuminate\Contracts\Validation\Rule;

class ValidRegencyForProvince implements Rule
{
    private $provinceCode;
    
    public function __construct($provinceCode)
    {
        $this->provinceCode = $provinceCode;
    }
    
    public function passes($attribute, $value)
    {
        $regency = Regency::find($value);
        return $regency && $regency->province_code === $this->provinceCode;
    }
    
    public function message()
    {
        return 'Kabupaten/kota yang dipilih bukan milik provinsi yang ditentukan.';
    }
}

// Penggunaan
'regency_code' => ['required', new ValidRegencyForProvince($provinceCode)]
```

## Skema Database

```sql
CREATE TABLE regencies (
    code VARCHAR(5) PRIMARY KEY,
    province_code VARCHAR(2) NOT NULL,
    name VARCHAR(255) NOT NULL,
    latitude DOUBLE NULL,
    longitude DOUBLE NULL,
    coordinates JSON NULL,
    FOREIGN KEY (province_code) REFERENCES provinces(code)
);

-- Indeks
CREATE INDEX idx_regencies_province ON regencies(province_code);
CREATE INDEX idx_regencies_name ON regencies(name);
CREATE INDEX idx_regencies_coordinates ON regencies(latitude, longitude);
```

## Konstanta

```php
// Jumlah total kabupaten/kota di Indonesia
Regency::count(); // 514

// Rincian berdasarkan jenis
$cities = Regency::where('name', 'like', '%Kota%')->count(); // ~98 kota
$kabupaten = Regency::where('name', 'like', '%Kabupaten%')->count(); // ~416 kabupaten
```

## Model Terkait

- **[Model Province](/id/api/models/province)** - Divisi administratif induk
- **[Model District](/id/api/models/district)** - Divisi administratif anak
- **[Model Village](/id/api/models/village)** - Divisi administratif cucu
- **[Model Address](/id/api/models/address)** - Manajemen alamat dengan referensi kabupaten/kota