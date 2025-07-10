# WithAddress

Trait `WithAddress` memungkinkan model Anda memiliki satu alamat dengan dukungan penuh untuk hierarki administratif Indonesia, validasi alamat, dan akses ke data geografis.

## Gambaran Umum

Trait `WithAddress` sempurna untuk model yang membutuhkan tepat satu alamat, seperti toko, kantor, atau entitas apa pun yang memiliki satu lokasi utama. Ini menyediakan interface yang bersih dan sederhana untuk manajemen alamat tanpa kompleksitas multiple alamat.

### Apa yang Anda Dapatkan

- **Relasi alamat tunggal** - Satu model memiliki satu alamat
- **Manajemen alamat otomatis** - Pembuatan dan update alamat yang disederhanakan
- **Akses hierarki lengkap** - Akses ke kelurahan/desa, kecamatan, kabupaten/kota, dan provinsi
- **Koordinat geografis** - Akses ke koordinat lokasi melalui kelurahan/desa
- **Validasi alamat** - Validasi bawaan untuk hierarki alamat

## Penggunaan Dasar

### Menambahkan Trait

```php
use Creasi\Nusa\Models\Concerns\WithAddress;

class Store extends Model
{
    use WithAddress;
    
    protected $fillable = [
        'name',
        'description',
        'phone'
    ];
}
```

### Tidak Perlu Perubahan Database

Trait ini menggunakan relasi polimorfik melalui tabel `addresses` yang sudah ada, jadi tidak diperlukan perubahan pada tabel model Anda.

### Membuat Store dengan Alamat

```php
$store = Store::create([
    'name' => 'My Store',
    'description' => 'Electronics store',
    'phone' => '0247654321'
]);

// Create address for the store
$store->address()->create([
    'name' => 'My Store',
    'phone' => '0247654321',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123',
    'postal_code' => '50132'
]);

// Access address and location data
echo $store->address->full_address;
echo $store->address->village->province->name;
```

## Address Management

### Accessing Address

```php
// Get store with address and location hierarchy
$store = Store::with(['address.village.district.regency.province'])->first();

// Access address data
if ($store->address) {
    echo $store->address->address_line;
    echo $store->address->village->name;
    echo $store->address->village->district->name;
    echo $store->address->village->regency->name;
    echo $store->address->village->province->name;
    echo $store->address->postal_code;
}
```

### Helper Methods

Add helper methods to your model:

```php
class Store extends Model
{
    use WithAddress;
    
    // Get formatted store location
    public function getStoreLocationAttribute()
    {
        if ($this->address && $this->address->village) {
            return [
                'store_name' => $this->name,
                'address_line' => $this->address->address_line,
                'village' => $this->address->village->name,
                'district' => $this->address->village->district->name,
                'regency' => $this->address->village->regency->name,
                'province' => $this->address->village->province->name,
                'postal_code' => $this->address->postal_code,
                'coordinates' => [
                    'latitude' => $this->address->village->latitude,
                    'longitude' => $this->address->village->longitude
                ]
            ];
        }
        return null;
    }
    
    // Get complete address string
    public function getFullAddressAttribute()
    {
        return $this->address?->full_address;
    }
    
    // Check if store has address
    public function hasAddress()
    {
        return $this->address !== null;
    }
    
    // Check if store is in specific province
    public function isInProvince($provinceCode)
    {
        return $this->address?->village?->province_code === $provinceCode;
    }
    
    // Get store coordinates
    public function getCoordinatesAttribute()
    {
        if ($this->address && $this->address->village) {
            return [
                'latitude' => $this->address->village->latitude,
                'longitude' => $this->address->village->longitude
            ];
        }
        return null;
    }
}
```

## Address Creation and Updates

### Creating Address

```php
// Create store and address together
$store = Store::create([
    'name' => 'Electronics Store',
    'phone' => '0247654321'
]);

$address = $store->address()->create([
    'name' => 'Electronics Store',
    'phone' => '0247654321',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Sudirman No. 456, Lantai 2',
    'notes' => 'Near the main intersection'
]);
```

### Updating Address

```php
// Update existing address
if ($store->address) {
    $store->address->update([
        'address_line' => 'Jl. Sudirman No. 456, Lantai 3',
        'phone' => '0247654322'
    ]);
}

// Replace address (delete old, create new)
$store->address()?->delete();
$store->address()->create([
    'name' => 'Electronics Store',
    'phone' => '0247654322',
    'village_code' => '33.74.02.1005',
    'address_line' => 'Jl. Pemuda No. 789'
]);
```

### Address Validation

```php
class StoreAddressValidator
{
    public function validateStoreAddress($storeData, $addressData)
    {
        $errors = [];
        
        // Validate village exists
        $village = Village::find($addressData['village_code']);
        if (!$village) {
            $errors[] = 'Selected village is invalid';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validate address completeness
        if (empty($addressData['address_line'])) {
            $errors[] = 'Complete address is required';
        }
        
        // Validate phone number consistency
        if ($storeData['phone'] !== $addressData['phone']) {
            $errors[] = 'Store phone and address phone should match';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'village' => $village,
            'suggested_postal_code' => $village->postal_code
        ];
    }
}
```

## Querying Stores with Addresses

### Basic Queries

```php
// Get stores with addresses
$stores = Store::has('address')->get();

// Get stores with address and location data
$stores = Store::with(['address.village.district.regency.province'])->get();

// Get stores without addresses
$storesWithoutAddress = Store::doesntHave('address')->get();
```

### Location-based Queries

```php
// Stores in specific province
$stores = Store::whereHas('address.village', function ($query) {
    $query->where('province_code', '33');
})->get();

// Stores in specific regency
$stores = Store::whereHas('address.village', function ($query) {
    $query->where('regency_code', '33.74');
})->get();

// Stores in specific postal code
$stores = Store::whereHas('address', function ($query) {
    $query->where('postal_code', '50132');
})->get();
```

### Custom Scopes

```php
class Store extends Model
{
    use WithAddress;
    
    // Scope for stores with addresses
    public function scopeWithAddress($query)
    {
        return $query->has('address');
    }
    
    // Scope for stores in province
    public function scopeInProvince($query, $provinceCode)
    {
        return $query->whereHas('address.village', function ($q) use ($provinceCode) {
            $q->where('province_code', $provinceCode);
        });
    }
    
    // Scope for stores in regency
    public function scopeInRegency($query, $regencyCode)
    {
        return $query->whereHas('address.village', function ($q) use ($regencyCode) {
            $q->where('regency_code', $regencyCode);
        });
    }
    
    // Scope for stores in Java
    public function scopeInJava($query)
    {
        return $query->whereHas('address.village', function ($q) {
            $q->whereIn('province_code', ['31', '32', '33', '34', '35', '36']);
        });
    }
}

// Usage
$storesWithAddress = Store::withAddress()->get();
$centralJavaStores = Store::inProvince('33')->get();
$semarangStores = Store::inRegency('33.74')->get();
$javaStores = Store::inJava()->get();
```

## Geographic Operations

### Distance Calculations

```php
class StoreLocator
{
    public function findNearestStores($latitude, $longitude, $radiusKm = 10)
    {
        return Store::whereHas('address.village', function ($query) use ($latitude, $longitude, $radiusKm) {
            $query->selectRaw("
                *,
                (6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )) AS distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
        })
        ->with(['address.village'])
        ->get()
        ->map(function ($store) {
            return [
                'store' => $store->name,
                'address' => $store->full_address,
                'distance_km' => $store->address->village->distance ?? null,
                'coordinates' => $store->coordinates
            ];
        });
    }
    
    public function calculateDistanceBetweenStores(Store $store1, Store $store2)
    {
        if (!$store1->coordinates || !$store2->coordinates) {
            return null;
        }
        
        $earthRadius = 6371; // km
        
        $lat1 = deg2rad($store1->coordinates['latitude']);
        $lng1 = deg2rad($store1->coordinates['longitude']);
        $lat2 = deg2rad($store2->coordinates['latitude']);
        $lng2 = deg2rad($store2->coordinates['longitude']);
        
        $deltaLat = $lat2 - $lat1;
        $deltaLng = $lng2 - $lng1;
        
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}
```

### Store Coverage Analysis

```php
class StoreCoverageAnalyzer
{
    public function analyzeRegionalCoverage()
    {
        return Province::withCount(['villages'])
            ->get()
            ->map(function ($province) {
                $storesInProvince = Store::inProvince($province->code)->count();
                
                return [
                    'province' => $province->name,
                    'total_villages' => $province->villages_count,
                    'stores_count' => $storesInProvince,
                    'coverage_ratio' => $storesInProvince / $province->villages_count,
                    'coverage_status' => $this->determineCoverageStatus($storesInProvince, $province->villages_count)
                ];
            });
    }
    
    private function determineCoverageStatus($storesCount, $villagesCount)
    {
        $ratio = $storesCount / $villagesCount;
        
        if ($ratio >= 0.1) return 'Well Covered';
        if ($ratio >= 0.05) return 'Adequately Covered';
        if ($ratio > 0) return 'Under Covered';
        return 'Not Covered';
    }
}
```

## API Resources

### Store Resource with Address

```php
class StoreResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'phone' => $this->phone,
            'address' => [
                'line' => $this->address?->address_line,
                'village' => $this->address?->village?->name,
                'district' => $this->address?->village?->district?->name,
                'regency' => $this->address?->village?->regency?->name,
                'province' => $this->address?->village?->province?->name,
                'postal_code' => $this->address?->postal_code,
                'full_address' => $this->full_address
            ],
            'location' => [
                'coordinates' => $this->coordinates,
                'has_address' => $this->hasAddress()
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
```

## Testing

### Store Address Tests

```php
class StoreAddressTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_store_can_have_address()
    {
        $store = Store::factory()->create();
        $village = Village::factory()->create();
        
        $address = $store->address()->create([
            'name' => 'Test Store',
            'village_code' => $village->code,
            'address_line' => 'Test Address'
        ]);
        
        $this->assertInstanceOf(Address::class, $store->address);
        $this->assertEquals($address->id, $store->address->id);
    }
    
    public function test_store_location_helper_methods()
    {
        $store = Store::factory()->create(['name' => 'Test Store']);
        $village = Village::factory()->create(['postal_code' => '50132']);
        
        $store->address()->create([
            'name' => 'Test Store',
            'village_code' => $village->code,
            'address_line' => 'Jl. Test No. 123'
        ]);
        
        $store->load('address.village');
        
        $this->assertTrue($store->hasAddress());
        $this->assertNotNull($store->coordinates);
        $this->assertStringContains('Jl. Test No. 123', $store->full_address);
    }
}
```

## Langkah Selanjutnya

- **[WithAddresses](/id/api/concerns/with-addresses)** - Manajemen multiple alamat
- **[Model Address](/id/api/models/address)** - Dokumentasi lengkap model alamat
- **[WithVillage](/id/api/concerns/with-village)** - Trait relasi kelurahan/desa
- **[WithCoordinate](/id/api/concerns/with-coordinate)** - Trait koordinat geografis
