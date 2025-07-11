# Trait WithRegency

Trait `WithRegency` menambahkan relasi `belongsTo` ke model Regency, memungkinkan model Anda untuk berelasi dengan kabupaten/kota tertentu.

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithRegency
```

## Penggunaan

### Implementasi Dasar

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithRegency;

class KantorKecamatan extends Model
{
    use WithRegency;
    
    protected $fillable = [
        'name',
        'address',
        'regency_code'
    ];
}
```

### Migrasi Database

```php
Schema::create('kantor_kecamatan', function (Blueprint $table) {
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

## Fitur

- Secara otomatis menambahkan `regency_code` ke `fillable`
- Menyediakan metode relasi `regency()`
- Mendukung *foreign key* kustom melalui properti `$regencyKey`

### Penggunaan Dasar

```php
$kantor = KantorDistrict::with('regency.province')->first();

echo "Kantor: {$kantor->name}";
echo "Kabupaten/Kota: {$kantor->regency->name}";
echo "Provinsi: {$kantor->regency->province->name}";
```

## Contoh Penggunaan Umum

### Pusat Layanan Regional

```php
class PusatLayanan extends Model
{
    use WithRegency;
    
    protected $fillable = [
        'name',
        'tipe_layanan',
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

// Penggunaan
$pusat = PusatLayanan::inProvince('33')->get(); // Jawa Tengah
```

### Manajemen Wilayah Penjualan

```php
class WilayahPenjualan extends Model
{
    use WithRegency;
    
    protected $fillable = [
        'name',
        'id_perwakilan_penjualan',
        'regency_code',
        'target_pendapatan'
    ];
    
    public function salesRep()
    {
        return $this->belongsTo(User::class, 'id_perwakilan_penjualan');
    }
    
    public function getFullTerritoryNameAttribute()
    {
        return "{$this->name} - {$this->regency->name}";
    }
}
```

## Kustomisasi

### *Foreign Key* Kustom

```php
class KantorKecamatan extends Model
{
    use WithRegency;
    
    protected $regencyKey = 'regency_code_kantor';
    
    protected $fillable = [
        'name',
        'regency_code_kantor'
    ];
}
```

## Dokumentasi Terkait

- **[Model Regency](/id/api/models/regency)** - Dokumentasi lengkap model Regency
- **[Trait WithProvince](/id/api/concerns/with-province)** - Untuk asosiasi tingkat provinsi
- **[Trait WithDistrict](/id/api/concerns/with-district)** - Untuk asosiasi tingkat kecamatan
- **[Trait WithVillage](/id/api/concerns/with-village)** - Untuk asosiasi tingkat desa/kelurahan