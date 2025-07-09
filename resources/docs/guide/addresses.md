# Address Management

Laravel Nusa provides a comprehensive address management system that allows you to easily integrate Indonesian administrative data into your application's address functionality. This includes traits, models, and migrations for handling user addresses.

## Overview

The address management system provides:

- **Address Model** - Store complete address information
- **Traits** - Easy integration with existing models
- **Validation** - Ensure address consistency
- **Relationships** - Connect to administrative data

## Setup

### Publish Migrations

First, publish the address table migration:

```bash
php artisan vendor:publish --tag=creasi-migrations
```

This creates a migration file in your `database/migrations/` directory.

### Run Migrations

```bash
php artisan migrate
```

This creates the `addresses` table in your main database.

## Address Model

The default address model provides a complete structure for storing addresses:

```php
use Creasi\Nusa\Models\Address;

$address = Address::create([
    'user_id' => 1,
    'name' => 'John Doe',
    'phone' => '081234567890',
    'province_code' => '33',
    'regency_code' => '3375',
    'district_code' => '337501',
    'village_code' => '3375011002',
    'address_line' => 'Jl. Merdeka No. 123',
    'postal_code' => '51111',
    'is_default' => true,
]);
```

### Address Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `user_id` | `bigint` | Foreign key to users table |
| `name` | `string` | Recipient name |
| `phone` | `string` | Contact phone number |
| `province_code` | `string` | Province code |
| `regency_code` | `string` | Regency code |
| `district_code` | `string` | District code |
| `village_code` | `string` | Village code |
| `address_line` | `text` | Detailed address |
| `postal_code` | `string` | Postal code |
| `is_default` | `boolean` | Default address flag |

### Address Relationships

The address model automatically connects to administrative data:

```php
$address = Address::find(1);

// Get administrative hierarchy
$province = $address->province;  // Province model
$regency = $address->regency;    // Regency model
$district = $address->district;  // District model
$village = $address->village;    // Village model

// Get full address string
$fullAddress = $address->full_address;
// "Jl. Merdeka No. 123, Medono, Pekalongan Barat, Kota Pekalongan, Jawa Tengah 51111"
```

## Using Traits

### HasAddresses Trait

For models that can have multiple addresses (like users):

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Creasi\Nusa\Contracts\HasAddresses;
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Authenticatable implements HasAddresses
{
    use WithAddresses;
    
    // Your existing user model code...
}
```

Now your users can have multiple addresses:

```php
$user = User::find(1);

// Get all addresses
$addresses = $user->addresses;

// Get default address
$defaultAddress = $user->defaultAddress;

// Create new address
$address = $user->addresses()->create([
    'name' => 'John Doe',
    'phone' => '081234567890',
    'province_code' => '33',
    'regency_code' => '3375',
    'district_code' => '337501',
    'village_code' => '3375011002',
    'address_line' => 'Jl. Merdeka No. 123',
    'postal_code' => '51111',
    'is_default' => true,
]);

// Set as default
$user->setDefaultAddress($address);
```

### HasAddress Trait

For models that have a single address:

```php
<?php

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
        // address fields will be added automatically
    ];
}
```

Single address usage:

```php
$store = Store::create([
    'name' => 'My Store',
    'description' => 'A great store',
    'province_code' => '33',
    'regency_code' => '3375',
    'district_code' => '337501',
    'village_code' => '3375011002',
    'address_line' => 'Jl. Toko No. 456',
    'postal_code' => '51111',
]);

// Access address information
echo $store->full_address;
echo $store->province->name;
echo $store->regency->name;
```

## Custom Address Model

You can create your own address model with additional fields:

```php
<?php

namespace App\Models;

use Creasi\Nusa\Models\Address as BaseAddress;

class CustomAddress extends BaseAddress
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'address_line',
        'postal_code',
        'is_default',
        // Custom fields
        'label',           // e.g., 'Home', 'Office'
        'notes',           // Additional notes
        'latitude',        // Custom coordinates
        'longitude',
        'is_verified',     // Address verification status
    ];
    
    protected $casts = [
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
    
    // Custom methods
    public function getFormattedLabelAttribute()
    {
        return $this->label ? ucfirst($this->label) : 'Address';
    }
    
    public function markAsVerified()
    {
        $this->update(['is_verified' => true]);
    }
}
```

Update your configuration to use the custom model:

```php
// config/creasi/nusa.php
'addressable' => \App\Models\CustomAddress::class,
```

## Address Validation

### Form Request Validation

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class StoreAddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_code' => [
                'required',
                'string',
                'exists:nusa.provinces,code'
            ],
            'regency_code' => [
                'required',
                'string',
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
                'string',
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
                'string',
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
    
    public function messages()
    {
        return [
            'province_code.required' => 'Please select a province.',
            'regency_code.required' => 'Please select a regency or city.',
            'district_code.required' => 'Please select a district.',
            'village_code.required' => 'Please select a village.',
            'address_line.required' => 'Please enter your detailed address.',
        ];
    }
}
```

### Custom Validation Rules

```php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Creasi\Nusa\Models\Village;

class ValidIndonesianAddress implements Rule
{
    public function passes($attribute, $value)
    {
        // Validate that all address components are consistent
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

// Usage in form request
'address' => ['required', 'array', new ValidIndonesianAddress],
```

## Address Controller Example

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use Creasi\Nusa\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()->with([
            'province', 'regency', 'district', 'village'
        ])->get();
        
        return view('addresses.index', compact('addresses'));
    }
    
    public function create()
    {
        return view('addresses.create');
    }
    
    public function store(StoreAddressRequest $request)
    {
        $address = auth()->user()->addresses()->create($request->validated());
        
        // Auto-fill postal code if not provided
        if (!$address->postal_code && $address->village) {
            $address->update(['postal_code' => $address->village->postal_code]);
        }
        
        // Set as default if it's the first address
        if (auth()->user()->addresses()->count() === 1) {
            $address->update(['is_default' => true]);
        }
        
        return redirect()->route('addresses.index')
            ->with('success', 'Address created successfully!');
    }
    
    public function show(Address $address)
    {
        $this->authorize('view', $address);
        
        return view('addresses.show', compact('address'));
    }
    
    public function edit(Address $address)
    {
        $this->authorize('update', $address);
        
        return view('addresses.edit', compact('address'));
    }
    
    public function update(UpdateAddressRequest $request, Address $address)
    {
        $this->authorize('update', $address);
        
        $address->update($request->validated());
        
        return redirect()->route('addresses.index')
            ->with('success', 'Address updated successfully!');
    }
    
    public function destroy(Address $address)
    {
        $this->authorize('delete', $address);
        
        // If this was the default address, set another as default
        if ($address->is_default) {
            $newDefault = auth()->user()->addresses()
                ->where('id', '!=', $address->id)
                ->first();
                
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }
        
        $address->delete();
        
        return redirect()->route('addresses.index')
            ->with('success', 'Address deleted successfully!');
    }
    
    public function setDefault(Address $address)
    {
        $this->authorize('update', $address);
        
        auth()->user()->setDefaultAddress($address);
        
        return redirect()->route('addresses.index')
            ->with('success', 'Default address updated!');
    }
}
```

## Address Policy

```php
<?php

namespace App\Policies;

use App\Models\User;
use Creasi\Nusa\Models\Address;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;
    
    public function view(User $user, Address $address)
    {
        return $user->id === $address->user_id;
    }
    
    public function create(User $user)
    {
        return true;
    }
    
    public function update(User $user, Address $address)
    {
        return $user->id === $address->user_id;
    }
    
    public function delete(User $user, Address $address)
    {
        return $user->id === $address->user_id;
    }
}
```

## Migration Customization

You can customize the address table migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->char('province_code', 2);
            $table->char('regency_code', 4);
            $table->char('district_code', 6);
            $table->char('village_code', 10);
            $table->text('address_line');
            $table->char('postal_code', 5)->nullable();
            $table->boolean('is_default')->default(false);
            
            // Custom fields
            $table->string('label')->nullable(); // 'Home', 'Office', etc.
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_verified')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_default']);
            $table->index('province_code');
            $table->index('regency_code');
            $table->index('district_code');
            $table->index('village_code');
            $table->index('postal_code');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
};
```

This address management system provides a complete solution for handling Indonesian addresses in your Laravel application, with proper validation, relationships, and flexibility for customization.
