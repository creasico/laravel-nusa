# WithDistrict Trait

The `WithDistrict` trait adds a `belongsTo` relationship to the District model, allowing your models to be associated with a specific district (kecamatan).

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithDistrict
```

## Usage

### Basic Implementation

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithDistrict;

class HealthCenter extends Model
{
    use WithDistrict;
    
    protected $fillable = [
        'name',
        'address',
        'district_code'
    ];
}
```

### Database Migration

```php
Schema::create('health_centers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('address')->nullable();
    $table->string('district_code');
    $table->timestamps();
    
    $table->foreign('district_code')
          ->references('code')
          ->on('districts')
          ->onDelete('cascade');
});
```

## Features

- Automatically adds `district_code` to fillable
- Provides `district()` relationship method
- Supports custom foreign key via `$districtKey` property

### Basic Usage

```php
$center = HealthCenter::with('district.regency.province')->first();

echo "Center: {$center->name}";
echo "District: {$center->district->name}";
echo "Regency: {$center->district->regency->name}";
echo "Province: {$center->district->regency->province->name}";
```

## Common Use Cases

### Public Service Facilities

```php
class PublicFacility extends Model
{
    use WithDistrict;
    
    protected $fillable = [
        'name',
        'facility_type',
        'district_code'
    ];
    
    public function scopeByType($query, $type)
    {
        return $query->where('facility_type', $type);
    }
    
    public function getLocationDescriptionAttribute()
    {
        return "District {$this->district->name}, {$this->district->regency->name}";
    }
}

// Usage
$schools = PublicFacility::byType('school')
    ->with('district.regency')
    ->get();
```

### Local Government Services

```php
class GovernmentService extends Model
{
    use WithDistrict;
    
    protected $fillable = [
        'service_name',
        'description',
        'district_code',
        'operating_hours'
    ];
    
    public function scopeInRegency($query, $regencyCode)
    {
        return $query->whereHas('district', function ($q) use ($regencyCode) {
            $q->where('regency_code', $regencyCode);
        });
    }
}
```

## Customization

### Custom Foreign Key

```php
class HealthCenter extends Model
{
    use WithDistrict;
    
    protected $districtKey = 'service_district_code';
    
    protected $fillable = [
        'name',
        'service_district_code'
    ];
}
```

## Related Documentation

- **[District Model](/en/api/models/district)** - Complete District model documentation
- **[WithProvince Trait](/en/api/concerns/with-province)** - For province-level associations
- **[WithRegency Trait](/en/api/concerns/with-regency)** - For regency-level associations
- **[WithVillage Trait](/en/api/concerns/with-village)** - For village-level associations
