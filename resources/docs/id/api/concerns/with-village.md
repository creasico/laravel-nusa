# WithVillage

Trait `WithVillage` memungkinkan model Anda memiliki relasi ke satu kelurahan/desa, memberikan akses ke hierarki administratif lengkap Indonesia dari tingkat desa hingga provinsi.

The `WithVillage` trait allows your model to have a relationship to a single village, providing access to Indonesia's complete administrative hierarchy from village to province level.

## Overview

The `WithVillage` trait is one of the most commonly used traits in Laravel Nusa as it provides the most granular level of location data. When your model uses this trait, it gains access to the complete administrative hierarchy through the village relationship.

### What You Get

- **Village relationship** - Direct access to village data
- **Complete hierarchy** - Access to district, regency, and province through village
- **Postal code access** - Automatic postal code through village
- **Geographic coordinates** - Latitude and longitude from village data

## Basic Usage

### Adding the Trait

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class User extends Model
{
    use WithVillage;
    
    protected $fillable = [
        'name',
        'email',
        'village_code'
    ];
}
```

### Database Requirements

Your model's table must have a `village_code` column:

```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->string('village_code', 13)->nullable();
    $table->foreign('village_code')->references('code')->on('nusa.villages');
});
```

### Creating Records

```php
// Create user with village
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'village_code' => '33.74.01.1001'
]);

// Access village data
echo $user->village->name; // Village name
echo $user->village->postal_code; // Postal code
```

## Accessing Administrative Hierarchy

### Complete Hierarchy Access

```php
$user = User::with(['village.district.regency.province'])->first();

// Access all levels
echo $user->village->name;           // "Medono"
echo $user->village->district->name; // "Semarang Tengah"
echo $user->village->regency->name;  // "Kota Semarang"
echo $user->village->province->name; // "Jawa Tengah"

// Access postal code
echo $user->village->postal_code; // "50132"
```

### Helper Methods

You can add helper methods to your model:

```php
class User extends Model
{
    use WithVillage;
    
    // Get full address string
    public function getFullLocationAttribute()
    {
        if ($this->village) {
            return "{$this->village->name}, {$this->village->district->name}, {$this->village->regency->name}, {$this->village->province->name}";
        }
        return null;
    }
    
    // Get postal code
    public function getPostalCodeAttribute()
    {
        return $this->village?->postal_code;
    }
    
    // Check if user is in specific province
    public function isInProvince($provinceCode)
    {
        return $this->village?->province_code === $provinceCode;
    }
    
    // Check if user is in Java
    public function isInJava()
    {
        $javaCodes = ['31', '32', '33', '34', '35', '36'];
        return in_array($this->village?->province_code, $javaCodes);
    }
}

// Usage
echo $user->full_location; // "Medono, Semarang Tengah, Kota Semarang, Jawa Tengah"
echo $user->postal_code;   // "50132"
echo $user->isInJava() ? 'Yes' : 'No'; // "Yes"
```

## Querying with Village Relationships

### Basic Queries

```php
// Get users with their villages
$users = User::with('village')->get();

// Get users in specific village
$users = User::where('village_code', '33.74.01.1001')->get();

// Get users with villages (only those who have village_code)
$users = User::whereNotNull('village_code')->with('village')->get();
```

### Advanced Filtering

```php
// Users in specific province
$users = User::whereHas('village', function ($query) {
    $query->where('province_code', '33');
})->get();

// Users in specific regency
$users = User::whereHas('village', function ($query) {
    $query->where('regency_code', '33.74');
})->get();

// Users in specific district
$users = User::whereHas('village', function ($query) {
    $query->where('district_code', '33.74.01');
})->get();

// Users by postal code
$users = User::whereHas('village', function ($query) {
    $query->where('postal_code', '50132');
})->get();

// Users in Java provinces
$users = User::whereHas('village', function ($query) {
    $query->whereIn('province_code', ['31', '32', '33', '34', '35', '36']);
})->get();
```

### Custom Scopes

```php
class User extends Model
{
    use WithVillage;
    
    // Scope for users in specific province
    public function scopeInProvince($query, $provinceCode)
    {
        return $query->whereHas('village', function ($q) use ($provinceCode) {
            $q->where('province_code', $provinceCode);
        });
    }
    
    // Scope for users in specific regency
    public function scopeInRegency($query, $regencyCode)
    {
        return $query->whereHas('village', function ($q) use ($regencyCode) {
            $q->where('regency_code', $regencyCode);
        });
    }
    
    // Scope for users in Java
    public function scopeInJava($query)
    {
        return $query->whereHas('village', function ($q) {
            $q->whereIn('province_code', ['31', '32', '33', '34', '35', '36']);
        });
    }
    
    // Scope for users by postal code
    public function scopeByPostalCode($query, $postalCode)
    {
        return $query->whereHas('village', function ($q) use ($postalCode) {
            $q->where('postal_code', $postalCode);
        });
    }
}

// Usage
$javaUsers = User::inJava()->get();
$semarangUsers = User::inRegency('33.74')->get();
$centralJavaUsers = User::inProvince('33')->get();
$postalUsers = User::byPostalCode('50132')->get();
```

## Geographic Operations

### Distance Calculations

```php
class User extends Model
{
    use WithVillage;
    
    // Get users near a specific location
    public static function nearLocation($latitude, $longitude, $radiusKm = 10)
    {
        return static::whereHas('village', function ($query) use ($latitude, $longitude, $radiusKm) {
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
        })->get();
    }
    
    // Get distance to another user
    public function distanceTo(User $otherUser)
    {
        if (!$this->village || !$otherUser->village) {
            return null;
        }
        
        $earthRadius = 6371; // km
        
        $lat1 = deg2rad($this->village->latitude);
        $lng1 = deg2rad($this->village->longitude);
        $lat2 = deg2rad($otherUser->village->latitude);
        $lng2 = deg2rad($otherUser->village->longitude);
        
        $deltaLat = $lat2 - $lat1;
        $deltaLng = $lng2 - $lng1;
        
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}

// Usage
$nearbyUsers = User::nearLocation(-6.2088, 106.8456, 25);
$distance = $user1->distanceTo($user2);
```

## Validation

### Form Request Validation

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
                'string',
                'exists:nusa.villages,code'
            ]
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

### Custom Validation Rules

```php
class ValidVillageCode implements Rule
{
    public function passes($attribute, $value)
    {
        return Village::where('code', $value)->exists();
    }
    
    public function message()
    {
        return 'The selected village code is invalid.';
    }
}

// Usage in form request
public function rules()
{
    return [
        'village_code' => ['required', new ValidVillageCode]
    ];
}
```

## API Resources

### User Resource with Village

```php
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'location' => [
                'village' => $this->village?->name,
                'district' => $this->village?->district?->name,
                'regency' => $this->village?->regency?->name,
                'province' => $this->village?->province?->name,
                'postal_code' => $this->village?->postal_code,
                'full_address' => $this->full_location
            ],
            'coordinates' => [
                'latitude' => $this->village?->latitude,
                'longitude' => $this->village?->longitude
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
```

## Performance Optimization

### Efficient Loading

```php
// Load users with village hierarchy efficiently
$users = User::with(['village.district.regency.province'])->get();

// Load only needed fields
$users = User::with(['village' => function ($query) {
    $query->select('code', 'name', 'postal_code', 'district_code');
}, 'village.district' => function ($query) {
    $query->select('code', 'name', 'regency_code');
}])->get();

// Count users by province without loading all data
$provinceCounts = User::join('nusa.villages', 'users.village_code', '=', 'villages.code')
    ->join('nusa.provinces', 'villages.province_code', '=', 'provinces.code')
    ->groupBy('provinces.code', 'provinces.name')
    ->selectRaw('provinces.code, provinces.name, count(*) as user_count')
    ->get();
```

### Caching

```php
class CachedUserService
{
    public function getUsersByProvince($provinceCode)
    {
        return Cache::remember("users_province_{$provinceCode}", 3600, function () use ($provinceCode) {
            return User::inProvince($provinceCode)->with('village')->get();
        });
    }
    
    public function getUserLocationStats()
    {
        return Cache::remember('user_location_stats', 3600, function () {
            return [
                'total_users' => User::whereNotNull('village_code')->count(),
                'provinces' => User::join('nusa.villages', 'users.village_code', '=', 'villages.code')
                    ->distinct('villages.province_code')
                    ->count(),
                'regencies' => User::join('nusa.villages', 'users.village_code', '=', 'villages.code')
                    ->distinct('villages.regency_code')
                    ->count()
            ];
        });
    }
}
```

## Testing

### Model Tests

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
    
    public function test_user_full_location_attribute()
    {
        $user = User::factory()->create();
        $village = Village::factory()->create();
        $user->village_code = $village->code;
        $user->save();
        
        $user->load('village.district.regency.province');
        
        $expectedLocation = "{$village->name}, {$village->district->name}, {$village->regency->name}, {$village->province->name}";
        $this->assertEquals($expectedLocation, $user->full_location);
    }
    
    public function test_user_scope_in_province()
    {
        $province = Province::factory()->create(['code' => '33']);
        $village = Village::factory()->create(['province_code' => '33']);
        $user = User::factory()->create(['village_code' => $village->code]);
        
        $users = User::inProvince('33')->get();
        
        $this->assertTrue($users->contains($user));
    }
}
```

## Common Use Cases

### E-commerce Customer Management

```php
class Customer extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'phone', 'village_code'];
    
    // Get shipping cost based on location
    public function getShippingCost()
    {
        if (!$this->village) {
            return 0;
        }
        
        // Different rates for different provinces
        $rates = [
            '31' => 15000, // DKI Jakarta
            '32' => 20000, // Jawa Barat
            '33' => 18000, // Jawa Tengah
            // ... other provinces
        ];
        
        return $rates[$this->village->province_code] ?? 25000;
    }
    
    // Check if customer is in delivery area
    public function isInDeliveryArea()
    {
        $deliveryProvinces = ['31', '32', '33', '34', '35', '36']; // Java only
        return in_array($this->village?->province_code, $deliveryProvinces);
    }
}
```

### Service Provider Coverage

```php
class ServiceProvider extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'village_code', 'service_radius'];
    
    // Get coverage area
    public function getCoverageArea()
    {
        if (!$this->village) {
            return collect();
        }
        
        return Village::selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(latitude))
            )) AS distance
        ", [$this->village->latitude, $this->village->longitude, $this->village->latitude])
        ->having('distance', '<=', $this->service_radius)
        ->orderBy('distance')
        ->get();
    }
}
```

## Next Steps

- **[WithAddresses](/id/api/concerns/with-addresses)** - Multiple addresses management
- **[WithCoordinate](/id/api/concerns/with-coordinate)** - Geographic coordinates
- **[WithRegency](/id/api/concerns/with-regency)** - Regency-level relationships
- **[Village Model](/id/api/models/village)** - Complete village model documentation
