# WithAddresses

Trait `WithAddresses` memungkinkan model Anda memiliki banyak alamat dengan dukungan penuh untuk hierarki administratif Indonesia, validasi alamat, dan manajemen alamat default.

The `WithAddresses` trait allows your model to have multiple addresses with full support for Indonesian administrative hierarchy, address validation, and default address management.

## Overview

The `WithAddresses` trait is essential for applications that need to manage multiple addresses per entity, such as e-commerce platforms, delivery services, or any application where users, businesses, or other entities need multiple location references.

### What You Get

- **Multiple addresses** - One model can have many addresses
- **Default address management** - Automatic handling of default address
- **Address validation** - Built-in validation for address hierarchy
- **Polymorphic relationships** - Works with any model
- **Complete hierarchy access** - Access to village, district, regency, and province

## Basic Usage

### Adding the Trait

```php
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model
{
    use WithAddresses;
    
    protected $fillable = ['name', 'email'];
}
```

### No Database Changes Required

The trait uses polymorphic relationships through the existing `addresses` table, so no changes to your model's table are needed.

### Creating Addresses

```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Create home address
$homeAddress = $user->addresses()->create([
    'name' => 'John Doe',
    'phone' => '081234567890',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123, RT 01/RW 02',
    'notes' => 'Home address',
    'is_default' => true
]);

// Create office address
$officeAddress = $user->addresses()->create([
    'name' => 'John Doe',
    'phone' => '0247654321',
    'village_code' => '33.74.02.1005',
    'address_line' => 'Gedung ABC Lt. 5, Jl. Sudirman No. 456',
    'notes' => 'Office address'
]);
```

## Address Management

### Accessing Addresses

```php
// Get all addresses
$addresses = $user->addresses;

// Get addresses with location hierarchy
$addresses = $user->addresses()
    ->with(['village.district.regency.province'])
    ->get();

// Get default address
$defaultAddress = $user->addresses()->where('is_default', true)->first();

// Get addresses by type (using notes)
$homeAddresses = $user->addresses()->where('notes', 'like', '%home%')->get();
```

### Helper Methods

Add helper methods to your model:

```php
class User extends Model
{
    use WithAddresses;
    
    // Get default address
    public function getDefaultAddressAttribute()
    {
        return $this->addresses()->where('is_default', true)->first();
    }
    
    // Get home addresses
    public function getHomeAddressesAttribute()
    {
        return $this->addresses()->where('notes', 'like', '%home%')->get();
    }
    
    // Get office addresses
    public function getOfficeAddressesAttribute()
    {
        return $this->addresses()->where('notes', 'like', '%office%')->get();
    }
    
    // Check if user has address in specific province
    public function hasAddressInProvince($provinceCode)
    {
        return $this->addresses()
            ->whereHas('village', function ($query) use ($provinceCode) {
                $query->where('province_code', $provinceCode);
            })
            ->exists();
    }
    
    // Get addresses count by province
    public function getAddressesByProvince()
    {
        return $this->addresses()
            ->join('nusa.villages', 'addresses.village_code', '=', 'villages.code')
            ->join('nusa.provinces', 'villages.province_code', '=', 'provinces.code')
            ->groupBy('provinces.code', 'provinces.name')
            ->selectRaw('provinces.code, provinces.name, count(*) as address_count')
            ->get();
    }
}
```

## Default Address Management

### Automatic Default Handling

The Address model automatically manages default addresses:

```php
// When creating first address as default
$address1 = $user->addresses()->create([
    'name' => 'John Doe',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Address 1',
    'is_default' => true
]);

// When creating second address as default
$address2 = $user->addresses()->create([
    'name' => 'John Doe',
    'village_code' => '33.74.01.1002',
    'address_line' => 'Address 2',
    'is_default' => true
]);

// $address1 is automatically set to is_default = false
// $address2 becomes the new default address
```

### Manual Default Management

```php
// Set specific address as default
public function setDefaultAddress($addressId)
{
    // Remove default from all addresses
    $this->addresses()->update(['is_default' => false]);
    
    // Set new default
    $this->addresses()->where('id', $addressId)->update(['is_default' => true]);
}

// Usage
$user->setDefaultAddress($address->id);
```

## Address Validation

### Form Request Validation

```php
class StoreAddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'village_code' => [
                'required',
                'exists:nusa.villages,code'
            ],
            'address_line' => 'required|string|max:500',
            'notes' => 'nullable|string|max:255',
            'is_default' => 'boolean'
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'Recipient name is required.',
            'phone.required' => 'Phone number is required.',
            'village_code.required' => 'Please select a village.',
            'village_code.exists' => 'The selected village is invalid.',
            'address_line.required' => 'Complete address is required.'
        ];
    }
}
```

### Address Hierarchy Validation

```php
class AddressValidator
{
    public function validateHierarchy($data)
    {
        $village = Village::find($data['village_code']);
        
        if (!$village) {
            return [
                'valid' => false,
                'errors' => ['Village not found']
            ];
        }
        
        $errors = [];
        
        // Validate district if provided
        if (isset($data['district_code']) && $village->district_code !== $data['district_code']) {
            $errors[] = 'Village does not belong to selected district';
        }
        
        // Validate regency if provided
        if (isset($data['regency_code']) && $village->regency_code !== $data['regency_code']) {
            $errors[] = 'Village does not belong to selected regency';
        }
        
        // Validate province if provided
        if (isset($data['province_code']) && $village->province_code !== $data['province_code']) {
            $errors[] = 'Village does not belong to selected province';
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

## Querying Addresses

### Basic Queries

```php
// Users with addresses
$users = User::has('addresses')->get();

// Users with default addresses
$users = User::whereHas('addresses', function ($query) {
    $query->where('is_default', true);
})->get();

// Users with addresses in specific province
$users = User::whereHas('addresses.village', function ($query) {
    $query->where('province_code', '33');
})->get();
```

### Advanced Filtering

```php
// Users with addresses in Java
$users = User::whereHas('addresses.village', function ($query) {
    $query->whereIn('province_code', ['31', '32', '33', '34', '35', '36']);
})->get();

// Users with multiple addresses
$users = User::has('addresses', '>=', 2)->get();

// Users with addresses in specific postal code
$users = User::whereHas('addresses.village', function ($query) {
    $query->where('postal_code', '50132');
})->get();
```

### Custom Scopes

```php
class User extends Model
{
    use WithAddresses;
    
    // Users with addresses in province
    public function scopeWithAddressInProvince($query, $provinceCode)
    {
        return $query->whereHas('addresses.village', function ($q) use ($provinceCode) {
            $q->where('province_code', $provinceCode);
        });
    }
    
    // Users with multiple addresses
    public function scopeWithMultipleAddresses($query)
    {
        return $query->has('addresses', '>=', 2);
    }
    
    // Users with default address
    public function scopeWithDefaultAddress($query)
    {
        return $query->whereHas('addresses', function ($q) {
            $q->where('is_default', true);
        });
    }
}

// Usage
$javaUsers = User::withAddressInProvince('33')->get();
$multiAddressUsers = User::withMultipleAddresses()->get();
```

## Bulk Operations

### Import Addresses from CSV

```php
class AddressBulkImporter
{
    public function importForUser($userId, $csvFile)
    {
        $user = User::findOrFail($userId);
        $addresses = [];
        
        foreach ($this->parseCsv($csvFile) as $row) {
            $village = Village::where('name', $row['village'])
                ->whereHas('district', function ($q) use ($row) {
                    $q->where('name', $row['district']);
                })
                ->first();
            
            if ($village) {
                $addresses[] = [
                    'addressable_type' => User::class,
                    'addressable_id' => $userId,
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'village_code' => $village->code,
                    'address_line' => $row['address_line'],
                    'postal_code' => $village->postal_code,
                    'notes' => $row['notes'] ?? null,
                    'is_default' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        if (!empty($addresses)) {
            Address::insert($addresses);
            
            // Set first address as default if user has no default
            if (!$user->addresses()->where('is_default', true)->exists()) {
                $user->addresses()->first()->update(['is_default' => true]);
            }
        }
        
        return count($addresses);
    }
}
```

### Bulk Address Updates

```php
class AddressBulkUpdater
{
    public function updatePostalCodes($userId)
    {
        $user = User::findOrFail($userId);
        $updated = 0;
        
        $user->addresses()->chunk(100, function ($addresses) use (&$updated) {
            foreach ($addresses as $address) {
                if ($address->village && $address->village->postal_code !== $address->postal_code) {
                    $address->update(['postal_code' => $address->village->postal_code]);
                    $updated++;
                }
            }
        });
        
        return $updated;
    }
}
```

## API Integration

### Address Resource

```php
class AddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'recipient' => [
                'name' => $this->name,
                'phone' => $this->phone
            ],
            'address' => [
                'line' => $this->address_line,
                'village' => $this->village?->name,
                'district' => $this->village?->district?->name,
                'regency' => $this->village?->regency?->name,
                'province' => $this->village?->province?->name,
                'postal_code' => $this->postal_code,
                'full_address' => $this->full_address
            ],
            'coordinates' => [
                'latitude' => $this->village?->latitude,
                'longitude' => $this->village?->longitude
            ],
            'is_default' => $this->is_default,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
```

### User Resource with Addresses

```php
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'default_address' => new AddressResource($this->whenLoaded('defaultAddress')),
            'addresses_count' => $this->addresses_count ?? $this->addresses->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
```

## Performance Optimization

### Efficient Loading

```php
// Load users with addresses and hierarchy
$users = User::with([
    'addresses.village.district.regency.province'
])->get();

// Load only needed address fields
$users = User::with([
    'addresses' => function ($query) {
        $query->select('id', 'addressable_id', 'name', 'village_code', 'address_line', 'is_default');
    },
    'addresses.village' => function ($query) {
        $query->select('code', 'name', 'postal_code');
    }
])->get();

// Count addresses without loading
$users = User::withCount('addresses')->get();
```

### Caching

```php
class CachedAddressService
{
    public function getUserAddresses($userId)
    {
        return Cache::remember("user_addresses_{$userId}", 1800, function () use ($userId) {
            return User::find($userId)
                ->addresses()
                ->with(['village.district.regency.province'])
                ->get();
        });
    }
    
    public function getUserDefaultAddress($userId)
    {
        return Cache::remember("user_default_address_{$userId}", 1800, function () use ($userId) {
            return User::find($userId)
                ->addresses()
                ->where('is_default', true)
                ->with(['village.district.regency.province'])
                ->first();
        });
    }
}
```

## Testing

### Address Tests

```php
class UserAddressTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_have_multiple_addresses()
    {
        $user = User::factory()->create();
        $village1 = Village::factory()->create();
        $village2 = Village::factory()->create();
        
        $address1 = $user->addresses()->create([
            'name' => 'John Doe',
            'village_code' => $village1->code,
            'address_line' => 'Address 1'
        ]);
        
        $address2 = $user->addresses()->create([
            'name' => 'John Doe',
            'village_code' => $village2->code,
            'address_line' => 'Address 2'
        ]);
        
        $this->assertCount(2, $user->addresses);
        $this->assertTrue($user->addresses->contains($address1));
        $this->assertTrue($user->addresses->contains($address2));
    }
    
    public function test_default_address_management()
    {
        $user = User::factory()->create();
        $village = Village::factory()->create();
        
        // Create first address as default
        $address1 = $user->addresses()->create([
            'name' => 'John Doe',
            'village_code' => $village->code,
            'address_line' => 'Address 1',
            'is_default' => true
        ]);
        
        // Create second address as default
        $address2 = $user->addresses()->create([
            'name' => 'John Doe',
            'village_code' => $village->code,
            'address_line' => 'Address 2',
            'is_default' => true
        ]);
        
        // First address should no longer be default
        $this->assertFalse($address1->fresh()->is_default);
        $this->assertTrue($address2->fresh()->is_default);
    }
}
```

## Next Steps

- **[WithAddress](/id/api/concerns/with-address)** - Single address management
- **[Address Model](/id/api/models/address)** - Complete address model documentation
- **[Address Forms](/id/examples/address-forms)** - Building interactive address forms
- **[WithVillage](/id/api/concerns/with-village)** - Village relationship trait
