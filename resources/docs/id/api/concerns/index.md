# Ikhtisar Model Concerns

Laravel Nusa menyediakan berbagai trait (concerns) yang dapat Anda gunakan untuk menambahkan fungsionalitas lokasi ke model existing Anda. Trait-trait ini memungkinkan integrasi yang mudah dengan hierarki administratif Indonesia.

Laravel Nusa provides various traits (concerns) that you can use to add location functionality to your existing models. These traits enable easy integration with Indonesia's administrative hierarchy.

## Available Concerns

### Single Location Traits

Trait untuk menambahkan relasi ke satu lokasi administratif:

| Trait | Purpose | Relationship |
|-------|---------|--------------|
| **[WithProvince](/id/api/concerns/with-province)** | Relasi ke satu provinsi | `belongsTo` Province |
| **[WithRegency](/id/api/concerns/with-regency)** | Relasi ke satu kabupaten/kota | `belongsTo` Regency |
| **[WithDistrict](/id/api/concerns/with-district)** | Relasi ke satu kecamatan | `belongsTo` District |
| **[WithVillage](/id/api/concerns/with-village)** | Relasi ke satu kelurahan/desa | `belongsTo` Village |

### Multiple Location Traits

Trait untuk menambahkan relasi ke banyak lokasi administratif:

| Trait | Purpose | Relationship |
|-------|---------|--------------|
| **[WithDistricts](/id/api/concerns/with-districts)** | Relasi ke banyak kecamatan | `belongsToMany` District |
| **[WithVillages](/id/api/concerns/with-villages)** | Relasi ke banyak kelurahan/desa | `belongsToMany` Village |

### Address Management Traits

Trait untuk manajemen alamat:

| Trait | Purpose | Relationship |
|-------|---------|--------------|
| **[WithAddress](/id/api/concerns/with-address)** | Satu alamat per model | `morphOne` Address |
| **[WithAddresses](/id/api/concerns/with-addresses)** | Banyak alamat per model | `morphMany` Address |

### Geographic Traits

Trait untuk fungsionalitas geografis:

| Trait | Purpose | Features |
|-------|---------|----------|
| **[WithCoordinate](/id/api/concerns/with-coordinate)** | Koordinat geografis | Distance calculation, nearby queries |

## Quick Start

### Basic Usage

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class User extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
}

// Usage
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'village_code' => '33.74.01.1001'
]);

// Access location hierarchy
echo $user->village->name;           // Village name
echo $user->village->district->name; // District name
echo $user->village->regency->name;  // Regency name
echo $user->village->province->name; // Province name
```

### Multiple Traits

```php
use Creasi\Nusa\Models\Concerns\{WithVillage, WithAddresses, WithCoordinate};

class Store extends Model
{
    use WithVillage, WithAddresses, WithCoordinate;
    
    protected $fillable = [
        'name',
        'village_code',
        'latitude',
        'longitude'
    ];
}

// Usage
$store = Store::create([
    'name' => 'My Store',
    'village_code' => '33.74.01.1001',
    'latitude' => -6.2088,
    'longitude' => 106.8456
]);

// Add address
$store->addresses()->create([
    'name' => 'Store Address',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123'
]);

// Find nearby stores
$nearby = Store::nearby(-6.2088, 106.8456, 10)->get();
```

## Common Patterns

### User Location Management

```php
class User extends Model
{
    use WithVillage, WithAddresses;
    
    protected $fillable = ['name', 'email', 'village_code'];
    
    // Get user's full location
    public function getFullLocationAttribute()
    {
        if ($this->village) {
            return "{$this->village->name}, {$this->village->district->name}, {$this->village->regency->name}, {$this->village->province->name}";
        }
        return null;
    }
    
    // Get default address
    public function getDefaultAddressAttribute()
    {
        return $this->addresses()->where('is_default', true)->first();
    }
}
```

### Business Location Management

```php
class Business extends Model
{
    use WithRegency, WithCoordinate, WithAddresses;
    
    protected $fillable = [
        'name',
        'regency_code',
        'latitude',
        'longitude'
    ];
    
    // Get service area (villages within radius)
    public function getServiceAreaAttribute()
    {
        return Village::nearby($this->latitude, $this->longitude, 25)->get();
    }
    
    // Get business coverage by regency
    public function getCoverageStats()
    {
        return [
            'regency' => $this->regency->name,
            'province' => $this->regency->province->name,
            'service_radius' => 25, // km
            'covered_villages' => $this->service_area->count()
        ];
    }
}
```

### Delivery Zone Management

```php
class DeliveryZone extends Model
{
    use WithVillages;
    
    protected $fillable = ['name', 'description'];
    
    // Add villages by postal code
    public function addVillagesByPostalCode($postalCode)
    {
        $villages = Village::where('postal_code', $postalCode)->get();
        $this->villages()->attach($villages->pluck('code'));
        
        return $villages->count();
    }
    
    // Get coverage statistics
    public function getCoverageStats()
    {
        $villages = $this->villages()->with(['district.regency.province'])->get();
        
        return [
            'total_villages' => $villages->count(),
            'provinces' => $villages->pluck('province.name')->unique()->values(),
            'regencies' => $villages->pluck('regency.name')->unique()->values(),
            'postal_codes' => $villages->pluck('postal_code')->unique()->sort()->values()
        ];
    }
}
```

## Advanced Usage

### Custom Scopes with Traits

```php
class Customer extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
    
    // Scope for customers in Java
    public function scopeInJava($query)
    {
        return $query->whereHas('village.province', function ($q) {
            $q->whereIn('code', ['31', '32', '33', '34', '35', '36']);
        });
    }
    
    // Scope for customers in specific regency
    public function scopeInRegency($query, $regencyCode)
    {
        return $query->whereHas('village', function ($q) use ($regencyCode) {
            $q->where('regency_code', $regencyCode);
        });
    }
}

// Usage
$javaCustomers = Customer::inJava()->get();
$semarangCustomers = Customer::inRegency('33.74')->get();
```

### Trait Method Conflicts

If you have method name conflicts, use trait aliases:

```php
class User extends Model
{
    use WithVillage {
        WithVillage::village as nusaVillage;
    }
    
    // Your custom village method
    public function village()
    {
        // Custom implementation
        return $this->belongsTo(CustomVillage::class);
    }
    
    // Access Nusa village
    public function getNusaVillage()
    {
        return $this->nusaVillage();
    }
}
```

## Performance Considerations

### Eager Loading

```php
// Load relationships efficiently
$users = User::with(['village.district.regency.province'])->get();

// Load only needed fields
$users = User::with(['village' => function ($query) {
    $query->select('code', 'name', 'district_code');
}])->get();
```

### Caching

```php
class CachedLocationService
{
    public function getUsersByProvince($provinceCode)
    {
        return Cache::remember("users_province_{$provinceCode}", 3600, function () use ($provinceCode) {
            return User::whereHas('village', function ($q) use ($provinceCode) {
                $q->where('province_code', $provinceCode);
            })->get();
        });
    }
}
```

## Validation

### Form Requests with Traits

```php
class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'village_code' => [
                'required',
                'exists:nusa.villages,code'
            ]
        ];
    }
    
    public function messages()
    {
        return [
            'village_code.exists' => 'The selected village is invalid.'
        ];
    }
}
```

### Custom Validation Rules

```php
class ValidLocationHierarchy implements Rule
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function passes($attribute, $value)
    {
        $village = Village::find($value);
        
        if (!$village) {
            return false;
        }
        
        // Validate hierarchy consistency
        if (isset($this->data['regency_code'])) {
            return $village->regency_code === $this->data['regency_code'];
        }
        
        return true;
    }
    
    public function message()
    {
        return 'The location hierarchy is not consistent.';
    }
}
```

## Testing

### Testing Models with Traits

```php
class UserTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_has_village_relationship()
    {
        $village = Village::factory()->create();
        $user = User::factory()->create(['village_code' => $village->code]);
        
        $this->assertInstanceOf(Village::class, $user->village);
        $this->assertEquals($village->code, $user->village->code);
    }
    
    public function test_user_can_access_province_through_village()
    {
        $province = Province::factory()->create();
        $regency = Regency::factory()->create(['province_code' => $province->code]);
        $district = District::factory()->create(['regency_code' => $regency->code]);
        $village = Village::factory()->create(['district_code' => $district->code]);
        $user = User::factory()->create(['village_code' => $village->code]);
        
        $this->assertEquals($province->name, $user->village->province->name);
    }
}
```

## Next Steps

### **Individual Trait Documentation**:

- **[WithProvince](/id/api/concerns/with-province)** - Province relationship trait
- **[WithRegency](/id/api/concerns/with-regency)** - Regency relationship trait
- **[WithDistrict](/id/api/concerns/with-district)** - District relationship trait
- **[WithVillage](/id/api/concerns/with-village)** - Village relationship trait
- **[WithAddresses](/id/api/concerns/with-addresses)** - Multiple addresses trait
- **[WithCoordinate](/id/api/concerns/with-coordinate)** - Geographic coordinates trait

### **Related Documentation**:

- **[Models Overview](/id/api/models/overview)** - Complete model documentation
- **[Customization Guide](/id/guide/customization)** - Advanced customization patterns
- **[Custom Models Example](/id/examples/custom-models)** - Practical implementation examples
