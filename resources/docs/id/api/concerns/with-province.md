# Trait WithProvince

Trait `WithProvince` menambahkan relasi `belongsTo` ke model Province, memungkinkan model Anda untuk berelasi dengan provinsi tertentu (tingkat administratif tertinggi di Indonesia).

## Namespace

```php
Creasi\Nusa\Models\Concerns\WithProvince
```

## Penggunaan

### Implementasi Dasar

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Creasi\Nusa\Models\Concerns\WithProvince;

class KantorWilayah extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'name',
        'deskripsi',
        'province_code'
    ];
}
```

### Migrasi Database

```php
Schema::create('kantor_wilayah', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('deskripsi')->nullable();
    $table->string('province_code');
    $table->timestamps();
    
    // Opsional: Tambahkan constraint foreign key
    $table->foreign('province_code')
          ->references('code')
          ->on('provinces')
          ->onDelete('cascade');
});
```

## Fitur

### Fillable Otomatis

Trait ini secara otomatis menambahkan *foreign key* provinsi ke dalam *array* `$fillable` model:

```php
// Ditambahkan ke fillable secara otomatis
protected $fillable = ['province_code']; // atau nama kunci kustom
```

### Relasi Provinsi

Mengakses provinsi yang berelasi:

```php
$kantor = KantorWilayah::find(1);
$provinsi = $kantor->province;

echo "Kantor berlokasi di: {$provinsi->name}";
echo "Koordinat provinsi: {$provinsi->latitude}, {$provinsi->longitude}";
```

### Eager Loading

```php
$kantor = KantorWilayah::with('province')->get();

foreach ($kantor as $k) {
    echo "{$k->name} - {$k->province->name}";
}
```

## Kustomisasi

### *Foreign Key* Kustom

Anda dapat mengkustomisasi nama kolom *foreign key*:

```php
class KantorWilayah extends Model
{
    use WithProvince;
    
    protected $provinceKey = 'province_code_kantor';
    
    protected $fillable = [
        'name',
        'deskripsi',
        'province_code_kantor'
    ];
}
```

## Contoh Penggunaan Umum

### 1. Manajemen Bisnis Regional

```php
class Cabang extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'name',
        'address',
        'telepon',
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

// Penggunaan
$cabangJawa = Cabang::inJava()->get();
$cabangLuarJawa = Cabang::outsideJava()->get();
```

### 2. Statistik Regional

```php
class LaporanPenjualan extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'bulan',
        'tahun',
        'total_penjualan',
        'province_code'
    ];
    
    protected $casts = [
        'total_penjualan' => 'decimal:2'
    ];
    
    public static function getProvinceRanking($tahun)
    {
        return static::with('province')
            ->where('tahun', $tahun)
            ->groupBy('province_code')
            ->selectRaw('province_code, SUM(total_penjualan) as total')
            ->orderByDesc('total')
            ->get();
    }
}

// Penggunaan
$peringkat = LaporanPenjualan::getProvinceRanking(2024);
foreach ($peringkat as $laporan) {
    echo "{$laporan->province->name}: Rp " . number_format($laporan->total);
}
```

### 3. Manajemen Acara

```php
class Acara extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'judul',
        'deskripsi',
        'tanggal_acara',
        'province_code'
    ];
    
    protected $casts = [
        'tanggal_acara' => 'datetime'
    ];
    
    public function getRegionalEventsAttribute()
    {
        return static::where('province_code', $this->province_code)
            ->where('id', '!=', $this->id)
            ->where('tanggal_acara', '>=', now())
            ->orderBy('tanggal_acara')
            ->limit(5)
            ->get();
    }
}
```

### 4. Zona Pengiriman

```php
class ZonaPengiriman extends Model
{
    use WithProvince;
    
    protected $fillable = [
        'name',
        'biaya_dasar',
        'biaya_per_kg',
        'province_code'
    ];
    
    protected $casts = [
        'biaya_dasar' => 'decimal:2',
        'biaya_per_kg' => 'decimal:2'
    ];
    
    public function calculateShippingCost($berat)
    {
        return $this->biaya_dasar + ($this->biaya_per_kg * $berat);
    }
    
    public static function findByProvince($provinceCode)
    {
        return static::where('province_code', $provinceCode)->first();
    }
}

// Penggunaan
$zona = ZonaPengiriman::findByProvince('33'); // Jawa Tengah
$biaya = $zona->calculateShippingCost(2.5); // 2.5 kg
```

## Kueri Lanjutan

### Pengelompokan Geografis

```php
// Kelompokkan berdasarkan wilayah pulau
$cabang = Cabang::with('province')
    ->get()
    ->groupBy(function ($branch) {
        $code = $branch->province->code;
        
        if (in_array($code, ['31', '32', '33', '34', '35', '36'])) {
            return 'Jawa';
        } elseif (in_array($code, ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21'])) {
            return 'Sumatra';
        } elseif (in_array($code, ['61', '62', '63', '64', '65'])) {
            return 'Kalimantan';
        } elseif (in_array($code, ['71', '72', '73', '74', '75', '76'])) {
            return 'Sulawesi';
        } elseif (in_array($code, ['51', '52', '53'])) {
            return 'Bali & Nusa Tenggara';
        } elseif (in_array($code, ['81', '82', '91', '92', '93', '94', '95', '96', '97'])) {
            return 'Indonesia Timur';
        }
        
        return 'Lainnya';
    });
```

### Analisis Statistik

```php
class AnalitikProvinsi
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
                    'provinsi' => $item->province->name,
                    'jumlah' => $item->customer_count,
                    'persentase' => round(($item->customer_count / Customer::count()) * 100, 2)
                ];
            });
    }
}
```

## Validasi

### Validasi *Form Request*

```php
class RegionalOfficeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'province_code' => 'required|exists:nusa.provinces,code'
        ];
    }
    
    public function messages()
    {
        return [
            'province_code.required' => 'Silakan pilih provinsi.',
            'province_code.exists' => 'Provinsi yang dipilih tidak valid.'
        ];
    }
}
```

## Tips Kinerja

### 1. *Eager Loading*

```php
// Baik
$kantor = KantorWilayah::with('province')->get();

// Buruk - kueri N+1
$kantor = KantorWilayah::all();
foreach ($kantor as $k) {
    echo $k->province->name; // Kueri N+1
}
```

### 2. Memilih Kolom Tertentu

```php
$kantor = KantorWilayah::with(['province:code,name'])->get();
```

### 3. *Caching* Data Provinsi

```php
class KantorWilayah extends Model
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

## Dokumentasi Terkait

- **[Model Province](/id/api/models/province)** - Dokumentasi lengkap model Province
- **[Trait WithRegency](/id/api/concerns/with-regency)** - Untuk asosiasi tingkat kabupaten/kota
- **[Trait WithDistrict](/id/api/concerns/with-district)** - Untuk asosiasi tingkat kecamatan
- **[Trait WithVillage](/id/api/concerns/with-village)** - Untuk asosiasi tingkat desa/kelurahan
- **[Contoh Kueri Geografis](/id/examples/geographic-queries)** - Kueri geografis lanjutan