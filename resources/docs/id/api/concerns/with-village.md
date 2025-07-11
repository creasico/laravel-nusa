# Trait WithVillage

Trait `WithVillage` menambahkan relasi `belongsTo` ke model Village, memungkinkan model Anda berelasi dengan desa/kelurahan tertentu (tingkat administratif paling detail di Indonesia).

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithVillage
```

## Penggunaan

### Implementasi Dasar

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithVillage;

class Pelanggan extends Model
{
    use WithVillage;
    
    protected $fillable = [
        'name',
        'email',
        'village_code'
    ];
}
```

### Migrasi Database

```php
Schema::create('pelanggan', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('village_code')->nullable();
    $table->timestamps();
    
    // Opsional: Tambahkan constraint foreign key
    $table->foreign('village_code')
          ->references('code')
          ->on('villages')
          ->onDelete('set null');
});
```

## Fitur

### Fillable Otomatis

Trait ini secara otomatis menambahkan *foreign key* desa ke dalam *array* `$fillable` model:

```php
// Ditambahkan ke fillable secara otomatis
protected $fillable = ['village_code']; // atau nama kunci kustom
```

### Relasi Desa

Mengakses desa yang berelasi:

```php
$pelanggan = Pelanggan::find(1);
$desa = $pelanggan->village;

echo "Pelanggan tinggal di: {$desa->name}";
echo "Kode pos: {$desa->postal_code}";
```

### Eager Loading

```php
$pelanggan = Pelanggan::with('village')->get();

foreach ($pelanggan as $plgn) {
    echo "{$plgn->name} - {$plgn->village->name}";
}
```

### Relasi Bertingkat

Mengakses tingkat administratif di atasnya melalui desa:

```php
$pelanggan = Pelanggan::with('village.district.regency.province')->first();

echo "Alamat lengkap: ";
echo "{$pelanggan->village->name}, ";
echo "{$pelanggan->village->district->name}, ";
echo "{$pelanggan->village->regency->name}, ";
echo "{$pelanggan->village->province->name}";
```

## Kustomisasi

### *Foreign Key* Kustom

Anda dapat mengkustomisasi nama kolom *foreign key*:

```php
class Pelanggan extends Model
{
    use WithVillage;
    
    protected $villageKey = 'village_code_pelanggan';
    
    protected $fillable = [
        'name',
        'email',
        'village_code_pelanggan'
    ];
}
```

### Migrasi dengan Kunci Kustom

```php
Schema::create('pelanggan', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('village_code_pelanggan')->nullable();
    $table->timestamps();
    
    $table->foreign('village_code_pelanggan')
          ->references('code')
          ->on('villages')
          ->onDelete('set null');
});
```

## Contoh Penggunaan Umum

### 1. Manajemen Pelanggan

```php
class Pelanggan extends Model
{
    use WithVillage;
    
    public function getFullAddressAttribute()
    {
        if (!$this->village) return null;
        
        return collect([
            $this->village->name,
            $this->village->district->name,
            $this->village->regency->name,
            $this->village->province->name
        ])->implode(', ');
    }
}

// Penggunaan
$pelanggan = Pelanggan::with('village.district.regency.province')->first();
echo $pelanggan->full_address;
```

### 2. Manajemen Pengiriman

```php
class AlamatPengiriman extends Model
{
    use WithVillage;
    
    protected $fillable = [
        'nama_penerima',
        'telepon',
        'baris_alamat',
        'village_code'
    ];
    
    public function getShippingCostAttribute()
    {
        // Hitung biaya pengiriman berdasarkan lokasi desa
        $provinsi = $this->village->province;
        
        return match($provinsi->code) {
            '31', '32', '33', '34', '35', '36' => 15000, // Jawa
            '51', '52' => 20000, // Bali & Nusa Tenggara
            default => 25000 // Pulau lain
        };
    }
}
```

### 3. Pendaftaran Acara

```php
class PendaftaranAcara extends Model
{
    use WithVillage;
    
    protected $fillable = [
        'nama_peserta',
        'email',
        'village_code'
    ];
    
    public function scopeFromProvince($query, $provinceCode)
    {
        return $query->whereHas('village.province', function ($q) use ($provinceCode) {
            $q->where('code', $provinceCode);
        });
    }
    
    public function scopeFromRegency($query, $regencyCode)
    {
        return $query->whereHas('village.regency', function ($q) use ($regencyCode) {
            $q->where('code', $regencyCode);
        });
    }
}

// Penggunaan
$pesertaJakarta = PendaftaranAcara::fromProvince('31')->get();
$pesertaSemarang = PendaftaranAcara::fromRegency('33.74')->get();
```

## Validasi

### Validasi *Form Request*

```php
class CustomerRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers',
            'village_code' => 'required|exists:nusa.villages,code'
        ];
    }
    
    public function messages()
    {
        return [
            'village_code.required' => 'Silakan pilih desa.',
            'village_code.exists' => 'Desa yang dipilih tidak valid.'
        ];
    }
}
```

### Validasi Model

```php
class Pelanggan extends Model
{
    use WithVillage;
    
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($pelanggan) {
            if ($pelanggan->village_code && !Village::where('code', $pelanggan->village_code)->exists()) {
                throw new \InvalidArgumentException('Kode desa tidak valid');
            }
        });
    }
}
```

## Tips Kinerja

### 1. *Eager Loading*

Selalu lakukan *eager load* pada relasi desa untuk menghindari kueri N+1:

```php
// Baik
$pelanggan = Pelanggan::with('village')->get();

// Buruk - menyebabkan kueri N+1
$pelanggan = Pelanggan::all();
foreach ($pelanggan as $plgn) {
    echo $plgn->village->name; // Kueri N+1
}
```

### 2. Memuat Kolom Tertentu

Muat hanya kolom yang Anda butuhkan:

```php
$pelanggan = Pelanggan::with(['village:code,name,postal_code'])->get();
```

### 3. Pengindeksan

Tambahkan indeks database untuk kinerja kueri yang lebih baik:

```php
Schema::table('pelanggan', function (Blueprint $table) {
    $table->index('village_code');
});
```

## Dokumentasi Terkait

- **[Model Village](/id/api/models/village)** - Dokumentasi lengkap model Village
- **[Trait WithDistrict](/id/api/concerns/with-district)** - Untuk asosiasi tingkat kecamatan
- **[Trait WithRegency](/id/api/concerns/with-regency)** - Untuk asosiasi tingkat kabupaten/kota
- **[Trait WithProvince](/id/api/concerns/with-province)** - Untuk asosiasi tingkat provinsi
- **[Contoh Formulir Alamat](/id/examples/address-forms)** - Membangun formulir alamat bertingkat