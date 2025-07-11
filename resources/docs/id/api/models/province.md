# Model Provinsi

Model `Province` merepresentasikan provinsi di Indonesia dan menyediakan akses ke semua 34 wilayah administratif provinsi.

## Referensi Kelas

```php
namespace Creasi\Nusa\Models;

class Province extends Model
{
    // Implementasi model
}
```

## Atribut

### Atribut Utama

| Atribut | Tipe | Deskripsi | Contoh |
|-----------|------|-------------|---------|
| `code` | `string` | Kode provinsi 2 digit (Kunci Utama) | `"33"` |
| `name` | `string` | Nama provinsi dalam bahasa Indonesia | `"Jawa Tengah"` |
| `latitude` | `float` | Lintang pusat geografis | `-6.9934809206806` |
| `longitude` | `float` | Bujur pusat geografis | `110.42024335421` |
| `coordinates` | `array\|null` | Koordinat poligon batas | `[[-6.123, 110.456], ...]` |

### Atribut Terkomputasi

| Atribut | Tipe | Deskripsi |
|-----------|------|-------------|
| `postal_codes` | `array` | Semua kode pos di dalam provinsi |

## Relasi

### Relasi One-to-Many

```php
// Dapatkan semua kabupaten/kota di provinsi
$province->regencies; // Collection<Regency>

// Dapatkan semua kecamatan di provinsi  
$province->districts; // Collection<District>

// Dapatkan semua desa/kelurahan di provinsi
$province->villages; // Collection<Village>
```

### Metode Relasi

```php
// Relasi Regencies
public function regencies(): HasMany
{
    return $this->hasMany(Regency::class, 'province_code', 'code');
}

// Relasi Districts (melalui kabupaten/kota)
public function districts(): HasMany
{
    return $this->hasMany(District::class, 'province_code', 'code');
}

// Relasi Villages (melalui kecamatan)
public function villages(): HasMany
{
    return $this->hasMany(Village::class, 'province_code', 'code');
}
```

## Scope

### Scope Pencarian

```php
// Cari berdasarkan nama atau kode (tidak peka huruf besar/kecil)
Province::search('jawa')->get();
Province::search('33')->first();
Province::search('tengah')->get();
```

**Implementasi:**
```php
public function scopeSearch($query, $term)
{
    return $query->where(function ($q) use ($term) {
        $q->where('name', 'like', "%{$term}%")
          ->orWhere('code', 'like', "%{$term}%");
    });
}
```

## Metode

### Metode Statis

```php
// Temukan provinsi berdasarkan kode
Province::find('33'); // Mengembalikan Province atau null

// Temukan atau gagal
Province::findOrFail('33'); // Mengembalikan Province atau melempar exception

// Dapatkan semua provinsi
Province::all(); // Collection<Province>

// Dapatkan dengan paginasi
Province::paginate(15); // LengthAwarePaginator
```

### Metode Instans

```php
$province = Province::find('33');

// Dapatkan kode pos di provinsi ini
$postalCodes = $province->postal_codes; // array

// Hitung catatan terkait
$regencyCount = $province->regencies()->count();
$districtCount = $province->districts()->count(); 
$villageCount = $province->villages()->count();
```

## Contoh Penggunaan

### Kueri Dasar

```php
use Creasi\Nusa\Models\Province;

// Dapatkan semua provinsi
$provinces = Province::all();

// Temukan provinsi tertentu
$centralJava = Province::find('33');
$westJava = Province::find('32');

// Cari provinsi
$javaProvinces = Province::search('jawa')->get();
$province33 = Province::search('33')->first();
```

### Dengan Relasi

```php
// Eager load relasi
$provinces = Province::with(['regencies'])->get();

// Muat relasi bersarang
$province = Province::with(['regencies.districts.villages'])->find('33');

// Muat kolom tertentu
$provinces = Province::with(['regencies:code,province_code,name'])->get();
```

### Penyaringan dan Pengurutan

```php
// Dapatkan provinsi tertentu
$selectedProvinces = Province::whereIn('code', ['33', '34', '35'])->get();

// Urutkan berdasarkan nama
$provinces = Province::orderBy('name')->get();

// Dapatkan provinsi dengan koordinat
$provincesWithCoords = Province::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();
```

### Agregasi

```php
// Hitung kabupaten/kota per provinsi
$provincesWithCounts = Province::withCount('regencies')->get();

foreach ($provincesWithCounts as $province) {
    echo "{$province->name}: {$province->regencies_count} kabupaten/kota";
}

// Dapatkan provinsi dengan kabupaten/kota terbanyak
$topProvince = Province::withCount('regencies')
    ->orderBy('regencies_count', 'desc')
    ->first();
```

### Kueri Geografis

```php
// Dapatkan provinsi dalam rentang koordinat tertentu
$provinces = Province::whereBetween('latitude', [-8, -5])
    ->whereBetween('longitude', [105, 115])
    ->get();

// Temukan provinsi terdekat dari koordinat
function findNearestProvince($lat, $lon) {
    $provinces = Province::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();
        
    $nearest = null;
    $minDistance = PHP_FLOAT_MAX;
    
    foreach ($provinces as $province) {
        $distance = calculateDistance($lat, $lon, $province->latitude, $province->longitude);
        if ($distance < $minDistance) {
            $minDistance = $distance;
            $nearest = $province;
        }
    }
    
    return $nearest;
}
```

## Bekerja dengan Koordinat

### Koordinat Batas

```php
$province = Province::find('33');

if ($province->coordinates) {
    // Konversi ke GeoJSON
    $geoJson = [
        'type' => 'Feature',
        'properties' => [
            'name' => $province->name,
            'code' => $province->code
        ],
        'geometry' => [
            'type' => 'Polygon',
            'coordinates' => [$province->coordinates]
        ]
    ];
    
    // Gunakan dengan pustaka pemetaan
    return response()->json($geoJson);
}
```

### Koordinat Pusat

```php
$province = Province::find('33');

// Dapatkan titik pusat
$centerLat = $province->latitude;
$centerLon = $province->longitude;

// Gunakan dengan peta
echo "Pusat provinsi: {$centerLat}, {$centerLon}";
```

## Operasi Kode Pos

```php
$province = Province::find('33');

// Dapatkan semua kode pos di provinsi
$postalCodes = $province->postal_codes;
echo "Kode pos: " . implode(', ', $postalCodes);

// Temukan provinsi berdasarkan kode pos
$villages = Village::where('postal_code', '51111')->get();
$provinces = $villages->pluck('province_code')->unique();
```

## Tips Kinerja

### Kueri yang Efisien

```php
// Baik: Pilih kolom tertentu
$provinces = Province::select('code', 'name')->get();

// Baik: Gunakan paginasi untuk dataset besar
$provinces = Province::paginate(15);

// Baik: Gunakan whereIn untuk beberapa kode
$provinces = Province::whereIn('code', ['33', '34', '35'])->get();

// Hindari: Memuat semua data termasuk koordinat besar
$provinces = Province::all(); // Memuat semua koordinat
```

### Caching

```php
use Illuminate\Support\Facades\Cache;

// Cache daftar provinsi
$provinces = Cache::remember('provinces', 3600, function () {
    return Province::orderBy('name')->get(['code', 'name']);
});

// Cache provinsi tertentu
$province = Cache::remember("province.{$code}", 3600, function () use ($code) {
    return Province::find($code);
});
```

## Validasi

### Validasi Formulir

```php
// Validasi kode provinsi ada
'province_code' => 'required|exists:nusa.provinces,code'

// Aturan validasi kustom
use Illuminate\Contracts\Validation\Rule;

class ValidProvinceCode implements Rule
{
    public function passes($attribute, $value)
    {
        return Province::where('code', $value)->exists();
    }
    
    public function message()
    {
        return 'Provinsi yang dipilih tidak valid.';
    }
}
```

## Skema Database

```sql
CREATE TABLE provinces (
    code VARCHAR(2) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    latitude DOUBLE NULL,
    longitude DOUBLE NULL,
    coordinates JSON NULL
);

-- Indeks
CREATE INDEX idx_provinces_name ON provinces(name);
CREATE INDEX idx_provinces_coordinates ON provinces(latitude, longitude);
```

## Konstanta

```php
// Jumlah total provinsi di Indonesia
Province::count(); // 34

// Kode provinsi selalu 2 digit
// Contoh: '11', '12', '13', ..., '94', '95', '96'
```

## Model Terkait

- **[Model Regency](/id/api/models/regency)** - Divisi administratif tingkat kedua
- **[Model District](/id/api/models/district)** - Divisi administratif tingkat ketiga  
- **[Model Village](/id/api/models/village)** - Divisi administratif tingkat keempat
- **[Model Address](/id/api/models/address)** - Manajemen alamat dengan referensi provinsi