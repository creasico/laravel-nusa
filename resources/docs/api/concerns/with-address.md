# WithAddress Trait

The `WithAddress` trait adds a single polymorphic address relationship to your models, allowing them to have one associated address with complete Indonesian administrative region data.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithAddress
```

## Usage

### Basic Implementation

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithAddress;

class User extends Model
{
    use WithAddress;
    
    protected $fillable = [
        'name',
        'email'
    ];
}
```

### Database Setup

The trait uses Laravel Nusa's built-in address table. Ensure you've published and run the migrations:

```bash
php artisan vendor:publish --tag=creasi-migrations
php artisan migrate
```

## Features

### Single Address Relationship

Access the associated address:

```php
$user = User::find(1);
$address = $user->address;

if ($address) {
    echo "Address: {$address->address_line}";
    echo "Village: {$address->village->name}";
    echo "District: {$address->district->name}";
    echo "Regency: {$address->regency->name}";
    echo "Province: {$address->province->name}";
    echo "Postal Code: {$address->postal_code}";
}
```

### Creating Addresses

```php
$user = User::find(1);

$user->address()->create([
    'address_line' => 'Jl. Merdeka No. 123',
    'province_code' => '33',
    'regency_code' => '33.74',
    'district_code' => '33.74.01',
    'village_code' => '33.74.01.1001',
    'postal_code' => '50711'
]);
```

### Updating Addresses

```php
$user = User::find(1);

if ($user->address) {
    $user->address->update([
        'address_line' => 'Jl. Sudirman No. 456',
        'village_code' => '33.74.02.1005'
    ]);
} else {
    $user->address()->create([
        'address_line' => 'Jl. Sudirman No. 456',
        'province_code' => '33',
        'regency_code' => '33.74',
        'district_code' => '33.74.02',
        'village_code' => '33.74.02.1005'
    ]);
}
```

## Common Use Cases

### 1. User Profiles

```php
class User extends Model
{
    use WithAddress;
    
    protected $fillable = ['name', 'email'];
    
    public function getFullAddressAttribute()
    {
        if (!$this->address) return null;
        
        return collect([
            $this->address->address_line,
            $this->address->village->name,
            $this->address->district->name,
            $this->address->regency->name,
            $this->address->province->name,
            $this->address->postal_code
        ])->filter()->implode(', ');
    }
    
    public function hasCompleteAddress()
    {
        return $this->address && 
               $this->address->address_line && 
               $this->address->village_code;
    }
}

// Usage
$user = User::with('address.village.district.regency.province')->first();
echo $user->full_address;
```

### 2. Customer Management

```php
class Customer extends Model
{
    use WithAddress;
    
    protected $fillable = [
        'name',
        'email',
        'phone'
    ];
    
    public function scopeInProvince($query, $provinceCode)
    {
        return $query->whereHas('address.province', function ($q) use ($provinceCode) {
            $q->where('code', $provinceCode);
        });
    }
    
    public function scopeInRegency($query, $regencyCode)
    {
        return $query->whereHas('address.regency', function ($q) use ($regencyCode) {
            $q->where('code', $regencyCode);
        });
    }
    
    public function getShippingZoneAttribute()
    {
        if (!$this->address) return null;
        
        $provinceCode = $this->address->province_code;
        
        return match($provinceCode) {
            '31', '32', '33', '34', '35', '36' => 'Java',
            '11', '12', '13', '14', '15', '16', '17', '18', '19', '21' => 'Sumatra',
            '51', '52', '53' => 'Bali & Nusa Tenggara',
            default => 'Outer Islands'
        };
    }
}

// Usage
$jakartaCustomers = Customer::inProvince('31')->get();
$shippingZone = $customer->shipping_zone;
```

### 3. Business Locations

```php
class Store extends Model
{
    use WithAddress;
    
    protected $fillable = [
        'name',
        'description',
        'phone'
    ];
    
    public function scopeNearby($query, $provinceCode, $regencyCode = null)
    {
        return $query->whereHas('address', function ($q) use ($provinceCode, $regencyCode) {
            $q->where('province_code', $provinceCode);
            
            if ($regencyCode) {
                $q->where('regency_code', $regencyCode);
            }
        });
    }
    
    public function getOperatingHoursAttribute()
    {
        // Different operating hours based on location
        if (!$this->address) return '09:00 - 21:00';
        
        $provinceCode = $this->address->province_code;
        
        // Jakarta stores open later
        if ($provinceCode === '31') {
            return '10:00 - 22:00';
        }
        
        return '09:00 - 21:00';
    }
}
```

### 4. Event Management

```php
class Event extends Model
{
    use WithAddress;
    
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'max_participants'
    ];
    
    protected $casts = [
        'event_date' => 'datetime'
    ];
    
    public function getLocationNameAttribute()
    {
        if (!$this->address) return 'Location TBA';
        
        return collect([
            $this->address->regency->name,
            $this->address->province->name
        ])->implode(', ');
    }
    
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now());
    }
    
    public function scopeInLocation($query, $provinceCode, $regencyCode = null)
    {
        return $query->whereHas('address', function ($q) use ($provinceCode, $regencyCode) {
            $q->where('province_code', $provinceCode);
            
            if ($regencyCode) {
                $q->where('regency_code', $regencyCode);
            }
        });
    }
}

// Usage
$upcomingEvents = Event::upcoming()
    ->inLocation('33', '33.74') // Semarang
    ->with('address.regency.province')
    ->get();
```

## Advanced Usage

### Address Validation

```php
class User extends Model
{
    use WithAddress;
    
    public function updateAddress(array $addressData)
    {
        // Validate administrative hierarchy
        $village = Village::with(['district.regency.province'])
            ->where('code', $addressData['village_code'])
            ->first();
            
        if (!$village) {
            throw new \InvalidArgumentException('Invalid village code');
        }
        
        // Auto-fill parent codes
        $addressData['district_code'] = $village->district_code;
        $addressData['regency_code'] = $village->regency_code;
        $addressData['province_code'] = $village->province_code;
        
        if ($this->address) {
            $this->address->update($addressData);
        } else {
            $this->address()->create($addressData);
        }
        
        return $this->fresh('address');
    }
}
```

### Bulk Address Operations

```php
class AddressService
{
    public static function updateUserAddresses(array $userData)
    {
        foreach ($userData as $data) {
            $user = User::find($data['user_id']);
            
            if ($user) {
                $user->address()->updateOrCreate(
                    ['addressable_id' => $user->id, 'addressable_type' => User::class],
                    $data['address']
                );
            }
        }
    }
    
    public static function getUsersByRegion($provinceCode, $regencyCode = null)
    {
        return User::whereHas('address', function ($query) use ($provinceCode, $regencyCode) {
            $query->where('province_code', $provinceCode);
            
            if ($regencyCode) {
                $query->where('regency_code', $regencyCode);
            }
        })->with('address.village.district.regency.province')->get();
    }
}
```

## Form Integration

### Address Form Component

```php
class AddressController extends Controller
{
    public function update(Request $request, User $user)
    {
        $request->validate([
            'address_line' => 'required|string|max:255',
            'village_code' => 'required|exists:nusa.villages,code',
            'postal_code' => 'nullable|string|size:5'
        ]);
        
        $village = Village::with(['district.regency.province'])
            ->where('code', $request->village_code)
            ->first();
        
        $addressData = [
            'address_line' => $request->address_line,
            'village_code' => $village->code,
            'district_code' => $village->district_code,
            'regency_code' => $village->regency_code,
            'province_code' => $village->province_code,
            'postal_code' => $request->postal_code ?? $village->postal_code
        ];
        
        if ($user->address) {
            $user->address->update($addressData);
        } else {
            $user->address()->create($addressData);
        }
        
        return redirect()->back()->with('success', 'Address updated successfully');
    }
}
```

## Performance Tips

### 1. Eager Loading

```php
// Good
$users = User::with('address.village.district.regency.province')->get();

// Bad - N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->address->village->name; // Multiple queries
}
```

### 2. Selective Loading

```php
$users = User::with([
    'address:id,addressable_id,addressable_type,address_line,village_code',
    'address.village:code,name,district_code',
    'address.village.district:code,name,regency_code',
    'address.village.district.regency:code,name,province_code',
    'address.village.district.regency.province:code,name'
])->get();
```

## Related Documentation

- **[WithAddresses Trait](/api/concerns/with-addresses)** - For multiple address support
- **[Address Model](/api/models/address)** - Complete Address model documentation
- **[Address Management Guide](/guide/addresses)** - Complete address functionality guide
- **[Address Forms Example](/examples/address-forms)** - Building address forms
