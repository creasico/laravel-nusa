# Address Model

The `Address` model provides a complete address management system that integrates with Indonesian administrative data. It's designed to store user addresses with proper validation and relationships to administrative regions.

## Class Reference

```php
namespace Creasi\Nusa\Models;

class Address extends Model
{
    // Model implementation
}
```

## Attributes

### Primary Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `id` | `bigint` | Primary key (auto-increment) | `1` |
| `user_id` | `bigint` | Foreign key to users table | `123` |
| `name` | `string` | Recipient name | `"John Doe"` |
| `phone` | `string` | Contact phone number | `"081234567890"` |
| `province_code` | `string` | Province code (Foreign Key) | `"33"` |
| `regency_code` | `string` | Regency code (Foreign Key) | `"33.75"` |
| `district_code` | `string` | District code (Foreign Key) | `"33.75.01"` |
| `village_code` | `string` | Village code (Foreign Key) | `"33.75.01.1002"` |
| `address_line` | `text` | Detailed address | `"Jl. Merdeka No. 123"` |
| `postal_code` | `string` | 5-digit postal code | `"51111"` |
| `is_default` | `boolean` | Default address flag | `true` |

### Computed Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `full_address` | `string` | Complete formatted address |

## Relationships

### Belongs To

```php
// Get user who owns this address
$address->user; // User model

// Get administrative regions
$address->province; // Province model
$address->regency;  // Regency model
$address->district; // District model
$address->village;  // Village model
```

### Relationship Methods

```php
// User relationship
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// Administrative region relationships
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

## Scopes

### Default Address Scope

```php
// Get default addresses
Address::default()->get();

// Get non-default addresses
Address::notDefault()->get();
```

### User Scope

```php
// Get addresses for specific user
Address::forUser($userId)->get();
```

## Usage Examples

### Creating Addresses

```php
use Creasi\Nusa\Models\Address;

// Create new address
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

// Create via user relationship
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

### Querying Addresses

```php
// Get all addresses for a user
$userAddresses = Address::where('user_id', 1)->get();

// Get user's default address
$defaultAddress = Address::where('user_id', 1)
    ->where('is_default', true)
    ->first();

// Get addresses with administrative data
$addresses = Address::with(['province', 'regency', 'district', 'village'])
    ->where('user_id', 1)
    ->get();

// Search addresses by name or phone
$addresses = Address::where('user_id', 1)
    ->where(function ($query) use ($search) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
    })
    ->get();
```

### Working with Full Address

```php
$address = Address::with(['village', 'district', 'regency', 'province'])
    ->find(1);

// Get formatted full address
$fullAddress = $address->full_address;
// "Jl. Merdeka No. 123, Medono, Pekalongan Barat, Kota Pekalongan, Jawa Tengah 51111"

// Manual address building
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

## Address Management

### Default Address Handling

```php
// Set address as default (unset others)
function setDefaultAddress($userId, $addressId) {
    // Unset current default
    Address::where('user_id', $userId)
        ->where('is_default', true)
        ->update(['is_default' => false]);
    
    // Set new default
    Address::where('id', $addressId)
        ->where('user_id', $userId)
        ->update(['is_default' => true]);
}

// Get user's default address
function getDefaultAddress($userId) {
    return Address::where('user_id', $userId)
        ->where('is_default', true)
        ->with(['province', 'regency', 'district', 'village'])
        ->first();
}
```

### Address Validation

```php
// Validate address consistency
function validateAddressConsistency($addressData) {
    $village = Village::find($addressData['village_code']);
    
    if (!$village) {
        return false;
    }
    
    return $village->district_code === $addressData['district_code'] &&
           $village->regency_code === $addressData['regency_code'] &&
           $village->province_code === $addressData['province_code'];
}

// Auto-fill postal code from village
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

## Traits Integration

### HasAddresses Trait

For models that can have multiple addresses:

```php
use Creasi\Nusa\Contracts\HasAddresses;
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model implements HasAddresses
{
    use WithAddresses;
}

// Usage
$user = User::find(1);
$addresses = $user->addresses;
$defaultAddress = $user->defaultAddress;
$user->setDefaultAddress($address);
```

### HasAddress Trait

For models with a single address:

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

// Usage
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

## Form Validation

### Request Validation

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
                        $fail('The selected regency is invalid for this province.');
                    }
                },
            ],
            'district_code' => [
                'required',
                'exists:nusa.districts,code',
                function ($attribute, $value, $fail) {
                    $district = District::find($value);
                    if (!$district || $district->regency_code !== $this->regency_code) {
                        $fail('The selected district is invalid for this regency.');
                    }
                },
            ],
            'village_code' => [
                'required',
                'exists:nusa.villages,code',
                function ($attribute, $value, $fail) {
                    $village = Village::find($value);
                    if (!$village || $village->district_code !== $this->district_code) {
                        $fail('The selected village is invalid for this district.');
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

### Custom Validation Rules

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
        return 'The address components are not consistent.';
    }
}
```

## Database Schema

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

## Performance Tips

### Efficient Queries

```php
// Good: Load with specific relationships
$addresses = Address::with(['province:code,name', 'regency:code,name'])
    ->where('user_id', $userId)
    ->get();

// Good: Select specific columns
$addresses = Address::select('id', 'name', 'address_line', 'is_default')
    ->where('user_id', $userId)
    ->get();

// Avoid: Loading all relationships
$addresses = Address::with(['province', 'regency', 'district', 'village'])
    ->get(); // Loads too much data
```

### Caching

```php
// Cache user's addresses
function getUserAddresses($userId) {
    $cacheKey = "user.{$userId}.addresses";
    
    return Cache::remember($cacheKey, 1800, function () use ($userId) {
        return Address::with(['province', 'regency', 'district', 'village'])
            ->where('user_id', $userId)
            ->get();
    });
}
```

## Related Models

- **[Province Model](/en/api/models/province)** - Administrative region reference
- **[Regency Model](/en/api/models/regency)** - Administrative region reference
- **[District Model](/en/api/models/district)** - Administrative region reference
- **[Village Model](/en/api/models/village)** - Administrative region reference
