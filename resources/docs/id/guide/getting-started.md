# Memulai

Selamat datang di Laravel Nusa! Panduan ini akan membantu Anda memulai menggunakan paket ini dalam aplikasi Laravel Anda.

## Apa itu Laravel Nusa?

Laravel Nusa adalah paket Laravel yang menyediakan data wilayah administratif Indonesia yang lengkap dan siap pakai. Paket ini mencakup:

- **34 Provinsi** dengan kode dan nama resmi
- **514 Kabupaten/Kota** dengan relasi ke provinsi
- **7.266 Kecamatan** dengan relasi hierarkis
- **83.467 Kelurahan/Desa** dengan kode pos

## Instalasi Cepat

### 1. Install via Composer

```bash
composer require creasi/laravel-nusa
```

### 2. Jalankan Setup

```bash
php artisan nusa:install
```

Perintah ini akan:
- Menyiapkan konfigurasi database
- Mengatur koneksi SQLite untuk data Nusa
- Mempublikasikan file konfigurasi

### 3. Mulai Menggunakan

```php
use Creasi\Nusa\Models\Province;

// Dapatkan semua provinsi
$provinces = Province::all();

// Cari provinsi berdasarkan nama
$jateng = Province::search('Jawa Tengah')->first();

// Akses kabupaten/kota dalam provinsi
$regencies = $jateng->regencies;

// Akses semua desa dalam provinsi
$villages = $jateng->villages;
```

## Konsep Dasar

### Hierarki Administratif

Indonesia memiliki struktur administratif 4 tingkat:

```
🇮🇩 Indonesia
├── 34 Provinsi
├── 514 Kabupaten/Kota
├── 7.266 Kecamatan
└── 83.467 Kelurahan/Desa
```

### Model dan Relasi

Laravel Nusa menyediakan model Eloquent untuk setiap tingkat:

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Relasi ke bawah (one-to-many)
$province = Province::find('33');
$regencies = $province->regencies;
$districts = $province->districts;
$villages = $province->villages;

// Relasi ke atas (many-to-one)
$village = Village::find('33.74.01.1001');
$district = $village->district;
$regency = $village->regency;
$province = $village->province;
```

## Penggunaan Dasar

### Mengakses Data Provinsi

```php
use Creasi\Nusa\Models\Province;

// Semua provinsi
$provinces = Province::all();

// Provinsi tertentu
$jateng = Province::find('33');
echo $jateng->name; // "Jawa Tengah"

// Pencarian provinsi
$javaProvinces = Province::search('jawa')->get();
```

### Mengakses Data Kabupaten/Kota

```php
use Creasi\Nusa\Models\Regency;

// Kabupaten/kota dalam provinsi
$regencies = Regency::where('province_code', '33')->get();

// Kabupaten/kota tertentu
$semarang = Regency::find('33.74');
echo $semarang->name; // "Kota Semarang"

// Akses provinsi induk
echo $semarang->province->name; // "Jawa Tengah"
```

### Mengakses Data Kecamatan

```php
use Creasi\Nusa\Models\District;

// Kecamatan dalam kabupaten/kota
$districts = District::where('regency_code', '33.74')->get();

// Kecamatan tertentu
$district = District::find('33.74.01');
echo $district->name; // "Semarang Tengah"

// Akses hierarki lengkap
echo $district->regency->name; // "Kota Semarang"
echo $district->province->name; // "Jawa Tengah"
```

### Mengakses Data Kelurahan/Desa

```php
use Creasi\Nusa\Models\Village;

// Desa dalam kecamatan
$villages = Village::where('district_code', '33.74.01')->get();

// Desa tertentu
$village = Village::find('33.74.01.1001');
echo $village->name; // "Medono"
echo $village->postal_code; // "50132"

// Akses hierarki lengkap
echo $village->district->name; // "Semarang Tengah"
echo $village->regency->name; // "Kota Semarang"
echo $village->province->name; // "Jawa Tengah"
```

## Integrasi dengan Model Anda

### Menggunakan Trait

Laravel Nusa menyediakan trait untuk menambahkan fungsionalitas lokasi ke model Anda:

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class User extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
}

// Sekarang user memiliki relasi village
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'village_code' => '33.74.01.1001'
]);

echo $user->village->name; // "Medono"
echo $user->village->province->name; // "Jawa Tengah"
```

### Trait yang Tersedia

```php
// Relasi tunggal
use WithProvince;   // Relasi ke satu provinsi
use WithRegency;    // Relasi ke satu kabupaten/kota
use WithDistrict;   // Relasi ke satu kecamatan
use WithVillage;    // Relasi ke satu desa

// Relasi jamak
use WithDistricts;  // Relasi ke banyak kecamatan
use WithVillages;   // Relasi ke banyak desa

// Manajemen alamat
use WithAddress;    // Satu alamat
use WithAddresses;  // Banyak alamat

// Koordinat geografis
use WithCoordinate; // Latitude dan longitude
```

## Pencarian dan Filter

### Pencarian Fleksibel

```php
// Pencarian berdasarkan nama
$provinces = Province::search('jawa')->get();
$regencies = Regency::search('semarang')->get();
$villages = Village::search('medono')->get();

// Pencarian berdasarkan kode
$province = Province::search('33')->first();
$village = Village::search('33.74.01.1001')->first();

// Pencarian berdasarkan kode pos
$villages = Village::search('50132')->get();
```

### Filter Berdasarkan Relasi

```php
// Kabupaten/kota di Jawa Tengah
$regencies = Regency::whereHas('province', function ($query) {
    $query->where('name', 'like', '%Jawa Tengah%');
})->get();

// Desa dengan kode pos tertentu
$villages = Village::where('postal_code', '50132')->get();

// Kecamatan dengan banyak desa
$districts = District::has('villages', '>=', 10)->get();
```

## Contoh Aplikasi Sederhana

### Form Alamat Bertingkat

```php
// Controller
class AddressController extends Controller
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

### Model Customer dengan Alamat

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class Customer extends Model
{
    use WithVillage;
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'village_code',
        'address_line'
    ];
    
    // Mendapatkan alamat lengkap
    public function getFullAddressAttribute()
    {
        if ($this->village) {
            return "{$this->address_line}, {$this->village->name}, {$this->village->district->name}, {$this->village->regency->name}, {$this->village->province->name} {$this->village->postal_code}";
        }
        return $this->address_line;
    }
}

// Penggunaan
$customer = Customer::create([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'phone' => '081234567890',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123'
]);

echo $customer->full_address;
// "Jl. Merdeka No. 123, Medono, Semarang Tengah, Kota Semarang, Jawa Tengah 50132"
```

## Tips dan Best Practices

### 1. Gunakan Eager Loading

```php
// Efisien - load relasi sekaligus
$villages = Village::with(['district.regency.province'])->get();

// Tidak efisien - N+1 query problem
$villages = Village::all();
foreach ($villages as $village) {
    echo $village->province->name; // Query terpisah untuk setiap village
}
```

### 2. Gunakan Select untuk Field Tertentu

```php
// Hanya ambil field yang diperlukan
$provinces = Province::select('code', 'name')->get();

// Untuk dropdown/select option
$regencies = Regency::where('province_code', '33')
    ->select('code', 'name')
    ->orderBy('name')
    ->get();
```

### 3. Gunakan Pagination untuk Data Besar

```php
// Untuk menampilkan semua desa (83K+ records)
$villages = Village::paginate(50);

// Dengan pencarian
$villages = Village::search($query)->paginate(50);
```

### 4. Cache Data yang Sering Diakses

```php
// Cache daftar provinsi
$provinces = Cache::remember('provinces', 3600, function () {
    return Province::select('code', 'name')->get();
});
```

## Langkah Selanjutnya

Sekarang Anda sudah memahami dasar-dasar Laravel Nusa. Berikut adalah langkah selanjutnya:

1. **[Installation](/id/guide/installation)** - Panduan instalasi lengkap
2. **[Configuration](/id/guide/configuration)** - Konfigurasi lanjutan
3. **[Models](/id/guide/models)** - Memahami model dan relasi
4. **[Addresses](/id/guide/addresses)** - Manajemen alamat
5. **[API](/id/guide/api)** - Menggunakan RESTful API
6. **[Examples](/id/examples/basic-usage)** - Contoh implementasi praktis

---

*Mulai bangun aplikasi yang sadar lokasi dengan data administratif Indonesia yang akurat.*
