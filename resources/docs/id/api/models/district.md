# Model Kecamatan

Model `District` merepresentasikan kecamatan di Indonesia dan menyediakan akses ke semua 7.285 wilayah administratif tingkat ketiga.

## Referensi Kelas

```php
namespace Creasi\Nusa\Models;

class District extends Model
{
    // Implementasi model
}
```

## Atribut

### Atribut Utama

| Atribut | Tipe | Deskripsi | Contoh |
|-----------|------|-------------|---------|
| `code` | `string` | Kode kecamatan dalam format xx.xx.xx (Kunci Utama) | `"33.75.01"` |
| `regency_code` | `string` | Kode kabupaten/kota induk (Kunci Asing) | `"33.75"` |
| `province_code` | `string` | Kode provinsi induk (Kunci Asing) | `"33"` |
| `name` | `string` | Nama kecamatan dalam bahasa Indonesia | `"Pekalongan Barat"` |
| `latitude` | `float\|null` | Lintang pusat geografis | `-6.8969497174987` |
| `longitude` | `float\|null` | Bujur pusat geografis | `109.66208089654` |

### Atribut Terkomputasi

| Atribut | Tipe | Deskripsi |
|-----------|------|-------------|
| `postal_codes` | `array` | Semua kode pos di dalam kecamatan |

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
// Dapatkan semua desa/kelurahan di kecamatan
$district->villages; // Collection<Village>
```

### Metode Relasi

```php
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

// Relasi Villages
public function villages(): HasMany
{
    return $this->hasMany(Village::class, 'district_code', 'code');
}
```

## Scope

### Scope Pencarian

```php
// Cari berdasarkan nama atau kode (tidak peka huruf besar/kecil)
District::search('pekalongan')->get();
District::search('33.75.01')->first();
District::search('barat')->get();
```

## Contoh Penggunaan

### Kueri Dasar

```php
use Creasi\Nusa\Models\District;

// Dapatkan semua kecamatan (gunakan paginasi untuk kinerja)
$districts = District::paginate(50);

// Temukan kecamatan tertentu
$district = District::find('33.75.01');

// Cari kecamatan
$pekalongans = District::search('pekalongan')->get();
$westDistricts = District::search('barat')->get();
```

### Kueri Hirarkis

```php
// Dapatkan kecamatan di kabupaten/kota tertentu
$regencyDistricts = District::where('regency_code', '33.75')->get();

// Dapatkan kecamatan di provinsi tertentu
$provinceDistricts = District::where('province_code', '33')->get();

// Dapatkan kecamatan dengan wilayah induknya
$districts = District::with(['regency', 'province'])->get();

// Dapatkan kecamatan di beberapa kabupaten/kota
$districts = District::whereIn('regency_code', ['33.75', '33.76', '33.77'])->get();
```

### Dengan Relasi

```php
// Muat kecamatan dengan semua relasi
$district = District::with(['province', 'regency', 'villages'])->find('33.75.01');

// Muat kolom tertentu dari relasi
$districts = District::with([
    'province:code,name',
    'regency:code,name',
    'villages:code,district_code,name,postal_code'
])->get();

// Hitung catatan terkait
$districts = District::withCount('villages')->get();
```

### Penyaringan dan Pengurutan

```php
// Urutkan berdasarkan nama
$districts = District::orderBy('name')->get();

// Dapatkan kecamatan dengan koordinat
$districtsWithCoords = District::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();

// Filter berdasarkan pola nama
$centralDistricts = District::where('name', 'like', '%Tengah%')->get();
$northDistricts = District::where('name', 'like', '%Utara%')->get();
```

### Operasi Geografis

```php
// Temukan kecamatan dalam rentang koordinat
$districts = District::whereBetween('latitude', [-7, -6])
    ->whereBetween('longitude', [109, 111])
    ->get();

// Dapatkan koordinat pusat kecamatan
$district = District::find('33.75.01');
if ($district->latitude && $district->longitude) {
    echo "Pusat kecamatan: {$district->latitude}, {$district->longitude}";
}
```

## Struktur Kode

### Kode Kecamatan

Kode kecamatan mengikuti pola: `XX.YY.ZZ`
- `XX` = Kode provinsi (2 digit)
- `YY` = Kode kabupaten/kota di dalam provinsi (2 digit)
- `ZZ` = Kode kecamatan di dalam kabupaten/kota (2 digit)

```php
$district = District::find('33.75.01');
echo $district->province_code; // "33" (Jawa Tengah)
echo $district->regency_code;  // "33.75" (Kota Pekalongan)
echo explode('.', $district->code)[2]; // "01" (Kecamatan pertama di Pekalongan)
```

### Navigasi Hirarkis

```php
// Navigasi ke atas dalam hierarki
$district = District::find('33.75.01');
$regency = $district->regency;
$province = $district->province;

echo "Hierarki lengkap: {$province->name} > {$regency->name} > {$district->name}";

// Navigasi ke bawah dalam hierarki
$villages = $district->villages;
echo "Desa/Kelurahan di {$district->name}: {$villages->count()}";
```

## Operasi Kode Pos

```php
$district = District::find('33.75.01');

// Dapatkan semua kode pos di kecamatan
$postalCodes = $district->postal_codes;
echo "Kode pos di {$district->name}: " . implode(', ', $postalCodes);

// Temukan kecamatan berdasarkan kode pos
$districts = District::whereHas('villages', function ($query) {
    $query->where('postal_code', '51111');
})->get();

// Kelompokkan desa/kelurahan berdasarkan kode pos
$district = District::with('villages')->find('33.75.01');
$villagesByPostal = $district->villages->groupBy('postal_code');
```

## Agregasi dan Statistik

```php
// Hitung desa/kelurahan per kecamatan
$districtsWithCounts = District::withCount('villages')->get();

// Dapatkan kecamatan dengan desa/kelurahan terbanyak
$topDistrict = District::withCount('villages')
    ->orderBy('villages_count', 'desc')
    ->first();

// Kelompokkan berdasarkan kabupaten/kota
$districtsByRegency = District::with('regency')
    ->get()
    ->groupBy('regency.name');

// Statistik berdasarkan provinsi
$districtCounts = District::selectRaw('province_code, count(*) as total')
    ->groupBy('province_code')
    ->get();
```

## Tips Kinerja

### Kueri yang Efisien

```php
// Baik: Gunakan paginasi untuk dataset besar
$districts = District::paginate(50);

// Baik: Filter berdasarkan wilayah induk terlebih dahulu
$districts = District::where('regency_code', '33.75')
    ->with('villages')
    ->get();

// Baik: Pilih kolom tertentu
$districts = District::select('code', 'name', 'regency_code')->get();

// Hindari: Memuat semua kecamatan sekaligus
$districts = District::all(); // 7.285 catatan!
```

### Strategi Caching

```php
use Illuminate\Support\Facades\Cache;

// Cache kecamatan berdasarkan kabupaten/kota
function getDistrictsByRegency($regencyCode) {
    $cacheKey = "districts.regency.{$regencyCode}";
    
    return Cache::remember($cacheKey, 3600, function () use ($regencyCode) {
        return District::where('regency_code', $regencyCode)
            ->orderBy('name')
            ->get(['code', 'name']);
    });
}

// Cache kecamatan dengan relasi
function getDistrictWithDetails($code) {
    $cacheKey = "district.details.{$code}";
    
    return Cache::remember($cacheKey, 3600, function () use ($code) {
        return District::with(['regency', 'province', 'villages'])
            ->find($code);
    });
}
```

## Validasi

### Validasi Formulir

```php
// Validasi kecamatan ada dan termasuk dalam kabupaten/kota
'district_code' => [
    'required',
    'exists:nusa.districts,code',
    function ($attribute, $value, $fail) {
        $district = District::find($value);
        if (!$district || $district->regency_code !== request('regency_code')) {
            $fail('Kecamatan yang dipilih tidak valid untuk kabupaten/kota ini.');
        }
    }
]
```

### Aturan Validasi Kustom

```php
use Illuminate\Contracts\Validation\Rule;

class ValidDistrictForRegency implements Rule
{
    private $regencyCode;
    
    public function __construct($regencyCode)
    {
        $this->regencyCode = $regencyCode;
    }
    
    public function passes($attribute, $value)
    {
        $district = District::find($value);
        return $district && $district->regency_code === $this->regencyCode;
    }
    
    public function message()
    {
        return 'Kecamatan yang dipilih bukan milik kabupaten/kota yang ditentukan.';
    }
}

// Penggunaan
'district_code' => ['required', new ValidDistrictForRegency($regencyCode)]
```

## Skema Database

```sql
CREATE TABLE districts (
    code VARCHAR(8) PRIMARY KEY,
    regency_code VARCHAR(5) NOT NULL,
    province_code VARCHAR(2) NOT NULL,
    name VARCHAR(255) NOT NULL,
    latitude DOUBLE NULL,
    longitude DOUBLE NULL,
    FOREIGN KEY (regency_code) REFERENCES regencies(code),
    FOREIGN KEY (province_code) REFERENCES provinces(code)
);

-- Indeks
CREATE INDEX idx_districts_regency ON districts(regency_code);
CREATE INDEX idx_districts_province ON districts(province_code);
CREATE INDEX idx_districts_name ON districts(name);
CREATE INDEX idx_districts_coordinates ON districts(latitude, longitude);
```

## Konstanta

```php
// Jumlah total kecamatan di Indonesia
District::count(); // 7.285

// Rata-rata kecamatan per kabupaten/kota
$avgDistrictsPerRegency = District::count() / Regency::count(); // ~14.1
```

## Model Terkait

- **[Model Province](/id/api/models/province)** - Divisi administratif induk-atas
- **[Model Regency](/id/api/models/regency)** - Divisi administratif induk
- **[Model Village](/id/api/models/village)** - Divisi administratif anak
- **[Model Address](/id/api/models/address)** - Manajemen alamat dengan referensi kecamatan