# Model & Relasi

**Bangun aplikasi yang sadar lokasi** dengan model Eloquent Laravel Nusa yang komprehensif. Model-model ini menyediakan fondasi untuk mengintegrasikan struktur administratif Indonesia ke dalam logika bisnis Anda, dari analitik tingkat nasional hingga operasi spesifik desa.

## Mengapa Menggunakan Model Laravel Nusa?

### 🎯 **Cakupan Administratif Lengkap**
Bekerja dengan setiap tingkat hierarki administratif Indonesia - dari 34 provinsi hingga 83.467 desa. Cakupan komprehensif ini memastikan aplikasi Anda dapat menangani kebutuhan berbasis lokasi apa pun.

### ⚡ **Relasi Siap Pakai**
Relasi Eloquent yang sudah dibangun menangani kompleksitas struktur hierarkis Indonesia, memungkinkan Anda fokus pada logika bisnis daripada manajemen data.

### 🔄 **Sumber Data Resmi**
Model bekerja dengan data yang disinkronisasi dari sumber resmi pemerintah, memastikan aplikasi Anda memiliki informasi administratif yang akurat dan terkini.

## Memahami Hierarki Administratif

### 📊 **Struktur Empat Tingkat**
```
🇮🇩 Indonesia
├── 34 Provinsi → Operasi regional strategis
├── 514 Kabupaten/Kota → Layanan tingkat kota dan kabupaten
├── 7.266 Kecamatan → Layanan komunitas dan lokal
└── 83.467 Kelurahan/Desa → Penargetan lokasi yang presisi
```

::: tip Detail Teknis
Untuk informasi detail tentang struktur database, relasi, dan implementasi teknis, lihat [Models Overview](/id/api/models/overview) di Referensi API.
:::

### 🏢 **Aplikasi Bisnis**

**Platform E-Commerce**: Zona pengiriman, optimasi pengantaran, dan segmentasi pelanggan
**Sistem Kesehatan**: Manajemen fasilitas, demografi pasien, dan cakupan layanan
**Layanan Keuangan**: Penilaian risiko, perencanaan cabang, dan kepatuhan regulasi
**Layanan Pemerintah**: Manajemen warga, alokasi sumber daya, dan pelaporan administratif

## Penggunaan Model Dasar

### Bekerja dengan Provinsi

```php
use Creasi\Nusa\Models\Province;

// Dapatkan semua provinsi
$provinces = Province::all();

// Cari provinsi tertentu
$jateng = Province::find('33'); // Jawa Tengah

// Pencarian berdasarkan nama
$javaProvinces = Province::search('jawa')->get();

// Dengan relasi
$province = Province::with(['regencies', 'districts', 'villages'])->find('33');
```

### Bekerja dengan Kabupaten/Kota

```php
use Creasi\Nusa\Models\Regency;

// Dapatkan kabupaten/kota dalam provinsi
$regencies = Regency::where('province_code', '33')->get();

// Pencarian kabupaten/kota
$semarang = Regency::search('semarang')->first();

// Akses provinsi induk
echo $semarang->province->name; // "Jawa Tengah"
```

### Bekerja dengan Kecamatan

```php
use Creasi\Nusa\Models\District;

// Dapatkan kecamatan dalam kabupaten/kota
$districts = District::where('regency_code', '33.74')->get();

// Navigasi hierarki
$district = District::with(['regency.province'])->first();
echo $district->regency->province->name;
```

### Bekerja dengan Kelurahan/Desa

```php
use Creasi\Nusa\Models\Village;

// Cari berdasarkan kode pos
$villages = Village::where('postal_code', '50132')->get();

// Hierarki lengkap
$village = Village::with(['district.regency.province'])->first();

// Alamat lengkap
echo $village->name; // Nama desa
echo $village->district->name; // Nama kecamatan
echo $village->regency->name; // Nama kabupaten/kota
echo $village->province->name; // Nama provinsi
```

## Relasi dan Navigasi

### Relasi Hierarkis

Laravel Nusa menyediakan relasi lengkap untuk menavigasi hierarki administratif:

```php
$province = Province::find('33');

// Navigasi ke bawah
$regencies = $province->regencies; // Semua kabupaten/kota
$districts = $province->districts;  // Semua kecamatan
$villages = $province->villages;    // Semua desa

$regency = $province->regencies->first();

// Navigasi ke atas
$province = $regency->province;

// Navigasi lintas tingkat
$districts = $regency->districts;
$villages = $regency->villages;
```

### Eager Loading untuk Performa

```php
// Loading efisien dengan relasi
$villages = Village::with(['district.regency.province'])
    ->where('postal_code', '50132')
    ->get();

// Hitung relasi tanpa loading
$provinces = Province::withCount(['regencies', 'districts', 'villages'])->get();

foreach ($provinces as $province) {
    echo "{$province->name}: {$province->villages_count} desa";
}
```

## Kemampuan Pencarian

### Pencarian Fleksibel

Semua model menyertakan scope `search()` untuk pencarian fleksibel:

```php
// Pencarian berdasarkan nama (case-insensitive)
$provinces = Province::search('jawa')->get();
// Hasil: Jawa Barat, Jawa Tengah, Jawa Timur

// Pencarian berdasarkan kode
$province = Province::search('33')->first();

// Pencocokan parsial
$regencies = Regency::search('kota')->get();
// Hasil: Semua kota (bukan kabupaten)
```

### Filter Lanjutan

```php
// Filter berdasarkan relasi
$provinces = Province::whereHas('regencies', function ($query) {
    $query->where('name', 'like', '%Kota%');
})->get();

// Filter berdasarkan jumlah
$regencies = Regency::has('districts', '>=', 10)->get();

// Multiple kondisi
$villages = Village::where('postal_code', '50132')
    ->whereHas('district', function ($query) {
        $query->where('name', 'like', '%Tengah%');
    })
    ->get();
```

## Kasus Penggunaan Bisnis

### Implementasi E-Commerce

```php
// Manajemen zona pengiriman
class ShippingZone
{
    public function getCoverageByProvince($provinceCode)
    {
        $province = Province::with(['regencies.districts.villages'])
            ->find($provinceCode);

        return [
            'province' => $province->name,
            'total_areas' => $province->villages->count(),
            'shipping_cost' => $this->calculateShippingCost($province),
            'delivery_time' => $this->estimateDeliveryTime($province)
        ];
    }
}
```

### Sistem Kesehatan

```php
// Analisis cakupan fasilitas
class HealthcareCoverage
{
    public function analyzeCoverage($facilityLocations)
    {
        $provinces = Province::withCount('villages')->get();

        return $provinces->map(function ($province) use ($facilityLocations) {
            $coverage = $this->calculateCoverage($province, $facilityLocations);

            return [
                'province' => $province->name,
                'total_villages' => $province->villages_count,
                'covered_villages' => $coverage['covered'],
                'coverage_percentage' => $coverage['percentage']
            ];
        });
    }
}
```

### Layanan Pemerintah

```php
// Pelaporan administratif
class AdministrativeReport
{
    public function generateRegionalReport($provinceCode)
    {
        $province = Province::with(['regencies.districts.villages'])
            ->find($provinceCode);

        return [
            'province' => $province->name,
            'administrative_units' => [
                'regencies' => $province->regencies->count(),
                'districts' => $province->districts->count(),
                'villages' => $province->villages->count()
            ],
            'postal_codes' => $province->villages
                ->pluck('postal_code')
                ->unique()
                ->sort()
                ->values()
        ];
    }
}
```

## Optimasi Performa

### Query Efisien

```php
// Gunakan select untuk membatasi field
$provinces = Province::select('code', 'name')->get();

// Pagination untuk dataset besar
$villages = Village::paginate(50);

// Chunk processing untuk operasi bulk
Village::chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Proses village
    }
});
```

### Strategi Caching

```php
// Cache data yang sering diakses
$provinces = Cache::remember('provinces', 3600, function () {
    return Province::select('code', 'name')->get();
});

// Cache dengan tag untuk invalidation
Cache::tags(['locations'])->remember('regencies_33', 3600, function () {
    return Regency::where('province_code', '33')->get();
});
```

## Integrasi dengan Model Anda

### Menggunakan Trait

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class User extends Model
{
    use WithVillage;

    protected $fillable = ['name', 'email', 'village_code'];
}

// Sekarang user memiliki relasi village
$user = User::with('village.district.regency.province')->first();
echo $user->village->province->name;
```

### Relasi Kustom

```php
class Store extends Model
{
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }

    public function serviceArea()
    {
        // Dapatkan semua desa dalam radius 25km
        return Village::selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(latitude))
            )) AS distance
        ", [$this->latitude, $this->longitude, $this->latitude])
        ->having('distance', '<=', 25)
        ->orderBy('distance');
    }
}
```

## Langkah Selanjutnya

### **Pelajari Lebih Lanjut**:

1. **[Models Overview](/id/api/models/overview)** - Detail teknis, struktur database, dan relasi
2. **[Manajemen Alamat](/id/guide/addresses)** - Integrasikan fungsionalitas alamat
3. **[Kustomisasi](/id/guide/customization)** - Kustomisasi model dengan trait yang tersedia
4. **[Contoh](/id/examples/basic-usage)** - Lihat contoh implementasi praktis

---

*Bangun aplikasi yang sadar lokasi dengan data administratif Indonesia yang komprehensif.*
