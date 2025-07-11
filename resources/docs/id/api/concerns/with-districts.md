# Trait WithDistricts

Trait `WithDistricts` menambahkan relasi `hasMany` ke kecamatan, memungkinkan model untuk memiliki banyak kecamatan terkait.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithDistricts
```

## Penggunaan

### Implementasi Dasar

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithDistricts;

class Kabupaten extends Model
{
    use WithDistricts;
    
    protected $fillable = [
        'kode',
        'name',
        'province_code'
    ];
}
```

## Fitur

- Menyediakan relasi `hasMany` `districts()`
- Secara otomatis memuat semua kecamatan yang terkait dengan model

### Penggunaan Dasar

```php
$kabupaten = Regency::with('districts')->first();

echo "Kabupaten: {$kabupaten->name}";
echo "Kecamatan: {$kabupaten->districts->count()}";

foreach ($kabupaten->districts as $kecamatan) {
    echo "- {$kecamatan->name}";
}
```

## Contoh Penggunaan Umum

### Administrasi Regional

```php
class KantorWilayah extends Model
{
    use WithDistricts;
    
    protected $fillable = [
        'name',
        'tipe_kantor',
        'regency_code'
    ];
    
    public function getDistrictCountAttribute()
    {
        return $this->districts()->count();
    }
    
    public function getServiceAreaAttribute()
    {
        return $this->districts->pluck('name')->implode(', ');
    }
    
    public function scopeWithMinimumDistricts($query, $count)
    {
        return $query->has('districts', '>=', $count);
    }
}

// Penggunaan
$kantor = KantorWilayah::withMinimumDistricts(5)->get();
```

### Cakupan Layanan

```php
class PenyediaLayanan extends Model
{
    use WithDistricts;
    
    protected $fillable = [
        'name',
        'tipe_layanan'
    ];
    
    public function addDistrict($districtCode)
    {
        $kecamatan = Regency::find($districtCode);
        
        if ($kecamatan) {
            return $this->districts()->save($kecamatan);
        }
        
        return false;
    }
    
    public function removeDistrict($districtCode)
    {
        return $this->districts()->where('code', $districtCode)->delete();
    }
    
    public function coversDistrict($districtCode)
    {
        return $this->districts()->where('code', $districtCode)->exists();
    }
    
    public function getCoverageStatsAttribute()
    {
        return [
            'kecamatan' => $this->districts->count(),
            'kabupaten' => $this->districts->pluck('regency_code')->unique()->count(),
            'provinsi' => $this->districts->pluck('province_code')->unique()->count()
        ];
    }
}
```

### Daerah Pemilihan

```php
class DaerahPemilihan extends Model
{
    use WithDistricts;
    
    protected $fillable = [
        'name',
        'tahun_pemilu',
        'jumlah_perwakilan'
    ];
    
    public function getVoterEstimateAttribute()
    {
        // Estimasi berdasarkan kecamatan
        return $this->districts->sum(function ($kecamatan) {
            return $kecamatan->villages()->count() * 1000; // Estimasi kasar
        });
    }
    
    public function getGeographicSpreadAttribute()
    {
        $kabupaten = $this->districts->groupBy('regency_code');
        
        return $kabupaten->map(function ($districts, $regencyCode) {
            return [
                'regency_code' => $regencyCode,
                'nama_kabupaten' => $districts->first()->regency->name,
                'district_count' => $districts->count(),
                'kecamatan' => $districts->pluck('name')->toArray()
            ];
        })->values();
    }
}
```

## Tips Kinerja

### 1. Eager Loading

```php
// Baik
$kabupaten = Regency::with('districts')->get();

// Buruk - Kueri N+1
$kabupaten = Regency::all();
foreach ($kabupaten as $kab) {
    echo $kab->districts->count(); // Kueri N+1
}
```

### 2. Menghitung Kecamatan

```php
$kabupaten = Regency::withCount('districts')->get();

foreach ($kabupaten as $kab) {
    echo "{$kab->name}: {$kab->districts_count} kecamatan";
}
```

## Dokumentasi Terkait

- **[Model District](/id/api/models/district)** - Dokumentasi lengkap model District
- **[Trait WithDistrict](/id/api/concerns/with-district)** - Untuk asosiasi kecamatan tunggal
- **[Trait WithVillages](/id/api/concerns/with-villages)** - Untuk relasi desa/kelurahan ganda
- **[Model & Relasi](/id/guide/models)** - Memahami model Laravel Nusa