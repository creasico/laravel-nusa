# Trait WithDistrict

Trait `WithDistrict` menambahkan relasi `belongsTo` ke model District, memungkinkan model Anda berelasi dengan kecamatan tertentu.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithDistrict
```

## Penggunaan

### Implementasi Dasar

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithDistrict;

class PusatKesehatan extends Model
{
    use WithDistrict;
    
    protected $fillable = [
        'name',
        'address',
        'district_code'
    ];
}
```

### Migrasi Database

```php
Schema::create('pusat_kesehatan', function (Blueprint $table) {
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

## Fitur

- Secara otomatis menambahkan `district_code` ke `fillable`
- Menyediakan metode relasi `district()`
- Mendukung *foreign key* kustom melalui properti `$districtKey`

### Penggunaan Dasar

```php
$center = PusatKesehatan::with('district.regency.province')->first();

echo "Pusat: {$center->name}";
echo "Kecamatan: {$center->district->name}";
echo "Kabupaten/Kota: {$center->district->regency->name}";
echo "Provinsi: {$center->district->regency->province->name}";
```

## Contoh Penggunaan Umum

### Fasilitas Layanan Publik

```php
class FasilitasPublik extends Model
{
    use WithDistrict;
    
    protected $fillable = [
        'name',
        'tipe_fasilitas',
        'district_code'
    ];
    
    public function scopeByType($query, $type)
    {
        return $query->where('tipe_fasilitas', $type);
    }
    
    public function getLocationDescriptionAttribute()
    {
        return "Kecamatan {$this->district->name}, {$this->district->regency->name}";
    }
}

// Penggunaan
$sekolah = FasilitasPublik::byType('sekolah')
    ->with('district.regency')
    ->get();
```

### Layanan Pemerintah Daerah

```php
class LayananPemerintah extends Model
{
    use WithDistrict;
    
    protected $fillable = [
        'nama_layanan',
        'deskripsi',
        'district_code',
        'jam_operasional'
    ];
    
    public function scopeInRegency($query, $regencyCode)
    {
        return $query->whereHas('district', function ($q) use ($regencyCode) {
            $q->where('regency_code', $regencyCode);
        });
    }
}
```

## Kustomisasi

### *Foreign Key* Kustom

```php
class PusatKesehatan extends Model
{
    use WithDistrict;
    
    protected $districtKey = 'district_code_layanan';
    
    protected $fillable = [
        'name',
        'district_code_layanan'
    ];
}
```

## Dokumentasi Terkait

- **[Model District](/id/api/models/district)** - Dokumentasi lengkap model District
- **[Trait WithProvince](/id/api/concerns/with-province)** - Untuk asosiasi tingkat provinsi
- **[Trait WithRegency](/id/api/concerns/with-regency)** - Untuk asosiasi tingkat kabupaten/kota
- **[Trait WithVillage](/id/api/concerns/with-village)** - Untuk asosiasi tingkat desa/kelurahan