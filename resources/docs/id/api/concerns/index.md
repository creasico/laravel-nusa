# *Concern* Model (Trait)

Laravel Nusa menyediakan kumpulan *trait* (concern) yang dapat digunakan kembali untuk menambahkan fungsionalitas wilayah administratif Indonesia ke model Anda sendiri. *Trait* ini memudahkan integrasi fitur berbasis lokasi ke dalam model aplikasi Anda.

## Ringkasan

*Concern* model terletak di dalam *namespace* `Creasi\Nusa\Models\Concerns` dan menyediakan berbagai jenis relasi dan fungsionalitas:

### *Trait* Relasi

- **[WithProvince](/id/api/concerns/with-province)** - Menambahkan relasi `belongsTo` ke provinsi
- **[WithRegency](/id/api/concerns/with-regency)** - Menambahkan relasi `belongsTo` ke kabupaten/kota
- **[WithDistrict](/id/api/concerns/with-district)** - Menambahkan relasi `belongsTo` ke kecamatan
- **[WithVillage](/id/api/concerns/with-village)** - Menambahkan relasi `belongsTo` ke desa/kelurahan
- **[WithDistricts](/id/api/concerns/with-districts)** - Menambahkan relasi `hasMany` ke kecamatan
- **[WithVillages](/id/api/concerns/with-villages)** - Menambahkan relasi `hasMany` ke desa/kelurahan dengan kode pos

### *Trait* Manajemen Alamat

- **[WithAddress](/id/api/concerns/with-address)** - Menambahkan relasi alamat polimorfik tunggal
- **[WithAddresses](/id/api/concerns/with-addresses)** - Menambahkan relasi alamat polimorfik ganda

### *Trait* Geografis

- **[WithCoordinate](/id/api/concerns/with-coordinate)** - Menambahkan fungsionalitas koordinat lintang/bujur

## Contoh Penggunaan Umum

### Profil Pengguna dengan Lokasi

```php
use Creasi\Nusa\Models\Concerns\{WithProvince, WithRegency, WithDistrict, WithVillage};

class UserProfile extends Model
{
    use WithProvince, WithRegency, WithDistrict, WithVillage;
    
    protected $fillable = [
        'name', 'email', 
        'province_code', 'regency_code', 'district_code', 'village_code'
    ];
}

// Penggunaan
$profile = UserProfile::with(['province', 'regency', 'district', 'village'])->first();
echo "Location: {$profile->village->name}, {$profile->district->name}, {$profile->regency->name}, {$profile->province->name}";
```

### Lokasi Bisnis

```php
use Creasi\Nusa\Models\Concerns\{WithAddress, WithCoordinate};

class Store extends Model
{
    use WithAddress, WithCoordinate;
    
    protected $fillable = [
        'name', 'description', 'latitude', 'longitude'
    ];
}

// Penggunaan
$store = Store::with('address')->first();
echo "Store: {$store->name} at {$store->latitude}, {$store->longitude}";
```

### Manajemen Regional

```php
use Creasi\Nusa\Models\Concerns\{WithProvince, WithDistricts, WithVillages};

class RegionalOffice extends Model
{
    use WithProvince, WithDistricts, WithVillages;
    
    protected $fillable = ['name', 'province_code'];
}

// Penggunaan
$office = RegionalOffice::with(['province', 'districts', 'villages'])->first();
echo "Office covers {$office->districts->count()} districts and {$office->villages->count()} villages";
```

## Instalasi dan Pengaturan

### 1. Gunakan *Trait* di Model Anda

Cukup tambahkan pernyataan `use` untuk *trait* yang Anda butuhkan:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithVillage;

class Customer extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
}
```

### 2. Migrasi Database

Pastikan tabel database Anda memiliki kolom *foreign key* yang sesuai:

```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('village_code')->nullable();
    $table->timestamps();
    
    // Optional: Add foreign key constraint
    $table->foreign('village_code')
          ->references('code')
          ->on('villages')
          ->onDelete('set null');
});
```

### 3. Kustomisasi Nama *Foreign Key*

Anda dapat mengkustomisasi nama kolom *foreign key* dengan mendefinisikan properti:

```php
class Customer extends Model
{
    use WithVillage;
    
    protected $villageKey = 'customer_village_code';
    protected $fillable = ['name', 'email', 'customer_village_code'];
}
```

## Penggunaan Lanjutan

### Dukungan Alamat Ganda

```php
use Creasi\Nusa\Models\Concerns\WithAddresses;

class Company extends Model
{
    use WithAddresses;
}

// Usage
$company = Company::first();
$company->addresses()->create([
    'type' => 'headquarters',
    'province_code' => '33',
    'regency_code' => '33.75',
    'district_code' => '33.75.01',
    'village_code' => '33.75.01.1002',
    'address_line' => 'Jl. Merdeka No. 123'
]);
```

### Kueri Geografis

```php
use Creasi\Nusa\Models\Concerns\WithCoordinate;

class Event extends Model
{
    use WithCoordinate;
}

// Find events within a radius
$nearbyEvents = Event::selectRaw('*, (
    6371 * acos(
        cos(radians(?)) * cos(radians(latitude)) * 
        cos(radians(longitude) - radians(?)) + 
        sin(radians(?)) * sin(radians(latitude))
    )
) AS distance', [$lat, $lng, $lat])
->having('distance', '<', 10)
->orderBy('distance')
->get();
```

## Praktik Terbaik

### 1. Gunakan *Trait* yang Sesuai

Pilih *trait* berdasarkan kebutuhan model Anda:
- Gunakan `WithVillage` untuk lokasi paling spesifik
- Gunakan `WithProvince` untuk pengelompokan regional
- Gunakan `WithAddresses` untuk model yang dapat memiliki banyak lokasi

### 2. *Eager Loading*

Selalu lakukan *eager load* pada relasi untuk menghindari kueri N+1:

```php
$pelanggan = Pelanggan::with(['village.district.regency.province'])->get();
```

### 3. Validasi

Validasi kode administratif dalam *form request* Anda:

```php
public function rules()
{
    return [
        'village_code' => 'required|exists:nusa.villages,code',
        'district_code' => 'required|exists:nusa.districts,code',
        'regency_code' => 'required|exists:nusa.regencies,code',
        'province_code' => 'required|exists:nusa.provinces,code',
    ];
}
```

## Dokumentasi Terkait

- **[Panduan Manajemen Alamat](/id/guide/addresses)** - Panduan lengkap penggunaan fungsionalitas alamat
- **[Model & Relasi](/id/guide/models)** - Memahami model inti Laravel Nusa
- **[Contoh Model Kustom](/id/examples/custom-models)** - Contoh praktis penggunaan *trait*
- **[Contoh Formulir Alamat](/id/examples/address-forms)** - Membangun formulir dengan wilayah administratif