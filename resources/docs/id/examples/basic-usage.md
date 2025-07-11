# Contoh Penggunaan Dasar

Halaman ini menyediakan contoh praktis penggunaan Laravel Nusa dalam skenario umum. Contoh-contoh ini mendemonstrasikan kasus penggunaan dan pola yang paling sering Anda temui saat bekerja dengan data administratif Indonesia.

## Mencari Wilayah Administratif

### Berdasarkan Kode

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Cari berdasarkan kode yang tepat
$province = Province::find('33');              // Jawa Tengah
$regency = Regency::find('33.75');            // Kota Pekalongan
$district = District::find('33.75.01');       // Pekalongan Barat
$village = Village::find('33.75.01.1002');    // Kelurahan Medono

// Periksa apakah ditemukan
if ($province) {
    echo "Ditemukan: {$province->name}";
} else {
    echo "Provinsi tidak ditemukan";
}
```

### Berdasarkan Pencarian Nama

```php
// Pencarian case-insensitive
$provinces = Province::search('jawa')->get();
$regencies = Regency::search('semarang')->get();
$districts = District::search('pekalongan')->get();
$villages = Village::search('medono')->get();

// Ambil hasil pertama
$jateng = Province::search('jawa tengah')->first();
$semarang = Regency::search('kota semarang')->first();
```

### Pencarian dengan Multiple Terms

```php
// Cari beberapa provinsi
$javaProvinces = Province::where(function ($query) {
    $query->search('jawa barat')
          ->orWhere(function ($q) { $q->search('jawa tengah'); })
          ->orWhere(function ($q) { $q->search('jawa timur'); });
})->get();

// Pencarian dengan alternatif kode
$jakarta = Province::where('code', '31')
    ->orWhere(function ($query) {
        $query->search('jakarta')
              ->orSearch('dki jakarta')
              ->orSearch('dki');
    })->first();
```

## Bekerja dengan Relasi

### Memuat Data Terkait

```php
// Eager loading untuk performa yang lebih baik
$province = Province::with(['regencies', 'regencies.districts'])
    ->find('33');

// Akses data terkait
foreach ($province->regencies as $regency) {
    echo "Kabupaten/Kota: {$regency->name}\n";
    
    foreach ($regency->districts as $district) {
        echo "  - Kecamatan: {$district->name}\n";
    }
}
```

### Navigasi Hierarki

```php
// Dari desa ke provinsi
$village = Village::find('33.75.01.1002');
$district = $village->district;
$regency = $district->regency;
$province = $regency->province;

echo "Alamat lengkap: {$village->name}, {$district->name}, {$regency->name}, {$province->name}";

// Atau gunakan relasi nested
$village = Village::with(['district.regency.province'])->find('33.75.01.1002');
$fullAddress = [
    $village->name,
    $village->district->name,
    $village->district->regency->name,
    $village->district->regency->province->name
];
echo implode(', ', $fullAddress);
```

### Memuat Anak-anak dari Parent

```php
// Semua kabupaten/kota di Jawa Tengah
$regencies = Regency::where('province_code', '33')->get();

// Semua kecamatan di Kota Semarang
$districts = District::where('regency_code', '33.74')->get();

// Semua kelurahan/desa di Kecamatan Semarang Tengah
$villages = Village::where('district_code', '33.74.01')->get();

// Dengan pagination untuk dataset besar
$villages = Village::where('district_code', '33.74.01')
    ->paginate(50);
```

## Pencarian dan Filtering

### Pencarian Fuzzy

```php
// Pencarian yang toleran terhadap typo
$results = Province::search('jakrta')->get(); // Akan menemukan "Jakarta"
$results = Regency::search('semarag')->get();  // Akan menemukan "Semarang"

// Pencarian dengan wildcard
$results = Village::where('name', 'LIKE', '%medono%')->get();
$results = District::where('name', 'LIKE', 'semarang%')->get();
```

### Filter Berdasarkan Tipe

```php
// Filter kabupaten vs kota
$kabupaten = Regency::where('name', 'LIKE', 'Kabupaten%')->get();
$kota = Regency::where('name', 'LIKE', 'Kota%')->get();

// Filter kelurahan vs desa
$kelurahan = Village::where('name', 'LIKE', 'Kelurahan%')->get();
$desa = Village::where('name', 'NOT LIKE', 'Kelurahan%')->get();
```

### Pencarian dengan Scope

```php
// Menggunakan scope kustom
class Province extends Model
{
    public function scopeJava($query)
    {
        return $query->whereIn('code', ['31', '32', '33', '34', '35', '36']);
    }
    
    public function scopeOutsideJava($query)
    {
        return $query->whereNotIn('code', ['31', '32', '33', '34', '35', '36']);
    }
}

// Penggunaan
$javaProvinces = Province::java()->get();
$outsideJava = Province::outsideJava()->get();
```

## Bekerja dengan Kode Pos

### Mencari berdasarkan Kode Pos

```php
// Cari semua desa dengan kode pos tertentu
$villages = Village::where('postal_code', '50241')->get();

// Cari desa dalam range kode pos
$villages = Village::whereBetween('postal_code', ['50000', '59999'])->get();

// Cari desa tanpa kode pos
$villagesWithoutPostal = Village::whereNull('postal_code')->get();
```

### Validasi Kode Pos

```php
function validatePostalCode($villageCode, $postalCode)
{
    $village = Village::find($villageCode);
    
    if (!$village) {
        return false;
    }
    
    return $village->postal_code === $postalCode;
}

// Penggunaan
if (validatePostalCode('33.75.01.1002', '51119')) {
    echo "Kode pos valid";
} else {
    echo "Kode pos tidak sesuai";
}
```

## Aggregasi dan Statistik

### Menghitung Data

```php
// Hitung total per level
$totalProvinces = Province::count();           // 38
$totalRegencies = Regency::count();           // 514
$totalDistricts = District::count();          // 7,285
$totalVillages = Village::count();            // 83,762

// Hitung per provinsi
$regencyCount = Regency::where('province_code', '33')->count();
$districtCount = District::whereHas('regency', function ($query) {
    $query->where('province_code', '33');
})->count();
```

### Statistik per Wilayah

```php
// Statistik kabupaten/kota per provinsi
$stats = Province::withCount(['regencies'])->get();

foreach ($stats as $province) {
    echo "{$province->name}: {$province->regencies_count} kabupaten/kota\n";
}

// Statistik lengkap
$province = Province::withCount([
    'regencies',
    'regencies.districts',
    'regencies.districts.villages'
])->find('33');

echo "Jawa Tengah memiliki:\n";
echo "- {$province->regencies_count} kabupaten/kota\n";
echo "- {$province->regencies->sum('districts_count')} kecamatan\n";
echo "- {$province->regencies->sum(function($r) { return $r->districts->sum('villages_count'); })} kelurahan/desa\n";
```

## Optimisasi Performa

### Chunking untuk Dataset Besar

```php
// Hindari loading semua desa sekaligus
// ❌ Jangan lakukan ini
$allVillages = Village::all(); // 83,762 records!

// ✅ Gunakan chunking
Village::chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Process village
        echo "Processing: {$village->name}\n";
    }
});

// ✅ Atau gunakan lazy loading
foreach (Village::lazy() as $village) {
    // Process village satu per satu
    echo "Processing: {$village->name}\n";
}
```

### Caching Query

```php
use Illuminate\Support\Facades\Cache;

// Cache hasil query yang sering digunakan
$provinces = Cache::remember('all_provinces', 3600, function () {
    return Province::orderBy('name')->get();
});

// Cache dengan tag untuk invalidation yang mudah
Cache::tags(['nusa', 'provinces'])->put('java_provinces', $javaProvinces, 3600);

// Invalidate cache ketika data berubah
Cache::tags(['nusa'])->flush();
```

### Select Kolom Spesifik

```php
// Hanya ambil kolom yang diperlukan
$provinces = Province::select(['code', 'name'])->get();
$regencies = Regency::select(['code', 'name', 'province_code'])
    ->where('province_code', '33')
    ->get();

// Untuk dropdown
$provinceOptions = Province::pluck('name', 'code');
$regencyOptions = Regency::where('province_code', '33')
    ->pluck('name', 'code');
```

## Error Handling

### Validasi Input

```php
function findProvinceByCode($code)
{
    // Validasi format kode
    if (!preg_match('/^\d{2}$/', $code)) {
        throw new InvalidArgumentException('Kode provinsi harus 2 digit');
    }
    
    $province = Province::find($code);
    
    if (!$province) {
        throw new ModelNotFoundException("Provinsi dengan kode {$code} tidak ditemukan");
    }
    
    return $province;
}

// Penggunaan dengan try-catch
try {
    $province = findProvinceByCode('33');
    echo "Ditemukan: {$province->name}";
} catch (InvalidArgumentException $e) {
    echo "Format kode salah: {$e->getMessage()}";
} catch (ModelNotFoundException $e) {
    echo "Data tidak ditemukan: {$e->getMessage()}";
}
```

### Fallback untuk Data Tidak Ditemukan

```php
function getRegencyName($code, $fallback = 'Tidak Diketahui')
{
    $regency = Regency::find($code);
    return $regency ? $regency->name : $fallback;
}

// Penggunaan
echo getRegencyName('33.75', 'Kabupaten/Kota Tidak Ditemukan');
```

Contoh-contoh ini memberikan fondasi yang solid untuk menggunakan Laravel Nusa dalam aplikasi Anda. Ingatlah untuk selalu mempertimbangkan performa saat bekerja dengan dataset besar dan gunakan eager loading serta caching untuk optimisasi yang lebih baik.
