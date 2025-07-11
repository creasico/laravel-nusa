# Model Alamat

Model `Address` menyediakan sistem manajemen alamat lengkap yang terintegrasi dengan data administratif Indonesia. Model ini dirancang untuk menyimpan alamat pengguna dengan validasi dan relasi yang tepat ke wilayah administratif.

## Referensi Kelas

```php
namespace Creasi\Nusa\Models;

class Address extends Model
{
    // Implementasi model
}
```

## Atribut

### Atribut Utama

| Atribut | Tipe | Deskripsi | Contoh |
|-----------|------|-------------|---------|
| `id` | `bigint` | Kunci utama (auto-increment) | `1` |
| `user_id` | `bigint` | Kunci asing ke tabel pengguna | `123` |
| `name` | `string` | Nama penerima | `"John Doe"` |
| `phone` | `string` | Nomor telepon kontak | `"081234567890"` |
| `province_code` | `string` | Kode provinsi (Kunci Asing) | `"33"` |
| `regency_code` | `string` | Kode kabupaten/kota (Kunci Asing) | `"33.75"` |
| `district_code` | `string` | Kode kecamatan (Kunci Asing) | `"33.75.01"` |
| `village_code` | `string` | Kode desa/kelurahan (Kunci Asing) | `"33.75.01.1002"` |
| `address_line` | `text` | Alamat rinci | `"Jl. Merdeka No. 123"` |
| `postal_code` | `string` | Kode pos 5 digit | `"51111"` |
| `is_default` | `boolean` | Tanda alamat utama | `true` |

### Atribut Terkomputasi

| Atribut | Tipe | Deskripsi |
|-----------|------|-------------|
| `full_address` | `string` | Alamat lengkap yang diformat |

## Relasi

### Belongs To

```php
// Dapatkan pengguna yang memiliki alamat ini
$address->user; // Model User

// Dapatkan wilayah administratif
$address->province; // Model Province
$address->regency;  // Model Regency
$address->district; // Model District
$address->village;  // Model Village
```

### Metode Relasi

```php
// Relasi pengguna
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// Relasi wilayah administratif
public function province(): BelongsTo
{
    return $this->belongsTo(Province::class, 'province_code', 'code');
}

public function regency(): BelongsTo
{
    return $this->belongsTo(Regency::class, 'regency_code', 'code');
}

public function district(): BelongsTo
{
    return $this->belongsTo(District::class, 'district_code', 'code');
}

public function village(): BelongsTo
{
    return $this->belongsTo(Village::class, 'village_code', 'code');
}
```

## Scope

### Scope Alamat Utama

```php
// Dapatkan alamat utama
Address::default()->get();

// Dapatkan alamat bukan utama
Address::notDefault()->get();
```

### Scope Pengguna

```php
// Dapatkan alamat untuk pengguna tertentu
Address::forUser($userId)->get();
```

## Contoh Penggunaan

### Membuat Alamat

```php
use Creasi\Nusa\Models\Address;

// Buat alamat baru
$address = Address::create([
    'user_id' => 1,
    'name' => 'John Doe',
    'phone' => '081234567890',
    'province_code' => '33',
    'regency_code' => '33.75',
    'district_code' => '33.75.01',
    'village_code' => '33.75.01.1002',
    'address_line' => 'Jl. Merdeka No. 123',
    'postal_code' => '51111',
    'is_default' => true,
]);

// Buat melalui relasi pengguna
$user = User::find(1);
$address = $user->addresses()->create([
    'name' => 'Jane Doe',
    'phone' => '081234567891',
    'province_code' => '33',
    'regency_code' => '33.75',
    'district_code' => '33.75.01',
    'village_code' => '33.75.01.1002',
    'address_line' => 'Jl. Sudirman No. 456',
    'is_default' => false,
]);
```

### Mengambil Data Alamat

```php
// Dapatkan semua alamat untuk seorang pengguna
$userAddresses = Address::where('user_id', 1)->get();

// Dapatkan alamat utama pengguna
$defaultAddress = Address::where('user_id', 1)
    ->where('is_default', true)
    ->first();

// Dapatkan alamat dengan data administratif
$addresses = Address::with(['province', 'regency', 'district', 'village'])
    ->where('user_id', 1)
    ->get();

// Cari alamat berdasarkan nama atau telepon
$addresses = Address::where('user_id', 1)
    ->where(function ($query) use ($search) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
    })
    ->get();
```

### Bekerja dengan Alamat Lengkap

```php
$address = Address::with(['village', 'district', 'regency', 'province'])
    ->find(1);

// Dapatkan alamat lengkap yang diformat
$fullAddress = $address->full_address;
// "Jl. Merdeka No. 123, Medono, Pekalongan Barat, Kota Pekalongan, Jawa Tengah 51111"

// Membangun alamat secara manual
$addressParts = [
    $address->address_line,
    $address->village->name,
    $address->district->name,
    $address->regency->name,
    $address->province->name,
    $address->postal_code
];
$fullAddress = implode(', ', array_filter($addressParts));
```

## Manajemen Alamat

### Penanganan Alamat Utama

```php
// Atur alamat sebagai utama (batalkan yang lain)
function setDefaultAddress($userId, $addressId) {
    // Batalkan utama saat ini
    Address::where('user_id', $userId)
        ->where('is_default', true)
        ->update(['is_default' => false]);
    
    // Atur utama baru
    Address::where('id', $addressId)
        ->where('user_id', $userId)
        ->update(['is_default' => true]);
}

// Dapatkan alamat utama pengguna
function getDefaultAddress($userId) {
    return Address::where('user_id', $userId)
        ->where('is_default', true)
        ->with(['province', 'regency', 'district', 'village'])
        ->first();
}
```

### Validasi Alamat

```php
// Validasi konsistensi alamat
function validateAddressConsistency($addressData) {
    $village = Village::find($addressData['village_code']);
    
    if (!$village) {
        return false;
    }
    
    return $village->district_code === $addressData['district_code'] &&
           $village->regency_code === $addressData['regency_code'] &&
           $village->province_code === $addressData['province_code'];
}

// Isi otomatis kode pos dari desa/kelurahan
function autoFillPostalCode($addressData) {
    if (empty($addressData['postal_code'])) {
        $village = Village::find($addressData['village_code']);
        if ($village && $village->postal_code) {
            $addressData['postal_code'] = $village->postal_code;
        }
    }
    
    return $addressData;
}
```

## Integrasi Trait

### Trait HasAddresses

Untuk model yang dapat memiliki banyak alamat:

```php
use Creasi\Nusa\Contracts\HasAddresses;
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model implements HasAddresses
{
    use WithAddresses;
}

// Penggunaan
$user = User::find(1);
$addresses = $user->addresses;
$defaultAddress = $user->defaultAddress;
$user->setDefaultAddress($address);
```

### Trait HasAddress

Untuk model dengan satu alamat:

```php
use Creasi\Nusa\Contracts\HasAddress;
use Creasi\Nusa\Models\Concerns\WithAddress;

class Store extends Model implements HasAddress
{
    use WithAddress;
    
    protected $fillable = [
        'name',
        'description',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'address_line',
        'postal_code',
    ];
}

// Penggunaan
$store = Store::create([
    'name' => 'My Store',
    'province_code' => '33',
    'regency_code' => '33.75',
    'district_code' => '33.75.01',
    'village_code' => '33.75.01.1002',
    'address_line' => 'Jl. Toko No. 789',
]);

echo $store->full_address;
```

## Validasi Formulir

### Validasi Request

```php
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_code' => 'required|exists:nusa.provinces,code',
            'regency_code' => [
                'required',
                'exists:nusa.regencies,code',
                function ($attribute, $value, $fail) {
                    $regency = Regency::find($value);
                    if (!$regency || $regency->province_code !== $this->province_code) {
                        $fail('Kabupaten/kota yang dipilih tidak valid untuk provinsi ini.');
                    }
                },
            ],
            'district_code' => [
                'required',
                'exists:nusa.districts,code',
                function ($attribute, $value, $fail) {
                    $district = District::find($value);
                    if (!$district || $district->regency_code !== $this->regency_code) {
                        $fail('Kecamatan yang dipilih tidak valid untuk kabupaten/kota ini.');
                    }
                },
            ],
            'village_code' => [
                'required',
                'exists:nusa.villages,code',
                function ($attribute, $value, $fail) {
                    $village = Village::find($value);
                    if (!$village || $village->district_code !== $this->district_code) {
                        $fail('Desa/kelurahan yang dipilih tidak valid untuk kecamatan ini.');
                    }
                },
            ],
            'address_line' => 'required|string|max:500',
            'postal_code' => 'nullable|string|size:5',
            'is_default' => 'boolean',
        ];
    }
}
```

### Aturan Validasi Kustom

```php
use Illuminate\Contracts\Validation\Rule;

class ValidIndonesianAddress implements Rule
{
    public function passes($attribute, $value)
    {
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

## Skema Database

```sql
CREATE TABLE addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    province_code VARCHAR(2) NOT NULL,
    regency_code VARCHAR(5) NOT NULL,
    district_code VARCHAR(8) NOT NULL,
    village_code VARCHAR(13) NOT NULL,
    address_line TEXT NOT NULL,
    postal_code VARCHAR(5) NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_addresses_user (user_id),
    INDEX idx_addresses_default (user_id, is_default),
    INDEX idx_addresses_province (province_code),
    INDEX idx_addresses_regency (regency_code),
    INDEX idx_addresses_district (district_code),
    INDEX idx_addresses_village (village_code),
    INDEX idx_addresses_postal (postal_code)
);
```

## Tips Kinerja

### Kueri yang Efisien

```php
// Baik: Muat dengan relasi tertentu
$addresses = Address::with(['province:code,name', 'regency:code,name'])
    ->where('user_id', $userId)
    ->get();

// Baik: Pilih kolom tertentu
$addresses = Address::select('id', 'name', 'address_line', 'is_default')
    ->where('user_id', $userId)
    ->get();

// Hindari: Memuat semua relasi
$addresses = Address::with(['province', 'regency', 'district', 'village'])
    ->get(); // Memuat terlalu banyak data
```

### Caching

```php
// Cache alamat pengguna
function getUserAddresses($userId) {
    $cacheKey = "user.{$userId}.addresses";
    
    return Cache::remember($cacheKey, 1800, function () use ($userId) {
        return Address::with(['province', 'regency', 'district', 'village'])
            ->where('user_id', $userId)
            ->get();
    });
}
```

## Model Terkait

- **[Model Province](/id/api/models/province)** - Referensi wilayah administratif
- **[Model Regency](/id/api/models/regency)** - Referensi wilayah administratif
- **[Model District](/id/api/models/district)** - Referensi wilayah administratif
- **[Model Village](/id/api/models/village)** - Referensi wilayah administratif