# Model Kustom

Panduan ini menunjukkan cara memperluas Laravel Nusa dengan model kustom dan mengintegrasikan data administratif Indonesia ke dalam model domain aplikasi Anda.

## Memperluas Model Dasar

### Model Provinsi Kustom

```php
namespace App\Models;

use Creasi\Nusa\Models\Province as BaseProvince;

class Province extends BaseProvince
{
    // Tambahkan atribut kustom
    protected $appends = ['region_name', 'is_java'];
    
    // Custom accessor
    public function getRegionNameAttribute(): string
    {
        $regions = [
            'Sumatra' => ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21'],
            'Java' => ['31', '32', '33', '34', '35', '36'],
            'Kalimantan' => ['61', '62', '63', '64', '65'],
            'Sulawesi' => ['71', '72', '73', '74', '75', '76'],
            'Indonesia Timur' => ['81', '82', '91', '94', '95', '96'],
        ];
        
        foreach ($regions as $region => $codes) {
            if (in_array($this->code, $codes)) {
                return $region;
            }
        }
        
        return 'Tidak Diketahui';
    }
    
    // Custom accessor
    public function getIsJavaAttribute(): bool
    {
        return in_array($this->code, ['31', '32', '33', '34', '35', '36']);
    }
    
    // Custom scope
    public function scopeJava($query)
    {
        return $query->whereIn('code', ['31', '32', '33', '34', '35', '36']);
    }
    
    public function scopeOutsideJava($query)
    {
        return $query->whereNotIn('code', ['31', '32', '33', '34', '35', '36']);
    }
    
    public function scopeByRegion($query, string $region)
    {
        $regionCodes = [
            'sumatra' => ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21'],
            'java' => ['31', '32', '33', '34', '35', '36'],
            'kalimantan' => ['61', '62', '63', '64', '65'],
            'sulawesi' => ['71', '72', '73', '74', '75', '76'],
            'eastern' => ['81', '82', '91', '94', '95', '96'],
        ];
        
        $codes = $regionCodes[strtolower($region)] ?? [];
        
        return $query->whereIn('code', $codes);
    }
    
    // Relasi kustom
    public function businesses()
    {
        return $this->hasMany(Business::class, 'province_code', 'code');
    }
    
    public function users()
    {
        return $this->hasMany(User::class, 'province_code', 'code');
    }
}
```

### Model Kabupaten/Kota Kustom

```php
namespace App\Models;

use Creasi\Nusa\Models\Regency as BaseRegency;

class Regency extends BaseRegency
{
    protected $appends = ['type', 'is_city'];
    
    // Tentukan apakah ini kota atau kabupaten
    public function getTypeAttribute(): string
    {
        return str_starts_with($this->name, 'Kota') ? 'Kota' : 'Kabupaten';
    }
    
    public function getIsCityAttribute(): bool
    {
        return str_starts_with($this->name, 'Kota');
    }
    
    // Scope untuk filter berdasarkan tipe
    public function scopeCities($query)
    {
        return $query->where('name', 'LIKE', 'Kota%');
    }
    
    public function scopeRegencies($query)
    {
        return $query->where('name', 'LIKE', 'Kabupaten%');
    }
    
    // Relasi kustom
    public function branches()
    {
        return $this->hasMany(Branch::class, 'regency_code', 'code');
    }
    
    public function deliveryAreas()
    {
        return $this->hasMany(DeliveryArea::class, 'regency_code', 'code');
    }
}
```

### Model Kecamatan Kustom

```php
namespace App\Models;

use Creasi\Nusa\Models\District as BaseDistrict;

class District extends BaseDistrict
{
    // Relasi kustom
    public function serviceAreas()
    {
        return $this->hasMany(ServiceArea::class, 'district_code', 'code');
    }
    
    public function agents()
    {
        return $this->hasMany(Agent::class, 'district_code', 'code');
    }
    
    // Method untuk menghitung coverage area
    public function getCoveragePercentage(): float
    {
        $totalVillages = $this->villages()->count();
        $coveredVillages = $this->villages()
            ->whereHas('serviceAreas')
            ->count();
            
        return $totalVillages > 0 ? ($coveredVillages / $totalVillages) * 100 : 0;
    }
}
```

### Model Kelurahan/Desa Kustom

```php
namespace App\Models;

use Creasi\Nusa\Models\Village as BaseVillage;

class Village extends BaseVillage
{
    protected $appends = ['type', 'is_urban'];
    
    // Tentukan apakah ini kelurahan atau desa
    public function getTypeAttribute(): string
    {
        return str_starts_with($this->name, 'Kelurahan') ? 'Kelurahan' : 'Desa';
    }
    
    public function getIsUrbanAttribute(): bool
    {
        return str_starts_with($this->name, 'Kelurahan');
    }
    
    // Scope untuk filter berdasarkan tipe
    public function scopeUrban($query)
    {
        return $query->where('name', 'LIKE', 'Kelurahan%');
    }
    
    public function scopeRural($query)
    {
        return $query->where('name', 'NOT LIKE', 'Kelurahan%');
    }
    
    // Relasi kustom
    public function customers()
    {
        return $this->hasMany(Customer::class, 'village_code', 'code');
    }
    
    public function deliveryPoints()
    {
        return $this->hasMany(DeliveryPoint::class, 'village_code', 'code');
    }
    
    // Method untuk validasi kode pos
    public function validatePostalCode(string $postalCode): bool
    {
        return $this->postal_code === $postalCode;
    }
}
```

## Integrasi dengan Model Aplikasi

### Model User dengan Alamat

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password',
        'province_code', 'regency_code', 'district_code', 'village_code',
        'address_line', 'postal_code'
    ];
    
    // Relasi ke model Nusa
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }
    
    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_code', 'code');
    }
    
    public function district()
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }
    
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }
    
    // Accessor untuk alamat lengkap
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line,
            $this->village?->name,
            $this->district?->name,
            $this->regency?->name,
            $this->province?->name,
            $this->postal_code
        ]);
        
        return implode(', ', $parts);
    }
    
    // Scope untuk filter berdasarkan wilayah
    public function scopeInProvince($query, string $provinceCode)
    {
        return $query->where('province_code', $provinceCode);
    }
    
    public function scopeInRegency($query, string $regencyCode)
    {
        return $query->where('regency_code', $regencyCode);
    }
    
    public function scopeInJava($query)
    {
        return $query->whereIn('province_code', ['31', '32', '33', '34', '35', '36']);
    }
}
```

### Model Toko dengan Lokasi

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class Store extends Model
{
    protected $fillable = [
        'name', 'description', 'phone',
        'province_code', 'regency_code', 'district_code', 'village_code',
        'address_line', 'postal_code', 'latitude', 'longitude'
    ];
    
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
    
    // Relasi ke model Nusa
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }
    
    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_code', 'code');
    }
    
    public function district()
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }
    
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }
    
    // Scope untuk pencarian berdasarkan lokasi
    public function scopeNearby($query, float $latitude, float $longitude, int $radiusKm = 10)
    {
        $earthRadius = 6371; // km
        
        return $query->selectRaw("
            *, (
                {$earthRadius} * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
        ->having('distance', '<', $radiusKm)
        ->orderBy('distance');
    }
    
    public function scopeInArea($query, array $areaCodes, string $level = 'village')
    {
        $column = $level . '_code';
        return $query->whereIn($column, $areaCodes);
    }
    
    // Method untuk validasi alamat
    public function validateAddress(): bool
    {
        // Validasi hierarki alamat
        if (!$this->village) return false;
        if ($this->village->district_code !== $this->district_code) return false;
        if ($this->village->district->regency_code !== $this->regency_code) return false;
        if ($this->village->district->regency->province_code !== $this->province_code) return false;
        
        return true;
    }
}
```

## Model Domain-Spesifik

### Model Toko dengan Trait Alamat

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Contracts\HasAddress;
use Creasi\Nusa\Models\Concerns\WithAddress;

class Store extends Model implements HasAddress
{
    use WithAddress;

    protected $fillable = [
        'name',
        'description',
        'category',
        'phone',
        'email',
        'website',
        'opening_hours',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'address_line',
        'postal_code',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $appends = ['full_location', 'region'];

    // Custom accessor
    public function getFullLocationAttribute(): string
    {
        return $this->full_address;
    }

    public function getRegionAttribute(): string
    {
        return $this->province?->region_name ?? 'Tidak Diketahui';
    }

    // Scope untuk pencarian geografis
    public function scopeInRadius($query, float $lat, float $lng, int $radiusKm = 10)
    {
        $earthRadius = 6371; // km

        return $query->selectRaw("
            *, (
                {$earthRadius} * acos(
                    cos(radians(?)) *
                    cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(latitude))
                )
            ) AS distance
        ", [$lat, $lng, $lat])
        ->having('distance', '<', $radiusKm)
        ->orderBy('distance');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Relasi
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Method untuk menghitung jarak
    public function distanceTo(float $lat, float $lng): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 0;
        }

        $earthRadius = 6371; // km

        $latDelta = deg2rad($lat - $this->latitude);
        $lngDelta = deg2rad($lng - $this->longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
```

### Model Pengiriman dengan Validasi Alamat

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithAddress;

class Delivery extends Model
{
    use WithAddress;

    protected $fillable = [
        'order_id',
        'recipient_name',
        'recipient_phone',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'address_line',
        'postal_code',
        'delivery_notes',
        'status',
        'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';

    // Relasi
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scope
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    // Method untuk validasi alamat pengiriman
    public function validateDeliveryAddress(): array
    {
        $errors = [];

        // Validasi kode pos
        if ($this->postal_code && $this->village) {
            if ($this->village->postal_code !== $this->postal_code) {
                $errors[] = 'Kode pos tidak sesuai dengan kelurahan/desa';
            }
        }

        // Validasi hierarki alamat
        if (!$this->validateAddressHierarchy()) {
            $errors[] = 'Hierarki alamat tidak valid';
        }

        // Validasi kelengkapan alamat
        if (empty($this->address_line)) {
            $errors[] = 'Alamat lengkap harus diisi';
        }

        if (empty($this->recipient_name)) {
            $errors[] = 'Nama penerima harus diisi';
        }

        if (empty($this->recipient_phone)) {
            $errors[] = 'Nomor telepon penerima harus diisi';
        }

        return $errors;
    }

    // Method untuk estimasi biaya pengiriman
    public function calculateShippingCost(): int
    {
        $baseCost = 10000; // Biaya dasar

        // Tambahan biaya berdasarkan wilayah
        $regionMultiplier = match($this->province?->region_name) {
            'Java' => 1.0,
            'Sumatra' => 1.2,
            'Kalimantan' => 1.5,
            'Sulawesi' => 1.3,
            'Indonesia Timur' => 2.0,
            default => 1.0
        };

        // Tambahan biaya untuk desa (non-kelurahan)
        $ruralMultiplier = $this->village?->is_urban ? 1.0 : 1.1;

        return (int) ($baseCost * $regionMultiplier * $ruralMultiplier);
    }
}
```

### Model Area Layanan

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class ServiceArea extends Model
{
    protected $fillable = [
        'name',
        'description',
        'service_type',
        'coverage_level', // province, regency, district, village
        'coverage_codes', // JSON array of codes
        'is_active',
        'priority',
    ];

    protected $casts = [
        'coverage_codes' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    // Constants untuk level coverage
    const LEVEL_PROVINCE = 'province';
    const LEVEL_REGENCY = 'regency';
    const LEVEL_DISTRICT = 'district';
    const LEVEL_VILLAGE = 'village';

    // Scope
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByServiceType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('coverage_level', $level);
    }

    // Method untuk mengecek coverage
    public function coversAddress(string $provinceCode, string $regencyCode = null,
                                 string $districtCode = null, string $villageCode = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $codes = $this->coverage_codes ?? [];

        return match($this->coverage_level) {
            self::LEVEL_PROVINCE => in_array($provinceCode, $codes),
            self::LEVEL_REGENCY => in_array($regencyCode, $codes),
            self::LEVEL_DISTRICT => in_array($districtCode, $codes),
            self::LEVEL_VILLAGE => in_array($villageCode, $codes),
            default => false
        };
    }

    public function coversVillage(Village $village): bool
    {
        return $this->coversAddress(
            $village->district->regency->province_code,
            $village->district->regency_code,
            $village->district_code,
            $village->code
        );
    }

    // Method untuk mendapatkan wilayah yang dicakup
    public function getCoveredAreas()
    {
        $codes = $this->coverage_codes ?? [];

        return match($this->coverage_level) {
            self::LEVEL_PROVINCE => Province::whereIn('code', $codes)->get(),
            self::LEVEL_REGENCY => Regency::whereIn('code', $codes)->get(),
            self::LEVEL_DISTRICT => District::whereIn('code', $codes)->get(),
            self::LEVEL_VILLAGE => Village::whereIn('code', $codes)->get(),
            default => collect()
        };
    }

    // Method untuk menghitung coverage statistics
    public function getCoverageStats(): array
    {
        $coveredAreas = $this->getCoveredAreas();

        return [
            'total_areas' => $coveredAreas->count(),
            'coverage_level' => $this->coverage_level,
            'service_type' => $this->service_type,
            'is_active' => $this->is_active,
        ];
    }
}
```

## Service Classes

### Location Service

```php
namespace App\Services;

use Creasi\Nusa\Models\{Province, Regency, District, Village};
use Illuminate\Support\Collection;

class LocationService
{
    public function getLocationHierarchy(string $villageCode): ?array
    {
        $village = Village::with(['district.regency.province'])
            ->find($villageCode);

        if (!$village) {
            return null;
        }

        return [
            'village' => [
                'code' => $village->code,
                'name' => $village->name,
                'postal_code' => $village->postal_code,
            ],
            'district' => [
                'code' => $village->district->code,
                'name' => $village->district->name,
            ],
            'regency' => [
                'code' => $village->district->regency->code,
                'name' => $village->district->regency->name,
            ],
            'province' => [
                'code' => $village->district->regency->province->code,
                'name' => $village->district->regency->province->name,
            ],
            'full_address' => $this->buildFullAddress($village),
        ];
    }

    public function findNearestRegency(float $lat, float $lon): ?Regency
    {
        // Implementasi pencarian regency terdekat berdasarkan koordinat
        // Ini memerlukan data koordinat yang tidak tersedia di database default
        $regencies = Regency::all();

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($regencies as $regency) {
            // Gunakan koordinat pusat regency jika tersedia
            // Atau implementasi alternatif berdasarkan nama
            $distance = $this->calculateDistance($lat, $lon, $regency);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $regency;
            }
        }

        return $nearest;
    }

    public function validateAddressHierarchy(string $provinceCode, string $regencyCode,
                                           string $districtCode, string $villageCode): bool
    {
        $village = Village::with(['district.regency.province'])
            ->find($villageCode);

        if (!$village) {
            return false;
        }

        return $village->district_code === $districtCode &&
               $village->district->regency_code === $regencyCode &&
               $village->district->regency->province_code === $provinceCode;
    }

    public function searchLocations(string $query, int $limit = 10): array
    {
        $results = [
            'provinces' => Province::search($query)->limit($limit)->get(),
            'regencies' => Regency::search($query)->limit($limit)->get(),
            'districts' => District::search($query)->limit($limit)->get(),
            'villages' => Village::search($query)->limit($limit)->get(),
        ];

        return $results;
    }

    public function getRegionStatistics(): array
    {
        return [
            'total_provinces' => Province::count(),
            'total_regencies' => Regency::count(),
            'total_districts' => District::count(),
            'total_villages' => Village::count(),
            'java_provinces' => Province::java()->count(),
            'cities' => Regency::cities()->count(),
            'regencies' => Regency::regencies()->count(),
            'urban_villages' => Village::urban()->count(),
            'rural_villages' => Village::rural()->count(),
        ];
    }

    private function buildFullAddress(Village $village): string
    {
        $parts = [
            $village->name,
            $village->district->name,
            $village->district->regency->name,
            $village->district->regency->province->name,
        ];

        if ($village->postal_code) {
            $parts[] = $village->postal_code;
        }

        return implode(', ', $parts);
    }

    private function calculateDistance(float $lat, float $lon, Regency $regency): float
    {
        // Implementasi sederhana berdasarkan nama
        // Dalam implementasi nyata, Anda perlu data koordinat
        return rand(1, 1000); // Placeholder
    }
}
```

### Address Validation Service

```php
namespace App\Services;

use Creasi\Nusa\Models\{Province, Regency, District, Village};

class AddressValidationService
{
    public function validateComplete(array $addressData): array
    {
        $errors = [];

        // Validasi keberadaan kode
        if (!isset($addressData['province_code']) || empty($addressData['province_code'])) {
            $errors[] = 'Kode provinsi harus diisi';
        }

        if (!isset($addressData['regency_code']) || empty($addressData['regency_code'])) {
            $errors[] = 'Kode kabupaten/kota harus diisi';
        }

        if (!isset($addressData['district_code']) || empty($addressData['district_code'])) {
            $errors[] = 'Kode kecamatan harus diisi';
        }

        if (!isset($addressData['village_code']) || empty($addressData['village_code'])) {
            $errors[] = 'Kode kelurahan/desa harus diisi';
        }

        // Jika ada error dasar, return early
        if (!empty($errors)) {
            return $errors;
        }

        // Validasi keberadaan data
        $province = Province::find($addressData['province_code']);
        if (!$province) {
            $errors[] = 'Provinsi tidak ditemukan';
        }

        $regency = Regency::find($addressData['regency_code']);
        if (!$regency) {
            $errors[] = 'Kabupaten/kota tidak ditemukan';
        }

        $district = District::find($addressData['district_code']);
        if (!$district) {
            $errors[] = 'Kecamatan tidak ditemukan';
        }

        $village = Village::find($addressData['village_code']);
        if (!$village) {
            $errors[] = 'Kelurahan/desa tidak ditemukan';
        }

        // Validasi hierarki jika semua data ada
        if ($province && $regency && $district && $village) {
            if ($regency->province_code !== $province->code) {
                $errors[] = 'Kabupaten/kota tidak berada di provinsi yang dipilih';
            }

            if ($district->regency_code !== $regency->code) {
                $errors[] = 'Kecamatan tidak berada di kabupaten/kota yang dipilih';
            }

            if ($village->district_code !== $district->code) {
                $errors[] = 'Kelurahan/desa tidak berada di kecamatan yang dipilih';
            }
        }

        // Validasi kode pos jika ada
        if (isset($addressData['postal_code']) && !empty($addressData['postal_code'])) {
            if ($village && $village->postal_code !== $addressData['postal_code']) {
                $errors[] = 'Kode pos tidak sesuai dengan kelurahan/desa yang dipilih';
            }
        }

        return $errors;
    }

    public function validatePostalCode(string $villageCode, string $postalCode): bool
    {
        $village = Village::find($villageCode);

        if (!$village) {
            return false;
        }

        return $village->postal_code === $postalCode;
    }

    public function suggestCorrections(array $addressData): array
    {
        $suggestions = [];

        // Suggest berdasarkan nama yang mirip
        if (isset($addressData['province_name'])) {
            $similarProvinces = Province::search($addressData['province_name'])
                ->limit(3)
                ->get(['code', 'name']);

            if ($similarProvinces->isNotEmpty()) {
                $suggestions['provinces'] = $similarProvinces;
            }
        }

        if (isset($addressData['regency_name'])) {
            $similarRegencies = Regency::search($addressData['regency_name'])
                ->limit(3)
                ->get(['code', 'name']);

            if ($similarRegencies->isNotEmpty()) {
                $suggestions['regencies'] = $similarRegencies;
            }
        }

        return $suggestions;
    }
}
```

## Konfigurasi

### Registrasi Model Kustom

```php
// config/creasi/nusa.php

return [
    'models' => [
        'province' => \App\Models\Province::class,
        'regency' => \App\Models\Regency::class,
        'district' => \App\Models\District::class,
        'village' => \App\Models\Village::class,
    ],

    'services' => [
        'location' => \App\Services\LocationService::class,
        'validation' => \App\Services\AddressValidationService::class,
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 jam
        'prefix' => 'nusa_custom',
    ],
];
```

### Service Provider

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\{LocationService, AddressValidationService};

class NusaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LocationService::class);
        $this->app->singleton(AddressValidationService::class);
    }

    public function boot()
    {
        // Bind custom models jika diperlukan
        $this->app->bind(
            \Creasi\Nusa\Models\Province::class,
            \App\Models\Province::class
        );

        $this->app->bind(
            \Creasi\Nusa\Models\Regency::class,
            \App\Models\Regency::class
        );
    }
}
```

Dengan implementasi model kustom ini, Anda dapat memperluas fungsionalitas Laravel Nusa sesuai dengan kebutuhan spesifik aplikasi Anda, sambil tetap memanfaatkan data administratif Indonesia yang akurat dan lengkap.
