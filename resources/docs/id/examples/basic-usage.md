# Contoh Penggunaan Dasar

Halaman ini menyediakan contoh praktis penggunaan Laravel Nusa dalam skenario umum. Contoh-contoh ini menunjukkan kasus penggunaan dan pola yang paling sering Anda temui saat bekerja dengan data administratif Indonesia.

## Menemukan Wilayah Administratif

### Berdasarkan Kode

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Temukan berdasarkan kode persis
$province = Province::find('33');              // Jawa Tengah
$regency = Regency::find('33.75');            // Kota Pekalongan
$district = District::find('33.75.01');       // Pekalongan Barat
$village = Village::find('33.75.01.1002');    // Desa Medono

// Periksa apakah ditemukan
if ($province) {
    echo "Ditemukan: {$province->name}";
} else {
    echo "Provinsi tidak ditemukan";
}
```

### Berdasarkan Pencarian Nama

```php
// Pencarian tidak peka huruf besar/kecil
$provinces = Province::search('jawa')->get();
$regencies = Regency::search('semarang')->get();
$districts = District::search('pekalongan')->get();
$villages = Village::search('medono')->get();

// Dapatkan hasil pertama
$jateng = Province::search('jawa tengah')->first();
$semarang = Regency::search('kota semarang')->first();
```

### Beberapa Istilah Pencarian

```php
// Cari beberapa provinsi
$javaProvinces = Province::where(function ($query) {
    $query->search('jawa barat')
          ->orWhere(function ($q) { $q->search('jawa tengah'); })
          ->orWhere(function ($q) { $q->search('jawa timur'); });
})->get();

// Cari dengan alternatif kode
$results = Province::search('33')
    ->orWhere(function ($query) {
        $query->search('jawa tengah');
    })->get();
```

## Bekerja dengan Relasi

### Mendapatkan Data Terkait

```php
use Creasi\Nusa\Models\Province;

$province = Province::find('33');

// Dapatkan semua kabupaten/kota di provinsi
$regencies = $province->regencies;
echo "Kabupaten/Kota di {$province->name}: {$regencies->count()}";

// Dapatkan semua kecamatan di provinsi
$districts = $province->districts;
echo "Kecamatan di {$province->name}: {$districts->count()}";

// Dapatkan semua desa/kelurahan di provinsi
$villages = $province->villages;
echo "Desa/Kelurahan di {$province->name}: {$villages->count()}";
```

### *Eager Loading* untuk Kinerja

```php
// Muat provinsi dengan kabupaten/kotanya
$province = Province::with('regencies')->find('33');

// Muat beberapa relasi
$province = Province::with(['regencies', 'districts'])->find('33');

// Muat relasi bersarang
$provinces = Province::with(['regencies.districts.villages'])->get();

// Muat hanya kolom tertentu
$provinces = Province::with(['regencies:code,province_code,name'])->get();
```

### Relasi Terbalik

```php
use Creasi\Nusa\Models\Village;

$village = Village::find('33.75.01.1002');

// Dapatkan tingkat administratif induk
$district = $village->district;
$regency = $village->regency;
$province = $village->province;

echo "Alamat lengkap: {$village->name}, {$district->name}, {$regency->name}, {$province->name}";
```

## Integrasi Formulir Alamat

Untuk membangun formulir alamat lengkap dengan *dropdown* bertingkat, lihat panduan khusus [Formulir Alamat](/id/examples/address-forms) yang mencakup:

- Implementasi *controller backend* lengkap
- Integrasi JavaScript *frontend* dengan beberapa *framework*
- Validasi formulir dan penanganan *error*
- Pertimbangan gaya dan UX

### Data Formulir Alamat Cepat

```php
// Endpoint data dropdown bertingkat sederhana
class AddressController extends Controller
{
    public function getRegencies(Request $request)
    {
        return Regency::where('province_code', $request->province_code)
            ->orderBy('name')
            ->get(['code', 'name']);
    }

    public function getDistricts(Request $request)
    {
        return District::where('regency_code', $request->regency_code)
            ->orderBy('name')
            ->get(['code', 'name']);
    }

    public function getVillages(Request $request)
    {
        return Village::where('district_code', $request->district_code)
            ->orderBy('name')
            ->get(['code', 'name', 'postal_code']);
    }
}
```

## Penggunaan Data Geografis

### Bekerja dengan Koordinat

```php
use Creasi\Nusa\Models\Province;

$province = Province::find('33');

// Dapatkan koordinat pusat
$latitude = $province->latitude;
$longitude = $province->longitude;

// Dapatkan koordinat batas (jika tersedia)
$boundaries = $province->coordinates;

if ($boundaries) {
    echo "Provinsi memiliki " . count($boundaries) . " titik batas";
    
    // Gunakan dengan pustaka pemetaan
    $geoJson = [
        'type' => 'Polygon',
        'coordinates' => [$boundaries]
    ];
}
```

### Agregasi Kode Pos

```php
// Dapatkan semua kode pos di provinsi
$province = Province::find('33');
$postalCodes = $province->postal_codes;

echo "Kode pos di {$province->name}: " . implode(', ', $postalCodes);

// Dapatkan kode pos di kecamatan
$district = District::find('33.75.01');
$districtPostalCodes = $district->postal_codes;

// Temukan desa/kelurahan berdasarkan kode pos
$villages = Village::where('postal_code', '51111')->get();
```

## Validasi Data

### Validasi *Form Request*

```php
use Illuminate\Foundation\Http\FormRequest;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class AddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'province_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Province::find($value)) {
                        $fail('Provinsi yang dipilih tidak valid.');
                    }
                },
            ],
            'regency_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $regency = Regency::find($value);
                    if (!$regency || $regency->province_code !== $this->province_code) {
                        $fail('Kabupaten/kota yang dipilih tidak valid untuk provinsi ini.');
                    }
                },
            ],
            'district_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $district = District::find($value);
                    if (!$district || $district->regency_code !== $this->regency_code) {
                        $fail('Kecamatan yang dipilih tidak valid untuk kabupaten/kota ini.');
                    }
                },
            ],
            'village_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $village = Village::find($value);
                    if (!$village || $village->district_code !== $this->district_code) {
                        $fail('Desa/kelurahan yang dipilih tidak valid untuk kecamatan ini.');
                    }
                },
            ],
        ];
    }
}
```

### Aturan Validasi Kustom

```php
use Illuminate\Contracts\Validation\Rule;
use Creasi\Nusa\Models\Village;

class ValidIndonesianAddress implements Rule
{
    public function passes($attribute, $value)
    {
        // Validasi bahwa semua komponen alamat konsisten
        $village = Village::find($value['village_code']);
        
        return $village &&
               $village->district_code === $value['district_code'] &&
               $village->regency_code === $value['regency_code'] &&
               $village->province_code === $value['province_code'];
    }
    
    public function message()
    {
        return 'Komponen alamat tidak konsisten.';
    }
}
```

## Optimasi Kinerja

### Kueri yang Efisien

```php
// Baik: Gunakan kolom tertentu
$provinces = Province::select('code', 'name')->get();

// Baik: Gunakan paginasi untuk dataset besar
$villages = Village::paginate(50);

// Baik: Gunakan whereIn untuk beberapa kode
$regencies = Regency::whereIn('code', ['33.75', '33.76', '33.77'])->get();

// Hindari: Memuat semua desa/kelurahan sekaligus
// $allVillages = Village::all(); // 83.762 catatan!
```

### Strategi Caching

```php
use Illuminate\Support\Facades\Cache;

class LocationService
{
    public function getProvinces()
    {
        return Cache::remember('nusa.provinces', 3600, function () {
            return Province::orderBy('name')->get(['code', 'name']);
        });
    }
    
    public function getRegenciesByProvince(string $provinceCode)
    {
        $cacheKey = "nusa.regencies.{$provinceCode}";
        
        return Cache::remember($cacheKey, 3600, function () use ($provinceCode) {
            return Regency::where('province_code', $provinceCode)
                ->orderBy('name')
                ->get(['code', 'name']);
        });
    }
}
```

## Penanganan Error

### *Graceful Fallbacks*

```php
use Creasi\Nusa\Models\Province;

function getProvinceName(string $code): string
{
    try {
        $province = Province::find($code);
        return $province ? $province->name : "Provinsi Tidak Dikenal ({$code})";
    } catch (Exception $e) {
        Log::error("Gagal mendapatkan nama provinsi untuk kode: {$code}", [
            'error' => $e->getMessage()
        ]);
        return "Provinsi Tidak Dikenal";
    }
}

function buildFullAddress(array $codes): string
{
    $parts = [];
    
    if ($village = Village::find($codes['village_code'] ?? null)) {
        $parts[] = $village->name;
        $parts[] = $village->district->name;
        $parts[] = $village->regency->name;
        $parts[] = $village->province->name;
    }
    
    return implode(', ', array_filter($parts));
}
```

## Langkah Selanjutnya

- **[Integrasi API](/id/examples/api-integration)** - Pelajari cara menggunakan API RESTful
- **[Formulir Alamat](/id/examples/address-forms)** - Bangun formulir alamat lengkap
- **[Kueri Geografis](/id/examples/geographic-queries)** - Bekerja dengan koordinat dan batas wilayah