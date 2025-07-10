# Penggunaan Dasar

Panduan lengkap untuk menggunakan Laravel Nusa dalam berbagai skenario aplikasi.

## Mengakses Data Wilayah

### Mengambil Data Provinsi

```php
use Creasi\Nusa\Models\Province;

// Dapatkan semua provinsi
$provinces = Province::all();

// Cari provinsi berdasarkan kode
$jakarta = Province::find('31'); // DKI Jakarta

// Cari berdasarkan nama (case-insensitive)
$jateng = Province::search('Jawa Tengah')->first();
$bali = Province::search('bali')->first();

// Dengan pagination
$provinces = Province::paginate(10);
```

### Mengakses Kabupaten/Kota

```php
use Creasi\Nusa\Models\Regency;

// Semua kabupaten/kota dalam provinsi
$regencies = Regency::where('province_code', '33')->get();

// Cari berdasarkan nama
$semarang = Regency::search('Semarang')->first();

// Dengan relasi provinsi
$regency = Regency::with('province')->find('33.74');
echo $regency->province->name; // "Jawa Tengah"
```

### Mengakses Kecamatan

```php
use Creasi\Nusa\Models\District;

// Kecamatan dalam kabupaten/kota
$districts = District::where('regency_code', '33.74')->get();

// Dengan relasi lengkap
$district = District::with(['province', 'regency', 'villages'])->first();
```

### Mengakses Kelurahan/Desa

```php
use Creasi\Nusa\Models\Village;

// Cari berdasarkan kode pos
$villages = Village::where('postal_code', '50132')->get();

// Dengan hierarki lengkap
$village = Village::with(['district.regency.province'])->first();

// Navigasi hierarki
echo $village->name;              // Nama desa
echo $village->district->name;    // Nama kecamatan
echo $village->regency->name;     // Nama kabupaten/kota
echo $village->province->name;    // Nama provinsi
```

## Pencarian dan Filter

### Pencarian Fleksibel

```php
// Pencarian berdasarkan nama (partial match)
$provinces = Province::search('jawa')->get();
// Hasil: Jawa Barat, Jawa Tengah, Jawa Timur

// Pencarian berdasarkan kode
$province = Province::search('33')->first();

// Pencarian dengan multiple terms
$regencies = Regency::search('kota')->get();
// Hasil: Semua kota (bukan kabupaten)
```

### Filter Berdasarkan Relasi

```php
// Provinsi yang memiliki kota tertentu
$provinces = Province::whereHas('regencies', function ($query) {
    $query->where('name', 'like', '%Kota%');
})->get();

// Kabupaten/kota dengan jumlah kecamatan tertentu
$regencies = Regency::has('districts', '>=', 10)->get();

// Desa dengan kode pos tertentu
$villages = Village::whereIn('postal_code', ['50132', '50133'])->get();
```

## Relasi dan Eager Loading

### Mengoptimalkan Query

```php
// Buruk: N+1 query problem
$villages = Village::all();
foreach ($villages as $village) {
    echo $village->province->name; // Query untuk setiap village
}

// Baik: Eager loading
$villages = Village::with(['district.regency.province'])->get();
foreach ($villages as $village) {
    echo $village->province->name; // Tidak ada query tambahan
}
```

### Counting Relasi

```php
// Hitung jumlah relasi tanpa load data
$provinces = Province::withCount(['regencies', 'districts', 'villages'])->get();

foreach ($provinces as $province) {
    echo "{$province->name}: {$province->regencies_count} kabupaten/kota";
}
```

## Implementasi dalam Aplikasi

### 1. Dropdown Wilayah Bertingkat

```php
// Controller
class LocationController extends Controller
{
    public function getProvinces()
    {
        return Province::select('code', 'name')->get();
    }
    
    public function getRegencies($provinceCode)
    {
        return Regency::where('province_code', $provinceCode)
            ->select('code', 'name')
            ->get();
    }
    
    public function getDistricts($regencyCode)
    {
        return District::where('regency_code', $regencyCode)
            ->select('code', 'name')
            ->get();
    }
    
    public function getVillages($districtCode)
    {
        return Village::where('district_code', $districtCode)
            ->select('code', 'name', 'postal_code')
            ->get();
    }
}
```

```javascript
// Frontend (JavaScript)
function loadRegencies(provinceCode) {
    fetch(`/api/locations/regencies/${provinceCode}`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('regency');
            select.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
            data.forEach(regency => {
                select.innerHTML += `<option value="${regency.code}">${regency.name}</option>`;
            });
        });
}
```

### 2. Sistem Alamat User

```php
// Model User
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Authenticatable
{
    use WithAddresses;
    
    // User dapat memiliki multiple alamat
}

// Penggunaan
$user = User::find(1);

// Tambah alamat baru
$address = $user->addresses()->create([
    'name' => 'John Doe',
    'phone' => '081234567890',
    'province_code' => '33',
    'regency_code' => '33.74',
    'district_code' => '33.74.01',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123',
    'postal_code' => '50132',
    'is_default' => true,
]);

// Akses alamat dengan relasi
$addresses = $user->addresses()->with([
    'province', 'regency', 'district', 'village'
])->get();

foreach ($addresses as $address) {
    echo $address->full_address; // Alamat lengkap
}
```

## Tips Performa

### 1. Gunakan Select untuk Field Tertentu

```php
// Hanya ambil field yang diperlukan
$provinces = Province::select('code', 'name')->get();

// Untuk dropdown
$options = Province::pluck('name', 'code');
```

### 2. Pagination untuk Data Besar

```php
// Jangan load semua desa sekaligus
$villages = Village::paginate(50);

// Gunakan cursor pagination untuk performa lebih baik
$villages = Village::cursorPaginate(50);
```

### 3. Cache Query yang Sering Digunakan

```php
// Cache daftar provinsi
$provinces = Cache::remember('provinces', 3600, function () {
    return Province::select('code', 'name')->get();
});

// Cache dengan tag untuk invalidation
Cache::tags(['locations'])->remember('provinces', 3600, function () {
    return Province::all();
});
```

## Langkah Selanjutnya

- **[Address Management](/id/guide/addresses)** - Implementasi sistem alamat lengkap
- **[Customization](/id/guide/customization)** - Kustomisasi model dengan trait
- **[RESTful API](/id/guide/api)** - Menggunakan API endpoints
- **[API Integration](/id/examples/api-integration)** - Implementasi yang lebih kompleks
