# WithRegency Trait

The `WithRegency` trait adds a `belongsTo` relationship to the Regency model, allowing your models to be associated with a specific regency (kabupaten/kota).

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithRegency
```

## Usage

### Basic Implementation

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithRegency;

class DistrictOffice extends Model
{
    use WithRegency;
    
    protected $fillable = [
        'name',
        'address',
        'regency_code'
    ];
}
```

### Database Migration

```php
Schema::create('district_offices', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('address')->nullable();
    $table->string('regency_code');
    $table->timestamps();
    
    $table->foreign('regency_code')
          ->references('code')
          ->on('regencies')
          ->onDelete('cascade');
});
```

## Features

- Automatically adds `regency_code` to fillable
- Provides `regency()` relationship method
- Supports custom foreign key via `$regencyKey` property

### Basic Usage

```php
$office = DistrictOffice::with('regency.province')->first();

echo "Office: {$office->name}";
echo "Regency: {$office->regency->name}";
echo "Province: {$office->regency->province->name}";
```

## Common Use Cases

### Regional Service Centers

```php
class ServiceCenter extends Model
{
    use WithRegency;
    
    protected $fillable = [
        'name',
        'service_type',
        'regency_code'
    ];
    
    public function scopeInProvince($query, $provinceCode)
    {
        return $query->whereHas('regency', function ($q) use ($provinceCode) {
            $q->where('province_code', $provinceCode);
        });
    }
    
    public function getServiceAreaAttribute()
    {
        return "{$this->regency->name}, {$this->regency->province->name}";
    }
}

// Usage
$centers = ServiceCenter::inProvince('33')->get(); // Central Java
```

### Sales Territory Management

```php
class SalesTerritory extends Model
{
    use WithRegency;
    
    protected $fillable = [
        'name',
        'sales_rep_id',
        'regency_code',
        'target_revenue'
    ];
    
    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }
    
    public function getFullTerritoryNameAttribute()
    {
        return "{$this->name} - {$this->regency->name}";
    }
}
```

## Customization

### Custom Foreign Key

```php
class DistrictOffice extends Model
{
    use WithRegency;
    
    protected $regencyKey = 'office_regency_code';
    
    protected $fillable = [
        'name',
        'office_regency_code'
    ];
}
```

## Related Documentation

- **[Regency Model](/api/models/regency)** - Complete Regency model documentation
- **[WithProvince Trait](/api/concerns/with-province)** - For province-level associations
- **[WithDistrict Trait](/api/concerns/with-district)** - For district-level associations
- **[WithVillage Trait](/api/concerns/with-village)** - For village-level associations
