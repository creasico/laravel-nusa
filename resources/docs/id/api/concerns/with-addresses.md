# Trait WithAddresses

Trait `WithAddresses` menambahkan beberapa relasi alamat polimorfik ke model Anda, memungkinkan mereka untuk memiliki beberapa alamat terkait dengan data wilayah administratif Indonesia yang lengkap.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithAddresses
```

## Penggunaan

### Implementasi Dasar

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithAddresses;

class Company extends Model
{
    use WithAddresses;
    
    protected $fillable = [
        'name',
        'description'
    ];
}
```

### Pengaturan Database

Trait ini menggunakan tabel alamat bawaan Laravel Nusa. Pastikan Anda telah mempublikasikan dan menjalankan migrasi:

```bash
php artisan vendor:publish --tag=creasi-migrations
php artisan migrate
```

## Fitur

### Relasi Alamat Ganda

Mengakses semua alamat yang terkait:

```php
$company = Company::find(1);

// Headquarters
$company->addresses()->create([
    'type' => 'headquarters',
    'address_line' => 'Jl. Sudirman No. 123',
    'province_code' => '31',
    'regency_code' => '31.71',
    'district_code' => '31.71.01',
    'village_code' => '31.71.01.1001'
]);

// Branch office
$company->addresses()->create([
    'type' => 'branch',
    'address_line' => 'Jl. Malioboro No. 456',
    'province_code' => '34',
    'regency_code' => '34.71',
    'district_code' => '34.71.02',
    'village_code' => '34.71.02.1005'
]);

// Warehouse
$company->addresses()->create([
    'type' => 'warehouse',
    'address_line' => 'Jl. Industri No. 789',
    'province_code' => '33',
    'regency_code' => '33.74',
    'district_code' => '33.74.05',
    'village_code' => '33.74.05.1010'
]);
```

## Contoh Penggunaan Umum

### 1. Bisnis Multi-Lokasi

```php
class Company extends Model
{
    use WithAddresses;
    
    protected $fillable = ['name', 'description'];
    
    public function getHeadquartersAttribute()
    {
        return $this->addresses()->where('type', 'headquarters')->first();
    }
    
    public function getBranchesAttribute()
    {
        return $this->addresses()->where('type', 'branch')->get();
    }
    
    public function getWarehousesAttribute()
    {
        return $this->addresses()->where('type', 'warehouse')->get();
    }
    
    public function scopeWithLocationsIn($query, $provinceCode)
    {
        return $query->whereHas('addresses', function ($q) use ($provinceCode) {
            $q->where('province_code', $provinceCode);
        });
    }
    
    public function getLocationCountAttribute()
    {
        return $this->addresses()->count();
    }
    
    public function getCoverageAreasAttribute()
    {
        return $this->addresses()
            ->with('province')
            ->get()
            ->pluck('province.name')
            ->unique()
            ->values();
    }
}

// Usage
$company = Company::with(['addresses.village.district.regency.province'])->first();

echo "Headquarters: " . $company->headquarters?->address_line;
echo "Branches: " . $company->branches->count();
echo "Coverage: " . $company->coverage_areas->implode(', ');
```

### 2. Manajemen Pelanggan

```php
class Customer extends Model
{
    use WithAddresses;
    
    protected $fillable = [
        'name',
        'email',
        'phone'
    ];
    
    public function getHomeAddressAttribute()
    {
        return $this->addresses()->where('type', 'home')->first();
    }
    
    public function getOfficeAddressAttribute()
    {
        return $this->addresses()->where('type', 'office')->first();
    }
    
    public function getShippingAddressesAttribute()
    {
        return $this->addresses()->where('type', 'shipping')->get();
    }
    
    public function addShippingAddress(array $addressData)
    {
        return $this->addresses()->create(array_merge($addressData, [
            'type' => 'shipping'
        ]));
    }
    
    public function getPreferredShippingAddressAttribute()
    {
        return $this->addresses()
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first() ?? $this->home_address;
    }
    
    public function setDefaultShippingAddress($addressId)
    {
        // Remove default from all shipping addresses
        $this->addresses()
            ->where('type', 'shipping')
            ->update(['is_default' => false]);
        
        // Set new default
        return $this->addresses()
            ->where('id', $addressId)
            ->where('type', 'shipping')
            ->update(['is_default' => true]);
    }
}

// Usage
$customer = Customer::first();

$customer->addShippingAddress([
    'address_line' => 'Jl. Delivery No. 123',
    'village_code' => '33.74.01.1001',
    'district_code' => '33.74.01',
    'regency_code' => '33.74',
    'province_code' => '33'
]);

$shippingAddress = $customer->preferred_shipping_address;
```

### 3. Manajemen Acara

```php
class Event extends Model
{
    use WithAddresses;
    
    protected $fillable = [
        'title',
        'description',
        'event_date'
    ];
    
    protected $casts = [
        'event_date' => 'datetime'
    ];
    
    public function getVenuesAttribute()
    {
        return $this->addresses()->where('type', 'venue')->get();
    }
    
    public function getAccommodationsAttribute()
    {
        return $this->addresses()->where('type', 'accommodation')->get();
    }
    
    public function addVenue(array $venueData)
    {
        return $this->addresses()->create(array_merge($venueData, [
            'type' => 'venue'
        ]));
    }
    
    public function addAccommodation(array $accommodationData)
    {
        return $this->addresses()->create(array_merge($accommodationData, [
            'type' => 'accommodation'
        ]));
    }
    
    public function getLocationSummaryAttribute()
    {
        $venues = $this->venues;
        
        if ($venues->isEmpty()) {
            return 'Venues TBA';
        }
        
        $cities = $venues->map(function ($venue) {
            return $venue->regency->name;
        })->unique();
        
        return $cities->count() === 1 
            ? $cities->first()
            : $cities->count() . ' cities';
    }
}
```

### 4. Manajemen Logistik

```php
class LogisticsProvider extends Model
{
    use WithAddresses;
    
    protected $fillable = [
        'name',
        'service_type'
    ];
    
    public function getWarehousesAttribute()
    {
        return $this->addresses()->where('type', 'warehouse')->get();
    }
    
    public function getDistributionCentersAttribute()
    {
        return $this->addresses()->where('type', 'distribution_center')->get();
    }
    
    public function getServiceAreasAttribute()
    {
        return $this->addresses()
            ->with('province')
            ->get()
            ->groupBy('province.name')
            ->map(function ($addresses, $provinceName) {
                return [
                    'province' => $provinceName,
                    'locations' => $addresses->count(),
                    'types' => $addresses->pluck('type')->unique()->values()
                ];
            });
    }
    
    public function canServeLocation($provinceCode, $regencyCode = null)
    {
        $query = $this->addresses()->where('province_code', $provinceCode);
        
        if ($regencyCode) {
            $query->where('regency_code', $regencyCode);
        }
        
        return $query->exists();
    }
    
    public function getNearestFacility($provinceCode, $regencyCode, $type = null)
    {
        $query = $this->addresses()
            ->where('province_code', $provinceCode)
            ->where('regency_code', $regencyCode);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->first();
    }
}

// Usage
$provider = LogisticsProvider::first();

$canServe = $provider->canServeLocation('33', '33.74');
$warehouse = $provider->getNearestFacility('33', '33.74', 'warehouse');
$serviceAreas = $provider->service_areas;
```

## Penggunaan Lanjutan

### Manajemen Tipe Alamat

```php
class AddressableModel extends Model
{
    use WithAddresses;
    
    const ADDRESS_TYPES = [
        'home' => 'Home Address',
        'office' => 'Office Address',
        'shipping' => 'Shipping Address',
        'billing' => 'Billing Address',
        'warehouse' => 'Warehouse',
        'branch' => 'Branch Office',
        'headquarters' => 'Headquarters'
    ];
    
    public function getAddressesByTypeAttribute()
    {
        return $this->addresses->groupBy('type');
    }
    
    public function getAddressTypesAttribute()
    {
        return $this->addresses->pluck('type')->unique()->values();
    }
    
    public function hasAddressType($type)
    {
        return $this->addresses()->where('type', $type)->exists();
    }
    
    public function getAddressByType($type)
    {
        return $this->addresses()->where('type', $type)->get();
    }
    
    public function removeAddressType($type)
    {
        return $this->addresses()->where('type', $type)->delete();
    }
}
```

### Operasi Alamat Massal

```php
class AddressManager
{
    public static function bulkCreateAddresses($model, array $addressesData)
    {
        $addresses = collect($addressesData)->map(function ($data) use ($model) {
            return array_merge($data, [
                'addressable_id' => $model->id,
                'addressable_type' => get_class($model),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });
        
        return \DB::table('addresses')->insert($addresses->toArray());
    }
    
    public static function syncAddresses($model, array $addressesData)
    {
        // Delete existing addresses
        $model->addresses()->delete();
        
        // Create new addresses
        return static::bulkCreateAddresses($model, $addressesData);
    }
    
    public static function getLocationStatistics($modelClass)
    {
        return $modelClass::with('addresses.province')
            ->get()
            ->flatMap(function ($model) {
                return $model->addresses;
            })
            ->groupBy('province.name')
            ->map(function ($addresses, $provinceName) {
                return [
                    'province' => $provinceName,
                    'count' => $addresses->count(),
                    'types' => $addresses->pluck('type')->unique()->values()
                ];
            });
    }
}
```

## Validasi

### Validasi Alamat Ganda

```php
class CompanyAddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'addresses' => 'required|array|min:1',
            'addresses.*.type' => 'required|string|in:headquarters,branch,warehouse',
            'addresses.*.address_line' => 'required|string|max:255',
            'addresses.*.village_code' => 'required|exists:nusa.villages,code',
            'addresses.*.postal_code' => 'nullable|string|size:5'
        ];
    }
    
    public function messages()
    {
        return [
            'addresses.*.type.in' => 'Address type must be headquarters, branch, or warehouse.',
            'addresses.*.village_code.exists' => 'The selected village is invalid.'
        ];
    }
}
```

### Validasi Tipe Alamat Unik

```php
class CustomerAddressRequest extends FormRequest
{
    public function rules()
    {
        $customerId = $this->route('customer')->id ?? null;
        
        return [
            'type' => [
                'required',
                'string',
                Rule::unique('addresses')
                    ->where('addressable_type', Customer::class)
                    ->where('addressable_id', $customerId)
                    ->ignore($this->address)
            ],
            'address_line' => 'required|string|max:255',
            'village_code' => 'required|exists:nusa.villages,code'
        ];
    }
}
```

## Tips Kinerja

### 1. Eager Loading

```php
// Good
$companies = Company::with([
    'addresses.village.district.regency.province'
])->get();

// Bad - N+1 queries
$companies = Company::all();
foreach ($companies as $company) {
    foreach ($company->addresses as $address) {
        echo $address->village->name; // Multiple queries
    }
}
```

### 2. Memuat Berdasarkan Tipe secara Selektif

```php
$companies = Company::with([
    'addresses' => function ($query) {
        $query->where('type', 'headquarters')
              ->with('village.regency.province');
    }
])->get();
```

### 3. Menghitung Alamat

```php
$companies = Company::withCount([
    'addresses',
    'addresses as branches_count' => function ($query) {
        $query->where('type', 'branch');
    },
    'addresses as warehouses_count' => function ($query) {
        $query->where('type', 'warehouse');
    }
])->get();
```

## Dokumentasi Terkait

- **[Trait WithAddress](/id/api/concerns/with-address)** - Untuk dukungan alamat tunggal
- **[Model Address](/id/api/models/address)** - Dokumentasi lengkap model Address
- **[Panduan Manajemen Alamat](/id/guide/addresses)** - Panduan lengkap fungsionalitas alamat
- **[Contoh Formulir Alamat](/id/examples/address-forms)** - Membangun formulir alamat