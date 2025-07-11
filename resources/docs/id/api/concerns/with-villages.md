# Trait WithVillages

Trait `WithVillages` menambahkan relasi `hasMany` ke desa/kelurahan dan menyediakan fungsionalitas kode pos untuk model yang mencakup banyak desa/kelurahan.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithVillages
```

## Penggunaan

### Implementasi Dasar

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithVillages;

class District extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'code',
        'name',
        'regency_code'
    ];
}
```

## Fitur

- Menyediakan relasi `hasMany` `villages()`
- Secara otomatis menambahkan atribut `postal_codes`
- Menyediakan *accessor* `postal_codes` untuk kode pos unik
- Menyertakan relasi `distinctVillagesByPostalCodes()`

### Penggunaan Dasar

```php
$kecamatan = District::with('villages')->first();

echo "Kecamatan: {$kecamatan->name}";
echo "Desa/Kelurahan: {$kecamatan->villages->count()}";
echo "Kode Pos: " . $kecamatan->postal_codes->implode(', ');
```

## Contoh Penggunaan Umum

### Manajemen Administratif

```php
class AdministrativeRegion extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'name',
        'type',
        'parent_code'
    ];
    
    public function getVillageCountAttribute()
    {
        return $this->villages()->count();
    }
    
    public function getUniquePostalCodesAttribute()
    {
        return $this->postal_codes->unique()->sort()->values();
    }
    
    public function getVillagesByPostalCodeAttribute()
    {
        return $this->villages->groupBy('postal_code');
    }
}

// Usage
$region = AdministrativeRegion::first();
echo "Total villages: {$region->village_count}";
echo "Postal codes: " . $region->unique_postal_codes->implode(', ');
```

### Area Cakupan Layanan

```php
class ServiceArea extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'name',
        'service_type'
    ];
    
    public function getCoverageStatsAttribute()
    {
        return [
            'total_villages' => $this->villages->count(),
            'postal_codes' => $this->postal_codes->count(),
            'regencies' => $this->villages->pluck('regency_code')->unique()->count(),
            'provinces' => $this->villages->pluck('province_code')->unique()->count()
        ];
    }
    
    public function scopeWithMinimumVillages($query, $count)
    {
        return $query->has('villages', '>=', $count);
    }
}
```

### Zona Pengiriman

```php
class DeliveryZone extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'name',
        'delivery_cost',
        'estimated_days'
    ];
    
    protected $casts = [
        'delivery_cost' => 'decimal:2'
    ];
    
    public function addVillage($villageCode)
    {
        $village = Village::find($villageCode);
        
        if ($village) {
            return $this->villages()->save($village);
        }
        
        return false;
    }
    
    public function removeVillage($villageCode)
    {
        return $this->villages()->where('code', $villageCode)->delete();
    }
    
    public function canDeliverTo($villageCode)
    {
        return $this->villages()->where('code', $villageCode)->exists();
    }
    
    public function getDeliveryInfoAttribute()
    {
        return [
            'cost' => $this->delivery_cost,
            'estimated_days' => $this->estimated_days,
            'coverage' => $this->villages->count() . ' kelurahan',
            'postal_codes' => $this->postal_codes->count() . ' kode pos'
        ];
    }
}

// Usage
$zone = DeliveryZone::first();
$canDeliver = $zone->canDeliverTo('33.74.01.1001');
$info = $zone->delivery_info;
```

## Penggunaan Lanjutan

### Analisis Kode Pos

```php
class PostalCodeAnalyzer
{
    public static function analyzeRegion($model)
    {
        $villages = $model->villages()->with(['district', 'regency', 'province'])->get();
        
        return [
            'total_villages' => $villages->count(),
            'postal_codes' => $villages->pluck('postal_code')->unique()->count(),
            'districts' => $villages->pluck('district.name')->unique()->count(),
            'regencies' => $villages->pluck('regency.name')->unique()->count(),
            'provinces' => $villages->pluck('province.name')->unique()->count(),
            'postal_code_distribution' => $villages->groupBy('postal_code')
                ->map(function ($group, $postalCode) {
                    return [
                        'postal_code' => $postalCode,
                        'village_count' => $group->count(),
                        'villages' => $group->pluck('name')->toArray()
                    ];
                })->values()
        ];
    }
}
```

### Klastering Geografis

```php
class RegionalCluster extends Model
{
    use WithVillages;
    
    protected $fillable = [
        'name',
        'cluster_type'
    ];
    
    public function addVillagesByPostalCode($postalCode)
    {
        $villages = Village::where('postal_code', $postalCode)->get();
        
        foreach ($villages as $village) {
            $this->villages()->save($village);
        }
        
        return $villages->count();
    }
    
    public function addVillagesByRegency($regencyCode)
    {
        $villages = Village::where('regency_code', $regencyCode)->get();
        
        foreach ($villages as $village) {
            $this->villages()->save($village);
        }
        
        return $villages->count();
    }
    
    public function getGeographicSummaryAttribute()
    {
        $villages = $this->villages;
        
        return [
            'total_villages' => $villages->count(),
            'regencies' => $villages->groupBy('regency_code')->map(function ($group, $code) {
                return [
                    'code' => $code,
                    'name' => $group->first()->regency->name,
                    'village_count' => $group->count()
                ];
            })->values(),
            'postal_codes' => $this->postal_codes->sort()->values()
        ];
    }
}
```

## Tips Kinerja

### 1. Eager Loading

```php
// Good
$districts = District::with(['villages.regency.province'])->get();

// Bad - N+1 queries
$districts = District::all();
foreach ($districts as $district) {
    echo $district->villages->count(); // N+1 query
}
```

### 2. Menghitung Desa/Kelurahan

```php
$districts = District::withCount('villages')->get();

foreach ($districts as $district) {
    echo "{$district->name}: {$district->villages_count} villages";
}
```

### 3. Optimalisasi Kode Pos

```php
// Cache kode pos untuk kinerja yang lebih baik
class District extends Model
{
    use WithVillages;
    
    public function getCachedPostalCodesAttribute()
    {
        return Cache::remember(
            "district_postal_codes_{$this->id}",
            3600,
            fn() => $this->postal_codes
        );
    }
}
```

## Dokumentasi Terkait

- **[Model Village](/id/api/models/village)** - Dokumentasi lengkap model Village
- **[Trait WithVillage](/id/api/concerns/with-village)** - Untuk asosiasi desa/kelurahan tunggal
- **[Trait WithDistricts](/id/api/concerns/with-districts)** - Untuk relasi kecamatan ganda
- **[Model & Relasi](/id/guide/models)** - Memahami model Laravel Nusa