# Manajemen Alamat

**Revolusikan penanganan alamat Anda** dengan sistem manajemen alamat cerdas Laravel Nusa. Dari alur checkout e-commerce hingga manajemen lokasi enterprise, solusi kami mengubah persyaratan alamat Indonesia yang kompleks menjadi pengalaman pengguna yang mulus.

## Mengapa Manajemen Alamat Laravel Nusa?

### 🎯 **Manfaat Kritis Bisnis**

**Form Alamat yang Disederhanakan**: Input yang efisien dengan dropdown bertingkat
**Akurasi Pengiriman yang Ditingkatkan**: Validasi alamat terhadap data resmi
**Struktur Data yang Konsisten**: Menangani hierarki administratif Indonesia dengan benar
**Pengalaman Pengguna yang Lebih Baik**: Proses pemilihan alamat yang intuitif

### 🚀 **Fitur Siap Enterprise**

- **Dukungan Multi-Alamat** - Pelanggan dapat mengelola beberapa alamat pengiriman
- **Validasi Alamat** - Verifikasi real-time terhadap data resmi
- **Auto-Complete Cerdas** - Dropdown bertingkat dengan saran cerdas
- **Integrasi Fleksibel** - Bekerja dengan model pengguna yang sudah ada

## Solusi Bisnis Nyata

### 🛒 **Aplikasi E-Commerce**

**Tantangan**: Form alamat yang kompleks dapat membingungkan pelanggan saat checkout, terutama dengan struktur administratif multi-level Indonesia.

**Solusi**: Sederhanakan proses checkout dengan manajemen alamat cerdas:

```php
// Manajemen alamat cerdas untuk pelanggan
class Customer extends Model
{
    use WithAddresses;

    public function getDefaultShippingAddress()
    {
        return $this->addresses()
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first();
    }

    public function calculateShippingCost($productWeight)
    {
        $address = $this->getDefaultShippingAddress();
        $zone = $address->getShippingZone();

        return $zone->calculateCost($productWeight);
    }
}
```

**Manfaat**:
- Mengurangi kompleksitas form dan kebingungan pengguna
- Meningkatkan kualitas data alamat dan keberhasilan pengiriman
- Format alamat yang konsisten di seluruh aplikasi
- Pengalaman pelanggan yang lebih baik saat checkout

### 🏢 **Manajemen Bisnis Multi-Lokasi**

**Tantangan**: Organisasi dengan beberapa lokasi perlu mengelola alamat secara konsisten di berbagai kantor, gudang, dan pusat layanan.

**Solusi**: Manajemen alamat terpusat untuk struktur organisasi yang kompleks:

```php
// Manajemen bisnis multi-lokasi
class Company extends Model
{
    use WithAddresses;

    public function getLocationsByType($type)
    {
        return $this->addresses()
            ->where('type', $type)
            ->with(['village.regency.province'])
            ->get();
    }

    public function getRegionalCoverage()
    {
        return $this->addresses()
            ->with('province')
            ->get()
            ->groupBy('province.name')
            ->map(function ($addresses, $province) {
                return [
                    'province' => $province,
                    'locations' => $addresses->count(),
                    'types' => $addresses->pluck('type')->unique()
                ];
            });
    }
}
```

**Manfaat**:
- Manajemen terpusat semua lokasi bisnis
- Analisis dan pelaporan cakupan regional
- Data alamat yang konsisten di seluruh organisasi
- Pelaporan kepatuhan dan administratif yang disederhanakan

## Setup Cepat (2 Menit)

### 1. Instal Tabel Alamat

```bash
php artisan vendor:publish --tag=creasi-migrations
php artisan migrate
```

### 2. Tambahkan ke Model Anda

```php
use Creasi\Nusa\Models\Concerns\WithAddress;

class User extends Model
{
    use WithAddress; // Dukungan alamat tunggal
}
```

### 3. Mulai Menggunakan

```php
$user->address()->create([
    'address_line' => 'Jl. Sudirman No. 123',
    'village_code' => '33.74.01.1001',
    'postal_code' => '50132'
]);
```

## Fitur Bisnis Lanjutan

### 🎯 **Validasi Alamat Cerdas**

Pastikan akurasi pengiriman 100% dengan validasi cerdas:
```php
// Validasi alamat otomatis
class AddressValidator
{
    public function validateAddress(array $addressData)
    {
        $village = Village::find($addressData['village_code']);

        if (!$village) {
            throw new InvalidAddressException('Kode desa tidak valid');
        }

        // Auto-koreksi kode parent
        $addressData['district_code'] = $village->district_code;
        $addressData['regency_code'] = $village->regency_code;
        $addressData['province_code'] = $village->province_code;

        // Auto-fill kode pos
        if (empty($addressData['postal_code'])) {
            $addressData['postal_code'] = $village->postal_code;
        }

        return $addressData;
    }
}
```

### 📊 **Analitik Alamat Enterprise**

Dapatkan wawasan bisnis dari data alamat Anda:

```php
// Analitik alamat untuk bisnis
class AddressAnalytics
{
    public function getCustomerDistribution()
    {
        return Customer::with('addresses.village.province')
            ->get()
            ->flatMap->addresses
            ->groupBy('village.province.name')
            ->map->count()
            ->sortDesc();
    }

    public function getDeliveryZonePerformance()
    {
        return Order::with('shipping_address.village.regency')
            ->where('status', 'delivered')
            ->get()
            ->groupBy('shipping_address.village.regency.name')
            ->map(function ($orders) {
                return [
                    'total_orders' => $orders->count(),
                    'avg_delivery_time' => $orders->avg('delivery_days'),
                    'success_rate' => $orders->where('delivered_on_time', true)->count() / $orders->count()
                ];
            });
    }
}
```

## Integrasi Trait

### WithAddress (Alamat Tunggal)

```php
use Creasi\Nusa\Models\Concerns\WithAddress;

class Store extends Model
{
    use WithAddress;

    protected $fillable = ['name', 'village_code', 'address_line'];
}

// Penggunaan
$store = Store::create([
    'name' => 'Toko ABC',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Pahlawan No. 456'
]);

echo $store->address->full_address; // Alamat lengkap terformat
```

### WithAddresses (Multiple Alamat)

```php
use Creasi\Nusa\Models\Concerns\WithAddresses;

class Customer extends Model
{
    use WithAddresses;
}

// Manajemen multiple alamat
$customer->addresses()->create([
    'type' => 'shipping',
    'name' => 'John Doe',
    'phone' => '081234567890',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123',
    'is_default' => true
]);
```

## Form Alamat Cerdas

### Controller API

```php
class AddressApiController extends Controller
{
    public function getRegencies($provinceCode)
    {
        return Regency::where('province_code', $provinceCode)
            ->select('code', 'name')
            ->orderBy('name')
            ->get();
    }

    public function getDistricts($regencyCode)
    {
        return District::where('regency_code', $regencyCode)
            ->select('code', 'name')
            ->orderBy('name')
            ->get();
    }

    public function getVillages($districtCode)
    {
        return Village::where('district_code', $districtCode)
            ->select('code', 'name', 'postal_code')
            ->orderBy('name')
            ->get();
    }
}
```

### Frontend Integration

```javascript
// Vue.js component example
export default {
    data() {
        return {
            provinces: [],
            regencies: [],
            districts: [],
            villages: [],
            form: {
                province_code: '',
                regency_code: '',
                district_code: '',
                village_code: '',
                address_line: '',
                postal_code: ''
            }
        }
    },

    methods: {
        async loadRegencies() {
            if (this.form.province_code) {
                this.regencies = await this.fetchRegencies(this.form.province_code);
                this.resetLowerLevels(['regency', 'district', 'village']);
            }
        },

        async loadDistricts() {
            if (this.form.regency_code) {
                this.districts = await this.fetchDistricts(this.form.regency_code);
                this.resetLowerLevels(['district', 'village']);
            }
        },

        async loadVillages() {
            if (this.form.district_code) {
                this.villages = await this.fetchVillages(this.form.district_code);
                this.resetLowerLevels(['village']);
            }
        },

        onVillageSelected() {
            const village = this.villages.find(v => v.code === this.form.village_code);
            if (village) {
                this.form.postal_code = village.postal_code;
            }
        }
    }
}
```

## Best Practices

### 1. Optimasi Performa

```php
// Cache data wilayah yang sering diakses
$provinces = Cache::remember('provinces_dropdown', 3600, function () {
    return Province::select('code', 'name')->orderBy('name')->get();
});

// Gunakan eager loading untuk menghindari N+1 queries
$addresses = $user->addresses()
    ->with(['village.district.regency.province'])
    ->get();
```

### 2. Validasi Data

```php
// Custom validation rule
class ValidIndonesianAddress implements Rule
{
    public function passes($attribute, $value)
    {
        return Village::where('code', $value)->exists();
    }

    public function message()
    {
        return 'Kode desa tidak valid.';
    }
}
```

### 3. Error Handling

```php
try {
    $address = $user->addresses()->create($addressData);
} catch (QueryException $e) {
    if ($e->getCode() === '23000') {
        throw new ValidationException('Kode desa tidak valid atau tidak ditemukan.');
    }
    throw $e;
}
```

## Langkah Selanjutnya

Jelajahi fitur lanjutan lainnya:

- **[Contoh Form Alamat](/id/examples/address-forms)** - Implementasi form lengkap
- **[API Reference](/id/api/overview)** - Endpoint untuk data wilayah
- **[Model Concerns](/id/api/concerns/)** - Trait dan helper lainnya
- **[Referensi API](/id/api/models/address)** - Dokumentasi model alamat
- **[Contoh](/id/examples/basic-usage)** - Contoh implementasi praktis
