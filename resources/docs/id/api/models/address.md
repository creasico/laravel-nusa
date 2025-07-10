# Model Alamat

Dokumentasi lengkap untuk model Address Laravel Nusa, termasuk atribut, relasi, scope, dan metode yang tersedia untuk mengelola alamat dengan integrasi hierarki administratif Indonesia.

This comprehensive documentation covers the Address model in Laravel Nusa, including attributes, relationships, scopes, and available methods for managing addresses with Indonesian administrative hierarchy integration.

## Model Overview

The Address model provides a complete address management system that integrates seamlessly with Indonesia's administrative hierarchy. It supports polymorphic relationships, allowing any model to have multiple addresses with full validation and hierarchy support.

### Basic Usage

```php
use Creasi\Nusa\Models\Address;

// Create address for a user
$address = Address::create([
    'addressable_type' => User::class,
    'addressable_id' => 1,
    'name' => 'John Doe',
    'phone' => '081234567890',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123',
    'is_default' => true
]);

// Access hierarchy
echo $address->village->province->name; // "Jawa Tengah"
```

## Model Attributes

### Database Schema

| Attribute | Type | Description |
|-----------|------|-------------|
| `id` | bigint | Primary key, auto-increment |
| `addressable_type` | string | Polymorphic type (model class) |
| `addressable_id` | bigint | Polymorphic ID (model ID) |
| `name` | string | Recipient name |
| `phone` | string | Phone number |
| `village_code` | string(13) | Foreign key to villages table |
| `address_line` | text | Complete address details |
| `postal_code` | string(5) | Five-digit postal code |
| `notes` | text | Additional notes (nullable) |
| `is_default` | boolean | Default address flag |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

### Fillable Attributes

```php
protected $fillable = [
    'addressable_type',
    'addressable_id',
    'name',
    'phone',
    'village_code',
    'address_line',
    'postal_code',
    'notes',
    'is_default'
];
```

### Casts

```php
protected $casts = [
    'is_default' => 'boolean'
];
```

## Relationships

### Polymorphic Relationship

```php
// Parent model (User, Order, etc.)
public function addressable(): MorphTo

// Usage
$address = Address::find(1);
$user = $address->addressable; // Get the owner model
```

### Administrative Hierarchy

```php
// Village relationship
public function village(): BelongsTo

// Access complete hierarchy through village
$address = Address::with(['village.district.regency.province'])->first();
echo $address->village->district->name;  // District name
echo $address->village->regency->name;   // Regency name
echo $address->village->province->name;  // Province name
```

## Accessors

### Full Address Accessor

```php
public function getFullAddressAttribute()
{
    $parts = [$this->address_line];
    
    if ($this->village) {
        $parts[] = $this->village->name;
        $parts[] = $this->village->district->name;
        $parts[] = $this->village->regency->name;
        $parts[] = $this->village->province->name;
        
        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }
    }
    
    return implode(', ', array_filter($parts));
}

// Usage
echo $address->full_address;
// "Jl. Merdeka No. 123, Medono, Semarang Tengah, Kota Semarang, Jawa Tengah, 50132"
```

### Formatted Address Accessor

```php
public function getFormattedAddressAttribute()
{
    return [
        'recipient' => $this->name,
        'phone' => $this->phone,
        'address_line' => $this->address_line,
        'village' => $this->village?->name,
        'district' => $this->village?->district?->name,
        'regency' => $this->village?->regency?->name,
        'province' => $this->village?->province?->name,
        'postal_code' => $this->postal_code,
        'full_address' => $this->full_address
    ];
}
```

## Scopes

### Default Address Scope

```php
public function scopeDefault($query)
{
    return $query->where('is_default', true);
}

// Usage
$defaultAddress = $user->addresses()->default()->first();
```

### By Location Scopes

```php
public function scopeInProvince($query, $provinceCode)
{
    return $query->whereHas('village', function ($q) use ($provinceCode) {
        $q->where('province_code', $provinceCode);
    });
}

public function scopeInRegency($query, $regencyCode)
{
    return $query->whereHas('village', function ($q) use ($regencyCode) {
        $q->where('regency_code', $regencyCode);
    });
}

// Usage
$addresses = Address::inProvince('33')->get();
$addresses = Address::inRegency('33.74')->get();
```

## Model Events

### Auto-fill Postal Code

```php
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($address) {
        if (!$address->postal_code && $address->village_code) {
            $village = Village::find($address->village_code);
            if ($village && $village->postal_code) {
                $address->postal_code = $village->postal_code;
            }
        }
    });
    
    static::updating(function ($address) {
        if ($address->isDirty('village_code') && !$address->isDirty('postal_code')) {
            $village = Village::find($address->village_code);
            if ($village && $village->postal_code) {
                $address->postal_code = $village->postal_code;
            }
        }
    });
}
```

### Default Address Management

```php
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($address) {
        if ($address->is_default) {
            // Unset other default addresses for the same addressable
            static::where('addressable_type', $address->addressable_type)
                ->where('addressable_id', $address->addressable_id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    });
}
```

## Usage Examples

### Creating Addresses

```php
// Create address with automatic postal code
$address = Address::create([
    'addressable_type' => User::class,
    'addressable_id' => 1,
    'name' => 'John Doe',
    'phone' => '081234567890',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123, RT 01/RW 02',
    'is_default' => true
    // postal_code will be auto-filled from village
]);

// Create address with custom postal code
$address = Address::create([
    'addressable_type' => Business::class,
    'addressable_id' => 5,
    'name' => 'PT Example',
    'phone' => '0247654321',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Gedung ABC Lt. 5',
    'postal_code' => '50132', // Custom postal code
    'notes' => 'Entrance through main lobby'
]);
```

### Address Validation

```php
class AddressValidator
{
    public function validate($data)
    {
        $village = Village::find($data['village_code']);
        
        if (!$village) {
            return [
                'valid' => false,
                'errors' => ['Village not found']
            ];
        }
        
        $errors = [];
        
        // Validate postal code if provided
        if (isset($data['postal_code']) && $data['postal_code'] !== $village->postal_code) {
            $errors[] = "Postal code mismatch. Expected: {$village->postal_code}";
        }
        
        // Validate hierarchy if other codes provided
        if (isset($data['district_code']) && $village->district_code !== $data['district_code']) {
            $errors[] = 'Village does not belong to selected district';
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

### Bulk Address Operations

```php
class AddressBulkOperations
{
    public function importFromCsv($csvFile, $addressableType, $addressableId)
    {
        $addresses = [];
        
        foreach ($this->parseCsv($csvFile) as $row) {
            $village = Village::where('name', $row['village'])
                ->whereHas('district', function ($q) use ($row) {
                    $q->where('name', $row['district']);
                })
                ->first();
            
            if ($village) {
                $addresses[] = [
                    'addressable_type' => $addressableType,
                    'addressable_id' => $addressableId,
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'village_code' => $village->code,
                    'address_line' => $row['address_line'],
                    'postal_code' => $village->postal_code,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        Address::insert($addresses);
        
        return count($addresses);
    }
}
```

## Integration with Models

### Using WithAddresses Trait

```php
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model
{
    use WithAddresses;
    
    // Get default address
    public function getDefaultAddressAttribute()
    {
        return $this->addresses()->default()->first();
    }
    
    // Get addresses by type
    public function getHomeAddressesAttribute()
    {
        return $this->addresses()->where('notes', 'like', '%home%')->get();
    }
}

// Usage
$user = User::find(1);
$addresses = $user->addresses; // All addresses
$defaultAddress = $user->default_address; // Default address
```

### Using WithAddress Trait (Single Address)

```php
use Creasi\Nusa\Models\Concerns\WithAddress;

class Store extends Model
{
    use WithAddress;
    
    // Get formatted store address
    public function getStoreLocationAttribute()
    {
        if ($this->address) {
            return $this->address->formatted_address;
        }
        return null;
    }
}

// Usage
$store = Store::find(1);
$location = $store->store_location;
```

## API Resources

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

## Testing

### Address Model Tests

```php
class AddressTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_address_with_auto_postal_code()
    {
        $village = Village::factory()->create(['postal_code' => '50132']);
        $user = User::factory()->create();
        
        $address = Address::create([
            'addressable_type' => User::class,
            'addressable_id' => $user->id,
            'name' => 'John Doe',
            'village_code' => $village->code,
            'address_line' => 'Jl. Test No. 123'
        ]);
        
        $this->assertEquals('50132', $address->postal_code);
    }
    
    public function test_default_address_management()
    {
        $user = User::factory()->create();
        
        // Create first address as default
        $address1 = Address::factory()->create([
            'addressable_type' => User::class,
            'addressable_id' => $user->id,
            'is_default' => true
        ]);
        
        // Create second address as default
        $address2 = Address::factory()->create([
            'addressable_type' => User::class,
            'addressable_id' => $user->id,
            'is_default' => true
        ]);
        
        // First address should no longer be default
        $this->assertFalse($address1->fresh()->is_default);
        $this->assertTrue($address2->fresh()->is_default);
    }
}
```

## Next Steps

- **[Village Model](/id/api/models/village)** - Model kelurahan/desa documentation
- **[Address Management](/id/guide/addresses)** - Complete address management guide
- **[Address Forms](/id/examples/address-forms)** - Building interactive address forms
- **[Model Concerns](/id/api/concerns/)** - Available traits for address integration
