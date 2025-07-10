# WithProvince Trait

The `WithProvince` trait adds a `belongsTo` relationship to the Province model, allowing your models to be associated with a specific province (the highest administrative level in Indonesia).

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithProvince
```

## Usage

### Basic Implementation

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithProvince;

class RegionalOffice extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'name',
        'description',
        'province_code'
    ];
}
```

### Database Migration

```php
Schema::create('regional_offices', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('province_code');
    $table->timestamps();
    
    // Optional: Add foreign key constraint
    $table->foreign('province_code')
          ->references('code')
          ->on('provinces')
          ->onDelete('cascade');
});
```

## Features

### Automatic Fillable

The trait automatically adds the province foreign key to the model's `$fillable` array:

```php
// Automatically added to fillable
protected $fillable = ['province_code']; // or custom key name
```

### Province Relationship

Access the associated province:

```php
$office = RegionalOffice::find(1);
$province = $office->province;

echo "Office located in: {$province->name}";
echo "Province coordinates: {$province->latitude}, {$province->longitude}";
```

### Eager Loading

```php
$offices = RegionalOffice::with('province')->get();

foreach ($offices as $office) {
    echo "{$office->name} - {$office->province->name}";
}
```

## Customization

### Custom Foreign Key

You can customize the foreign key column name:

```php
class RegionalOffice extends Model
{
    use WithProvince;
    
    protected $provinceKey = 'office_province_code';
    
    protected $fillable = [
        'name',
        'description',
        'office_province_code'
    ];
}
```

## Common Use Cases

### 1. Regional Business Management

```php
class Branch extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'name',
        'address',
        'phone',
        'province_code'
    ];
    
    public function scopeInJava($query)
    {
        return $query->whereHas('province', function ($q) {
            $q->whereIn('code', ['31', '32', '33', '34', '35', '36']);
        });
    }
    
    public function scopeOutsideJava($query)
    {
        return $query->whereHas('province', function ($q) {
            $q->whereNotIn('code', ['31', '32', '33', '34', '35', '36']);
        });
    }
}

// Usage
$javaBranches = Branch::inJava()->get();
$outsideJavaBranches = Branch::outsideJava()->get();
```

### 2. Regional Statistics

```php
class SalesReport extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'month',
        'year',
        'total_sales',
        'province_code'
    ];
    
    protected $casts = [
        'total_sales' => 'decimal:2'
    ];
    
    public static function getProvinceRanking($year)
    {
        return static::with('province')
            ->where('year', $year)
            ->groupBy('province_code')
            ->selectRaw('province_code, SUM(total_sales) as total')
            ->orderByDesc('total')
            ->get();
    }
}

// Usage
$ranking = SalesReport::getProvinceRanking(2024);
foreach ($ranking as $report) {
    echo "{$report->province->name}: Rp " . number_format($report->total);
}
```

### 3. Event Management

```php
class Event extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'province_code'
    ];
    
    protected $casts = [
        'event_date' => 'datetime'
    ];
    
    public function getRegionalEventsAttribute()
    {
        return static::where('province_code', $this->province_code)
            ->where('id', '!=', $this->id)
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(5)
            ->get();
    }
}
```

### 4. Shipping Zones

```php
class ShippingZone extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'name',
        'base_cost',
        'per_kg_cost',
        'province_code'
    ];
    
    protected $casts = [
        'base_cost' => 'decimal:2',
        'per_kg_cost' => 'decimal:2'
    ];
    
    public function calculateShippingCost($weight)
    {
        return $this->base_cost + ($this->per_kg_cost * $weight);
    }
    
    public static function findByProvince($provinceCode)
    {
        return static::where('province_code', $provinceCode)->first();
    }
}

// Usage
$zone = ShippingZone::findByProvince('33'); // Central Java
$cost = $zone->calculateShippingCost(2.5); // 2.5 kg
```

## Advanced Queries

### Geographic Grouping

```php
// Group by island regions
$branches = Branch::with('province')
    ->get()
    ->groupBy(function ($branch) {
        $code = $branch->province->code;
        
        if (in_array($code, ['31', '32', '33', '34', '35', '36'])) {
            return 'Java';
        } elseif (in_array($code, ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21'])) {
            return 'Sumatra';
        } elseif (in_array($code, ['61', '62', '63', '64', '65'])) {
            return 'Kalimantan';
        } elseif (in_array($code, ['71', '72', '73', '74', '75', '76'])) {
            return 'Sulawesi';
        } elseif (in_array($code, ['51', '52', '53'])) {
            return 'Bali & Nusa Tenggara';
        } elseif (in_array($code, ['81', '82', '91', '92', '93', '94', '95', '96', '97'])) {
            return 'Eastern Indonesia';
        }
        
        return 'Other';
    });
```

### Statistical Analysis

```php
class ProvinceAnalytics
{
    public static function getCustomerDistribution()
    {
        return Customer::with('province')
            ->groupBy('province_code')
            ->selectRaw('province_code, COUNT(*) as customer_count')
            ->orderByDesc('customer_count')
            ->get()
            ->map(function ($item) {
                return [
                    'province' => $item->province->name,
                    'count' => $item->customer_count,
                    'percentage' => round(($item->customer_count / Customer::count()) * 100, 2)
                ];
            });
    }
}
```

## Validation

### Form Request Validation

```php
class RegionalOfficeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'province_code' => 'required|exists:nusa.provinces,code'
        ];
    }
    
    public function messages()
    {
        return [
            'province_code.required' => 'Please select a province.',
            'province_code.exists' => 'The selected province is invalid.'
        ];
    }
}
```

## Performance Tips

### 1. Eager Loading

```php
// Good
$offices = RegionalOffice::with('province')->get();

// Bad - N+1 queries
$offices = RegionalOffice::all();
foreach ($offices as $office) {
    echo $office->province->name; // N+1 query
}
```

### 2. Selective Fields

```php
$offices = RegionalOffice::with(['province:code,name'])->get();
```

### 3. Caching Province Data

```php
class RegionalOffice extends Model
{
    use WithProvince;
    
    public function getProvinceNameAttribute()
    {
        return Cache::remember(
            "province_name_{$this->province_code}",
            3600,
            fn() => $this->province->name
        );
    }
}
```

## Related Documentation

- **[Province Model](/en/api/models/province)** - Complete Province model documentation
- **[WithRegency Trait](/en/api/concerns/with-regency)** - For regency-level associations
- **[WithDistrict Trait](/en/api/concerns/with-district)** - For district-level associations
- **[WithVillage Trait](/en/api/concerns/with-village)** - For village-level associations
- **[Geographic Queries Example](/en/examples/geographic-queries)** - Advanced geographic queries
