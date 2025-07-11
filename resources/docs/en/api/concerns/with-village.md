# WithVillage Trait

The `WithVillage` trait adds a `belongsTo` relationship to the Village model, allowing your models to be associated with a specific village (the most granular administrative level in Indonesia).

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithVillage
```

## Usage

### Basic Implementation

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithVillage;

class Customer extends Model
{
    use WithVillage;
    
    protected $fillable = [
        'name',
        'email',
        'village_code'
    ];
}
```

### Database Migration

```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('village_code')->nullable();
    $table->timestamps();
    
    // Optional: Add foreign key constraint
    $table->foreign('village_code')
          ->references('code')
          ->on('villages')
          ->onDelete('set null');
});
```

## Features

### Automatic Fillable

The trait automatically adds the village foreign key to the model's `$fillable` array:

```php
// Automatically added to fillable
protected $fillable = ['village_code']; // or custom key name
```

### Village Relationship

Access the associated village:

```php
$customer = Customer::find(1);
$village = $customer->village;

echo "Customer lives in: {$village->name}";
echo "Postal code: {$village->postal_code}";
```

### Eager Loading

```php
$customers = Customer::with('village')->get();

foreach ($customers as $customer) {
    echo "{$customer->name} - {$customer->village->name}";
}
```

### Deep Relationships

Access parent administrative levels through the village:

```php
$customer = Customer::with('village.district.regency.province')->first();

echo "Full address: ";
echo "{$customer->village->name}, ";
echo "{$customer->village->district->name}, ";
echo "{$customer->village->regency->name}, ";
echo "{$customer->village->province->name}";
```

## Customization

### Custom Foreign Key

You can customize the foreign key column name:

```php
class Customer extends Model
{
    use WithVillage;
    
    protected $villageKey = 'customer_village_code';
    
    protected $fillable = [
        'name',
        'email',
        'customer_village_code'
    ];
}
```

### Migration with Custom Key

```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('customer_village_code')->nullable();
    $table->timestamps();
    
    $table->foreign('customer_village_code')
          ->references('code')
          ->on('villages')
          ->onDelete('set null');
});
```

## Common Use Cases

### 1. Customer Management

```php
class Customer extends Model
{
    use WithVillage;
    
    public function getFullAddressAttribute()
    {
        if (!$this->village) return null;
        
        return collect([
            $this->village->name,
            $this->village->district->name,
            $this->village->regency->name,
            $this->village->province->name
        ])->implode(', ');
    }
}

// Usage
$customer = Customer::with('village.district.regency.province')->first();
echo $customer->full_address;
```

### 2. Delivery Management

```php
class DeliveryAddress extends Model
{
    use WithVillage;
    
    protected $fillable = [
        'recipient_name',
        'phone',
        'address_line',
        'village_code'
    ];
    
    public function getShippingCostAttribute()
    {
        // Calculate shipping cost based on village location
        $province = $this->village->province;
        
        return match($province->code) {
            '31', '32', '33', '34', '35', '36' => 15000, // Java
            '51', '52' => 20000, // Bali & Nusa Tenggara
            default => 25000 // Other islands
        };
    }
}
```

### 3. Event Registration

```php
class EventRegistration extends Model
{
    use WithVillage;
    
    protected $fillable = [
        'participant_name',
        'email',
        'village_code'
    ];
    
    public function scopeFromProvince($query, $provinceCode)
    {
        return $query->whereHas('village.province', function ($q) use ($provinceCode) {
            $q->where('code', $provinceCode);
        });
    }
    
    public function scopeFromRegency($query, $regencyCode)
    {
        return $query->whereHas('village.regency', function ($q) use ($regencyCode) {
            $q->where('code', $regencyCode);
        });
    }
}

// Usage
$jakartaParticipants = EventRegistration::fromProvince('31')->get();
$semarangParticipants = EventRegistration::fromRegency('33.74')->get();
```

## Validation

### Form Request Validation

```php
class CustomerRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers',
            'village_code' => 'required|exists:nusa.villages,code'
        ];
    }
    
    public function messages()
    {
        return [
            'village_code.required' => 'Please select a village.',
            'village_code.exists' => 'The selected village is invalid.'
        ];
    }
}
```

### Model Validation

```php
class Customer extends Model
{
    use WithVillage;
    
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($customer) {
            if ($customer->village_code && !Village::where('code', $customer->village_code)->exists()) {
                throw new \InvalidArgumentException('Invalid village code');
            }
        });
    }
}
```

## Performance Tips

### 1. Eager Loading

Always eager load the village relationship to avoid N+1 queries:

```php
// Good
$customers = Customer::with('village')->get();

// Bad - causes N+1 queries
$customers = Customer::all();
foreach ($customers as $customer) {
    echo $customer->village->name; // N+1 query
}
```

### 2. Selective Loading

Load only the fields you need:

```php
$customers = Customer::with(['village:code,name,postal_code'])->get();
```

### 3. Indexing

Add database indexes for better query performance:

```php
Schema::table('customers', function (Blueprint $table) {
    $table->index('village_code');
});
```

## Related Documentation

- **[Village Model](/en/api/models/village)** - Complete Village model documentation
- **[WithDistrict Trait](/en/api/concerns/with-district)** - For district-level associations
- **[WithRegency Trait](/en/api/concerns/with-regency)** - For regency-level associations
- **[WithProvince Trait](/en/api/concerns/with-province)** - For province-level associations
- **[Address Forms Example](/en/examples/address-forms)** - Building cascading address forms
