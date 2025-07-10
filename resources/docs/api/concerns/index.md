# Model Concerns (Traits)

Laravel Nusa provides a collection of reusable traits (concerns) that you can use to add Indonesian administrative region functionality to your own models. These traits make it easy to integrate location-based features into your application's models.

## Overview

The model concerns are located in the `Creasi\Nusa\Models\Concerns` namespace and provide different types of relationships and functionality:

### Relationship Traits

- **[WithProvince](/api/concerns/with-province)** - Adds a `belongsTo` relationship to a province
- **[WithRegency](/api/concerns/with-regency)** - Adds a `belongsTo` relationship to a regency
- **[WithDistrict](/api/concerns/with-district)** - Adds a `belongsTo` relationship to a district
- **[WithVillage](/api/concerns/with-village)** - Adds a `belongsTo` relationship to a village
- **[WithDistricts](/api/concerns/with-districts)** - Adds a `hasMany` relationship to districts
- **[WithVillages](/api/concerns/with-villages)** - Adds a `hasMany` relationship to villages with postal codes

### Address Management Traits

- **[WithAddress](/api/concerns/with-address)** - Adds a single polymorphic address relationship
- **[WithAddresses](/api/concerns/with-addresses)** - Adds multiple polymorphic address relationships

### Geographic Traits

- **[WithCoordinate](/api/concerns/with-coordinate)** - Adds latitude/longitude coordinate functionality

## Common Use Cases

### User Profiles with Location

```php
use Creasi\Nusa\Models\Concerns\{WithProvince, WithRegency, WithDistrict, WithVillage};

class UserProfile extends Model
{
    use WithProvince, WithRegency, WithDistrict, WithVillage;
    
    protected $fillable = [
        'name', 'email', 
        'province_code', 'regency_code', 'district_code', 'village_code'
    ];
}

// Usage
$profile = UserProfile::with(['province', 'regency', 'district', 'village'])->first();
echo "Location: {$profile->village->name}, {$profile->district->name}, {$profile->regency->name}, {$profile->province->name}";
```

### Business Locations

```php
use Creasi\Nusa\Models\Concerns\{WithAddress, WithCoordinate};

class Store extends Model
{
    use WithAddress, WithCoordinate;
    
    protected $fillable = [
        'name', 'description', 'latitude', 'longitude'
    ];
}

// Usage
$store = Store::with('address')->first();
echo "Store: {$store->name} at {$store->latitude}, {$store->longitude}";
```

### Regional Management

```php
use Creasi\Nusa\Models\Concerns\{WithProvince, WithDistricts, WithVillages};

class RegionalOffice extends Model
{
    use WithProvince, WithDistricts, WithVillages;
    
    protected $fillable = ['name', 'province_code'];
}

// Usage
$office = RegionalOffice::with(['province', 'districts', 'villages'])->first();
echo "Office covers {$office->districts->count()} districts and {$office->villages->count()} villages";
```

## Installation and Setup

### 1. Use Traits in Your Models

Simply add the `use` statement for the traits you need:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithVillage;

class Customer extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
}
```

### 2. Database Migration

Ensure your database table has the appropriate foreign key columns:

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

### 3. Customize Foreign Key Names

You can customize the foreign key column names by defining properties:

```php
class Customer extends Model
{
    use WithVillage;
    
    protected $villageKey = 'customer_village_code';
    protected $fillable = ['name', 'email', 'customer_village_code'];
}
```

## Advanced Usage

### Multiple Address Support

```php
use Creasi\Nusa\Models\Concerns\WithAddresses;

class Company extends Model
{
    use WithAddresses;
}

// Usage
$company = Company::first();
$company->addresses()->create([
    'type' => 'headquarters',
    'province_code' => '33',
    'regency_code' => '33.75',
    'district_code' => '33.75.01',
    'village_code' => '33.75.01.1002',
    'address_line' => 'Jl. Merdeka No. 123'
]);
```

### Geographic Queries

```php
use Creasi\Nusa\Models\Concerns\WithCoordinate;

class Event extends Model
{
    use WithCoordinate;
}

// Find events within a radius
$nearbyEvents = Event::selectRaw('*, (
    6371 * acos(
        cos(radians(?)) * cos(radians(latitude)) * 
        cos(radians(longitude) - radians(?)) + 
        sin(radians(?)) * sin(radians(latitude))
    )
) AS distance', [$lat, $lng, $lat])
->having('distance', '<', 10)
->orderBy('distance')
->get();
```

## Best Practices

### 1. Use Appropriate Traits

Choose traits based on your model's needs:
- Use `WithVillage` for the most specific location
- Use `WithProvince` for regional grouping
- Use `WithAddresses` for models that can have multiple locations

### 2. Eager Loading

Always eager load relationships to avoid N+1 queries:

```php
$customers = Customer::with(['village.district.regency.province'])->get();
```

### 3. Validation

Validate administrative codes in your form requests:

```php
public function rules()
{
    return [
        'village_code' => 'required|exists:nusa.villages,code',
        'district_code' => 'required|exists:nusa.districts,code',
        'regency_code' => 'required|exists:nusa.regencies,code',
        'province_code' => 'required|exists:nusa.provinces,code',
    ];
}
```

## Related Documentation

- **[Address Management Guide](/guide/addresses)** - Complete guide to using address functionality
- **[Models & Relationships](/guide/models)** - Understanding Laravel Nusa's core models
- **[Custom Models Example](/examples/custom-models)** - Practical examples of using traits
- **[Address Forms Example](/examples/address-forms)** - Building forms with administrative regions
