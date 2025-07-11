# WithProvince

Trait `WithProvince` memungkinkan model Anda memiliki relasi ke satu provinsi, memberikan akses ke data provinsi dan kemampuan untuk mengelompokkan data berdasarkan tingkat provinsi.

The `WithProvince` trait allows your model to have a relationship to a single province, providing access to province data and the ability to group data by province level.

## Overview

The `WithProvince` trait is useful for models that need to be associated with a specific province but don't require more granular location data. This is common for business units, regional offices, or high-level administrative divisions.

### What You Get

- **Province relationship** - Direct access to province data
- **Regional grouping** - Easy grouping and filtering by province
- **Geographic coordinates** - Access to province center coordinates
- **Hierarchical access** - Access to regencies, districts, and villages within the province

## Basic Usage

### Adding the Trait

```php
use Creasi\Nusa\Models\Concerns\WithProvince;

class BusinessUnit extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'name',
        'province_code',
        'description'
    ];
}
```

### Database Requirements

Your model's table must have a `province_code` column:

```php
// Migration
Schema::table('business_units', function (Blueprint $table) {
    $table->string('province_code', 2)->nullable();
    $table->foreign('province_code')->references('code')->on('nusa.provinces');
});
```

### Creating Records

```php
// Create business unit for Central Java
$unit = BusinessUnit::create([
    'name' => 'Central Java Division',
    'province_code' => '33',
    'description' => 'Handles operations in Central Java'
]);

// Access province data
echo $unit->province->name; // "Jawa Tengah"
echo $unit->province->regencies->count(); // Number of regencies in province
```

## Accessing Province Data

### Basic Province Access

```php
$unit = BusinessUnit::with('province')->first();

echo $unit->province->name; // Province name
echo $unit->province->latitude; // Province center latitude
echo $unit->province->longitude; // Province center longitude
```

### Accessing Sub-regions

```php
$unit = BusinessUnit::with(['province.regencies', 'province.districts', 'province.villages'])->first();

// Access all regencies in the province
foreach ($unit->province->regencies as $regency) {
    echo $regency->name;
}

// Get statistics
echo "Regencies: " . $unit->province->regencies->count();
echo "Districts: " . $unit->province->districts->count();
echo "Villages: " . $unit->province->villages->count();
```

### Helper Methods

```php
class BusinessUnit extends Model
{
    use WithProvince;
    
    // Get province display name
    public function getProvinceDisplayNameAttribute()
    {
        return $this->province ? "Provinsi {$this->province->name}" : null;
    }
    
    // Check if unit is in Java
    public function isInJava()
    {
        $javaCodes = ['31', '32', '33', '34', '35', '36'];
        return in_array($this->province_code, $javaCodes);
    }
    
    // Get coverage area statistics
    public function getCoverageStats()
    {
        if (!$this->province) {
            return null;
        }
        
        return [
            'province' => $this->province->name,
            'regencies' => $this->province->regencies->count(),
            'districts' => $this->province->districts->count(),
            'villages' => $this->province->villages->count()
        ];
    }
}
```

## Querying with Province Relationships

### Basic Queries

```php
// Get units with their provinces
$units = BusinessUnit::with('province')->get();

// Get units in specific province
$units = BusinessUnit::where('province_code', '33')->get();

// Get units in Java provinces
$javaUnits = BusinessUnit::whereIn('province_code', ['31', '32', '33', '34', '35', '36'])->get();
```

### Advanced Filtering

```php
// Units in provinces with specific characteristics
$units = BusinessUnit::whereHas('province', function ($query) {
    $query->where('name', 'like', '%Jawa%');
})->get();

// Units in provinces with many regencies
$units = BusinessUnit::whereHas('province', function ($query) {
    $query->has('regencies', '>=', 20);
})->get();
```

### Custom Scopes

```php
class BusinessUnit extends Model
{
    use WithProvince;
    
    // Scope for units in Java
    public function scopeInJava($query)
    {
        return $query->whereIn('province_code', ['31', '32', '33', '34', '35', '36']);
    }
    
    // Scope for units in specific province
    public function scopeInProvince($query, $provinceCode)
    {
        return $query->where('province_code', $provinceCode);
    }
    
    // Scope for units in Sumatra
    public function scopeInSumatra($query)
    {
        $sumatraCodes = ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21'];
        return $query->whereIn('province_code', $sumatraCodes);
    }
}

// Usage
$javaUnits = BusinessUnit::inJava()->get();
$centralJavaUnits = BusinessUnit::inProvince('33')->get();
$sumatraUnits = BusinessUnit::inSumatra()->get();
```

## Regional Analytics

### Province-based Reporting

```php
class ProvinceAnalytics
{
    public function getBusinessDistribution()
    {
        return BusinessUnit::join('nusa.provinces', 'business_units.province_code', '=', 'provinces.code')
            ->groupBy('provinces.code', 'provinces.name')
            ->selectRaw('provinces.code, provinces.name, count(*) as unit_count')
            ->orderBy('unit_count', 'desc')
            ->get();
    }
    
    public function getRegionalPerformance()
    {
        return BusinessUnit::with('province')
            ->get()
            ->groupBy('province.name')
            ->map(function ($units, $provinceName) {
                return [
                    'province' => $provinceName,
                    'units_count' => $units->count(),
                    'total_revenue' => $units->sum('revenue'),
                    'average_performance' => $units->avg('performance_score')
                ];
            });
    }
}
```

### Geographic Analysis

```php
class GeographicAnalyzer
{
    public function getProvincesCoverage()
    {
        $totalProvinces = Province::count();
        $coveredProvinces = BusinessUnit::distinct('province_code')->count();
        
        return [
            'total_provinces' => $totalProvinces,
            'covered_provinces' => $coveredProvinces,
            'coverage_percentage' => ($coveredProvinces / $totalProvinces) * 100,
            'uncovered_provinces' => Province::whereNotIn('code', 
                BusinessUnit::distinct()->pluck('province_code')
            )->pluck('name')
        ];
    }
}
```

## Integration Examples

### Regional Office Management

```php
class RegionalOffice extends Model
{
    use WithProvince;
    
    protected $fillable = ['name', 'province_code', 'manager_name', 'phone'];
    
    // Get all branches in this province
    public function getBranchesInProvince()
    {
        return Branch::whereHas('village', function ($query) {
            $query->where('province_code', $this->province_code);
        })->get();
    }
    
    // Get coverage statistics
    public function getCoverageReport()
    {
        $branches = $this->getBranchesInProvince();
        
        return [
            'office' => $this->name,
            'province' => $this->province->name,
            'branches_count' => $branches->count(),
            'regencies_covered' => $branches->pluck('village.regency_code')->unique()->count(),
            'districts_covered' => $branches->pluck('village.district_code')->unique()->count()
        ];
    }
}
```

### Sales Territory Management

```php
class SalesTerritory extends Model
{
    use WithProvince;
    
    protected $fillable = ['name', 'province_code', 'sales_target'];
    
    // Get customers in this territory
    public function getCustomersInTerritory()
    {
        return Customer::whereHas('village', function ($query) {
            $query->where('province_code', $this->province_code);
        })->get();
    }
    
    // Calculate territory performance
    public function getPerformanceMetrics()
    {
        $customers = $this->getCustomersInTerritory();
        
        return [
            'territory' => $this->name,
            'province' => $this->province->name,
            'customers_count' => $customers->count(),
            'total_sales' => $customers->sum('total_purchases'),
            'sales_target' => $this->sales_target,
            'achievement_percentage' => ($customers->sum('total_purchases') / $this->sales_target) * 100
        ];
    }
}
```

## Validasi

### Validasi Kode Provinsi

```php
// Dalam FormRequest
public function rules()
{
    return [
        'province_code' => [
            'required',
            'string',
            'size:2',
            'exists:nusa.provinces,code'
        ]
    ];
}

// Custom validation rule
Validator::extend('valid_province', function ($attribute, $value, $parameters, $validator) {
    return Province::where('code', $value)->exists();
});
```

### Validasi dalam Model

```php
class BusinessUnit extends Model
{
    use WithProvince;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->province_code && !Province::where('code', $model->province_code)->exists()) {
                throw new InvalidArgumentException('Kode provinsi tidak valid');
            }
        });
    }
}
```

## Tips Performa

### Eager Loading

```php
// ❌ N+1 Problem
$units = BusinessUnit::all();
foreach ($units as $unit) {
    echo $unit->province->name; // Query untuk setiap unit
}

// ✅ Eager Loading
$units = BusinessUnit::with('province')->get();
foreach ($units as $unit) {
    echo $unit->province->name; // Tidak ada query tambahan
}
```

### Caching

```php
// Cache province data
$province = Cache::remember("province.{$this->province_code}", 3600, function () {
    return $this->province;
});

// Cache province statistics
$stats = Cache::remember("province.{$provinceCode}.stats", 1800, function () use ($provinceCode) {
    return BusinessUnit::where('province_code', $provinceCode)->count();
});
```

### Database Indexing

```php
// Migration untuk optimisasi
Schema::table('business_units', function (Blueprint $table) {
    $table->index(['province_code']); // Untuk filtering
    $table->index(['province_code', 'created_at']); // Untuk sorting dengan filter
});
```

## Kustomisasi

### Custom Foreign Key

```php
class BusinessUnit extends Model
{
    use WithProvince;

    // Jika menggunakan nama kolom yang berbeda
    public function province()
    {
        return $this->belongsTo(Province::class, 'prov_code', 'code');
    }
}
```

### Custom Province Model

```php
// Jika menggunakan model Province kustom
class BusinessUnit extends Model
{
    use WithProvince;

    public function province()
    {
        return $this->belongsTo(\App\Models\CustomProvince::class, 'province_code', 'code');
    }
}
```

## Dokumentasi Terkait

- [Province Model](/id/api/models/province) - Model provinsi lengkap
- [WithRegency Trait](/id/api/concerns/with-regency) - Untuk granularitas kabupaten/kota
- [WithAddress Trait](/id/api/concerns/with-address) - Untuk alamat lengkap
- [Geographic Queries](/id/examples/geographic-queries) - Query geografis lanjutan

## Next Steps

- **[WithRegency](/id/api/concerns/with-regency)** - Regency-level relationships
- **[WithVillage](/id/api/concerns/with-village)** - Village-level relationships
- **[Province Model](/id/api/models/province)** - Complete province model documentation
- **[WithAddresses](/id/api/concerns/with-addresses)** - Multiple addresses management
