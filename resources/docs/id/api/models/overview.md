# Ikhtisar Model

Laravel Nusa menyediakan model Eloquent yang komprehensif untuk semua tingkat hierarki administratif Indonesia. Model-model ini mencakup relasi lengkap, kemampuan pencarian, dan fitur integrasi untuk membangun aplikasi yang sadar lokasi.

Laravel Nusa provides comprehensive Eloquent models for all levels of Indonesia's administrative hierarchy. These models include complete relationships, search capabilities, and integration features for building location-aware applications.

## Hierarki Administratif

### Struktur Empat Tingkat

Struktur administratif Indonesia terdiri dari empat tingkat utama:

```
🇮🇩 Indonesia
├── 34 Provinsi
├── 514 Kabupaten/Kota
├── 7.266 Kecamatan
└── 83.467 Kelurahan/Desa
```

### Relasi Model

```php
Province (1) → (many) Regency (1) → (many) District (1) → (many) Village
```

Setiap tingkat mempertahankan relasi ke atas dan ke bawah, memungkinkan navigasi yang efisien melalui hierarki.

## Model Inti

### Model Provinsi

**Tabel**: `nusa.provinces`  
**Primary Key**: `code` (string, 2 karakter)  
**Jumlah Record**: 34 provinsi

```php
use Creasi\Nusa\Models\Province;

$province = Province::find('33'); // Jawa Tengah
$regencies = $province->regencies; // Semua kabupaten/kota dalam provinsi
$villages = $province->villages;   // Semua desa dalam provinsi (83K+ record)
```

### Model Kabupaten/Kota

**Tabel**: `nusa.regencies`  
**Primary Key**: `code` (string, 5 karakter dalam format xx.xx)  
**Jumlah Record**: 514 kabupaten dan kota

```php
use Creasi\Nusa\Models\Regency;

$regency = Regency::find('33.74'); // Kota Semarang
$province = $regency->province;     // Provinsi induk
$districts = $regency->districts;   // Semua kecamatan dalam kabupaten/kota
```

### Model Kecamatan

**Tabel**: `nusa.districts`  
**Primary Key**: `code` (string, 8 karakter dalam format xx.xx.xx)  
**Jumlah Record**: 7.266 kecamatan

```php
use Creasi\Nusa\Models\District;

$district = District::find('33.74.01'); // Semarang Tengah
$regency = $district->regency;           // Kabupaten/kota induk
$villages = $district->villages;         // Semua desa dalam kecamatan
```

### Model Kelurahan/Desa

**Tabel**: `nusa.villages`  
**Primary Key**: `code` (string, 13 karakter dalam format xx.xx.xx.xxxx)  
**Jumlah Record**: 83.467 kelurahan/desa

```php
use Creasi\Nusa\Models\Village;

$village = Village::find('33.74.01.1001'); // Desa spesifik
$district = $village->district;             // Kecamatan induk
$province = $village->province;             // Akses tingkat mana pun
```

## Skema Database

### Tabel Provinces

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `code` | string(2) | Primary key, kode provinsi |
| `name` | string | Nama resmi provinsi |
| `latitude` | decimal(10,8) | Latitude titik tengah |
| `longitude` | decimal(11,8) | Longitude titik tengah |

### Tabel Regencies

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `code` | string(5) | Primary key, kode kabupaten/kota |
| `province_code` | string(2) | Foreign key ke provinces |
| `name` | string | Nama resmi kabupaten/kota |
| `latitude` | decimal(10,8) | Latitude titik tengah |
| `longitude` | decimal(11,8) | Longitude titik tengah |

### Tabel Districts

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `code` | string(8) | Primary key, kode kecamatan |
| `regency_code` | string(5) | Foreign key ke regencies |
| `province_code` | string(2) | Foreign key ke provinces |
| `name` | string | Nama resmi kecamatan |
| `latitude` | decimal(10,8) | Latitude titik tengah |
| `longitude` | decimal(11,8) | Longitude titik tengah |

### Tabel Villages

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `code` | string(13) | Primary key, kode desa |
| `district_code` | string(8) | Foreign key ke districts |
| `regency_code` | string(5) | Foreign key ke regencies |
| `province_code` | string(2) | Foreign key ke provinces |
| `name` | string | Nama resmi desa |
| `postal_code` | string(5) | Kode pos lima digit |
| `latitude` | decimal(10,8) | Latitude titik tengah |
| `longitude` | decimal(11,8) | Longitude titik tengah |

## Relasi

### Relasi Hierarkis

Semua model mencakup relasi hierarkis yang lengkap:

```php
// Relasi ke bawah (one-to-many)
$province->regencies;  // HasMany
$province->districts;  // HasManyThrough
$province->villages;   // HasManyThrough

$regency->districts;   // HasMany
$regency->villages;    // HasManyThrough

$district->villages;   // HasMany

// Relasi ke atas (many-to-one)
$village->district;    // BelongsTo
$village->regency;     // BelongsTo (melalui district)
$village->province;    // BelongsTo (melalui district.regency)

$district->regency;    // BelongsTo
$district->province;   // BelongsTo (melalui regency)

$regency->province;    // BelongsTo
```

### Jumlah Relasi

Model mencakup relasi count untuk performa:

```php
$provinces = Province::withCount(['regencies', 'districts', 'villages'])->get();

foreach ($provinces as $province) {
    echo "{$province->name}: {$province->villages_count} desa";
}
```

## Kemampuan Pencarian

### Scope Search

Semua model mencakup scope `search()` untuk pencarian yang fleksibel:

```php
// Pencarian berdasarkan nama (case-insensitive, partial match)
$provinces = Province::search('jawa')->get();
// Hasil: Jawa Barat, Jawa Tengah, Jawa Timur

// Pencarian berdasarkan kode
$province = Province::search('33')->first();
// Hasil: Jawa Tengah

// Pencarian desa berdasarkan kode pos
$villages = Village::search('50132')->get();
```

### Filter Lanjutan

```php
// Filter berdasarkan relasi
$provinces = Province::whereHas('regencies', function ($query) {
    $query->where('name', 'like', '%Kota%');
})->get();

// Filter berdasarkan jumlah
$regencies = Regency::has('districts', '>=', 10)->get();

// Multiple kondisi
$villages = Village::where('postal_code', '50132')
    ->whereHas('district', function ($query) {
        $query->where('name', 'like', '%Tengah%');
    })
    ->get();
```

## Fitur Performa

### Koneksi Database

Laravel Nusa menggunakan koneksi database terpisah (`nusa`) untuk menghindari konflik:

```php
// config/database.php
'nusa' => [
    'driver' => 'sqlite',
    'database' => database_path('nusa.sqlite'),
    'prefix' => '',
    'foreign_key_constraints' => true,
],
```

### Query yang Dioptimalkan

Model dioptimalkan untuk performa:

```php
// Seleksi field yang efisien
$provinces = Province::select('code', 'name')->get();

// Pagination untuk dataset besar
$villages = Village::paginate(50);

// Chunk processing untuk operasi bulk
Village::chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Proses village
    }
});
```

### Eager Loading

Mencegah masalah N+1 query dengan eager loading:

```php
// Load hierarki lengkap secara efisien
$villages = Village::with(['district.regency.province'])
    ->where('postal_code', '50132')
    ->get();

// Akses tanpa query tambahan
foreach ($villages as $village) {
    echo $village->province->name; // Tidak ada query tambahan
}
```

## Fitur Integrasi

### Manajemen Alamat

Laravel Nusa mencakup model Address untuk mengelola alamat berbasis lokasi:

```php
use Creasi\Nusa\Models\Address;

$address = Address::create([
    'addressable_type' => User::class,
    'addressable_id' => 1,
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123',
    'postal_code' => '50132'
]);

// Akses hierarki melalui village
echo $address->village->province->name;
```

### Trait Model

Gunakan trait untuk menambahkan fungsi lokasi ke model Anda:

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class User extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
}

// User sekarang memiliki relasi village
$user = User::with('village.province')->first();
echo $user->village->province->name;
```

## Kustomisasi

### Model Kustom

Extend model dasar untuk fungsi kustom:

```php
// app/Models/CustomProvince.php
class CustomProvince extends \Creasi\Nusa\Models\Province
{
    protected $appends = ['display_name'];
    
    public function getDisplayNameAttribute()
    {
        return "Provinsi {$this->name}";
    }
    
    public function businesses()
    {
        return $this->hasMany(Business::class, 'province_code', 'code');
    }
}
```

### Konfigurasi

Update konfigurasi untuk menggunakan model kustom:

```php
// config/nusa.php
'models' => [
    'province' => \App\Models\CustomProvince::class,
    'regency' => \Creasi\Nusa\Models\Regency::class,
    'district' => \Creasi\Nusa\Models\District::class,
    'village' => \Creasi\Nusa\Models\Village::class,
    'address' => \Creasi\Nusa\Models\Address::class,
],
```

## Langkah Selanjutnya

### **Dokumentasi Model Individual**:

- **[Model Provinsi](/id/api/models/province)** - Referensi detail model provinsi
- **[Model Kabupaten/Kota](/id/api/models/regency)** - Referensi model kabupaten dan kota
- **[Model Kecamatan](/id/api/models/district)** - Referensi model kecamatan
- **[Model Kelurahan/Desa](/id/api/models/village)** - Referensi model kelurahan/desa
- **[Model Alamat](/id/api/models/address)** - Referensi manajemen alamat

### **Dokumentasi Terkait**:

- **[Model Concerns](/id/api/concerns/)** - Trait yang tersedia dan penggunaannya
- **[Endpoint API](/id/api/overview)** - RESTful API untuk mengakses model
- **[Contoh Penggunaan](/id/examples/basic-usage)** - Contoh implementasi praktis
