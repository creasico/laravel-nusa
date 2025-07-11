# Kustomisasi

Tingkatkan aplikasi Laravel Anda dengan fitur berbasis lokasi menggunakan *trait* model fleksibel Laravel Nusa. Alat kustomisasi ini membantu Anda mengintegrasikan data administratif Indonesia ke dalam model dan logika bisnis aplikasi Anda.

## Manfaat *Trait* Laravel Nusa

### 🎯 **Solusi Siap Pakai**
*Trait* yang sudah dibuat sebelumnya yang menangani persyaratan umum berbasis lokasi, menghemat waktu pengembangan dan memastikan konsistensi.

### 🚀 **Integrasi Cepat**
Tambahkan fungsionalitas lokasi ke model Anda yang sudah ada dengan perubahan kode minimal. *Trait* menangani kompleksitas relasi administratif.

### 🔧 **Pendekatan Fleksibel**
Pilih hanya *trait* yang Anda butuhkan. Gabungkan *trait* yang berbeda untuk menciptakan solusi yang tepat untuk persyaratan spesifik Anda.

## Kasus Penggunaan Umum

### 🏪 **Aplikasi E-Commerce**

**Tantangan**: Mengelola alamat pelanggan, zona pengiriman, dan lokasi toko di seluruh struktur administratif Indonesia yang kompleks.

**Solusi**: Terapkan fitur berbasis lokasi untuk pengalaman pelanggan yang lebih baik:

```php
// Pelanggan dengan beberapa alamat pengiriman
class Customer extends Model
{
    use WithAddresses;
    
    public function getPreferredShippingZone()
    {
        return $this->addresses()
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first()?->getShippingZone();
    }
}

// Pencari toko dengan perhitungan jarak
class Store extends Model
{
    use WithCoordinate, WithAddress;
    
    public function findNearbyStores($userLat, $userLng, $radiusKm = 10)
    {
        return static::nearby($userLat, $userLng, $radiusKm)
            ->with('address.village.regency.province')
            ->get();
    }
}
```

**Manfaat**:
- Perhitungan biaya pengiriman yang lebih akurat
- Pengalaman pelanggan yang lebih baik dengan fitur berbasis lokasi
- Fungsionalitas pencari toko dan pemetaan yang efisien
- Dukungan untuk operasi bisnis regional

[→ Lihat Implementasi Lengkap](/id/api/concerns/with-addresses)

### 🏢 **Manajemen Bisnis Multi-Lokasi**

**Tantangan**: Mengelola struktur perusahaan dengan kantor pusat, cabang, dan kantor regional di beberapa provinsi.

**Solusi**: Mengatur operasi bisnis multi-lokasi:

```php
// Perusahaan dengan beberapa lokasi kantor
class Company extends Model
{
    use WithAddresses;
    
    public function getRegionalCoverage()
    {
        return $this->addresses()
            ->with('province')
            ->get()
            ->groupBy('province.name')
            ->map(function ($addresses, $province) {
                return [
                    'province' => $province,
                    'locations' => $addresses->count(),
                    'types' => $addresses->pluck('type')->unique()
                ];
            });
    }
}

// Manajemen wilayah penjualan regional
class SalesTerritory extends Model
{
    use WithRegency;
    
    public function calculateTerritoryMetrics()
    {
        return [
            'coverage_area' => $this->regency->name,
            'population_estimate' => $this->regency->villages->count() * 1000,
            'market_potential' => $this->calculateMarketSize()
        ];
    }
}
```

**Manfaat**:
- Manajemen data lokasi terpusat
- Analisis dan pelaporan regional
- Perencanaan dan organisasi wilayah
- Pelaporan berbasis wilayah administratif

[→ Jelajahi Solusi Perusahaan](/id/api/concerns/with-address)

### 🚚 **Logistik & Pengiriman**

**Tantangan**: Mengoptimalkan rute pengiriman, mengelola area layanan, dan menghitung biaya pengiriman di seluruh geografi Indonesia yang beragam.

**Solusi**: Membangun sistem logistik cerdas:

```php
// Manajemen zona pengiriman
class DeliveryZone extends Model
{
    use WithVillages;
    
    public function calculateOptimalRoutes()
    {
        return $this->villages()
            ->with('coordinates')
            ->get()
            ->groupBy('district_code')
            ->map(function ($villages) {
                return $this->optimizeRoute($villages);
            });
    }
}

// Penyedia logistik dengan cakupan layanan
class LogisticsProvider extends Model
{
    use WithAddresses;
    
    public function canServeLocation($villageCode)
    {
        $village = Village::find($villageCode);
        
        return $this->addresses()
            ->where('province_code', $village->province_code)
            ->where('type', 'warehouse')
            ->exists();
    }
}
```

**Manfaat**:
- Perencanaan dan optimasi rute yang lebih baik
- Pemetaan cakupan layanan yang jelas
- Kemampuan penetapan harga berbasis lokasi
- Perencanaan pengiriman yang lebih baik

[→ Kuasai Solusi Logistik](/id/api/concerns/with-villages)

### 🏥 **Layanan Kesehatan & Publik**

**Tantangan**: Mengelola fasilitas kesehatan, cakupan layanan, dan demografi pasien di seluruh wilayah administratif.

**Solusi**: Meningkatkan penyediaan layanan publik:

```php
// Manajemen fasilitas kesehatan
class HealthFacility extends Model
{
    use WithDistrict, WithCoordinate;
    
    public function getServiceCoverage()
    {
        return [
            'primary_district' => $this->district->name,
            'coverage_radius' => $this->service_radius_km,
            'estimated_population' => $this->calculateCoveredPopulation(),
            'nearby_facilities' => $this->findNearbyFacilities()
        ];
    }
}

// Manajemen pasien dengan pelacakan lokasi
class Patient extends Model
{
    use WithVillage;
    
    public function getNearestHealthFacility()
    {
        return HealthFacility::whereHas('district', function ($query) {
            $query->where('regency_code', $this->village->regency_code);
        })->first();
    }
}
```

**Manfaat**:
- Manajemen fasilitas kesehatan yang lebih baik
- Perencanaan sumber daya yang lebih baik
- Pelacakan dan analisis lokasi pasien
- Optimasi cakupan layanan

[→ Panduan Solusi Kesehatan](/id/api/concerns/with-district)

### 🏛️ **Pemerintahan & Administrasi**

**Tantangan**: Mengelola layanan warga, batas administratif, dan tata kelola regional di seluruh hierarki administratif Indonesia.

**Solusi**: Memodernisasi layanan pemerintah:

```php
// Pusat layanan pemerintah
class ServiceCenter extends Model
{
    use WithRegency, WithDistricts;
    
    public function getJurisdictionInfo()
    {
        return [
            'regency' => $this->regency->name,
            'districts_served' => $this->districts->count(),
            'total_villages' => $this->districts->sum(function ($district) {
                return $district->villages->count();
            }),
            'estimated_citizens' => $this->calculateCitizenCount()
        ];
    }
}

// Pendaftaran warga dengan verifikasi lokasi
class Citizen extends Model
{
    use WithVillage;
    
    public function verifyResidency()
    {
        return $this->village && 
               $this->village->district &&
               $this->village->regency &&
               $this->village->province;
    }
}
```

**Manfaat**:
- Manajemen layanan warga yang terorganisir
- Data demografi dan lokasi yang akurat
- Perencanaan dan distribusi sumber daya yang lebih baik
- Peningkatan penyediaan layanan pemerintah

[→ Solusi Pemerintah](/id/api/concerns/with-regency)

## Alat Kustomisasi yang Tersedia

### 🔗 **Trait Relasi**

Hubungkan model Anda ke hierarki administratif Indonesia:

| Trait | Kasus Penggunaan | Nilai Bisnis |
|-------|----------|----------------|
| **WithProvince** | Kantor regional, wilayah penjualan | Analisis dan manajemen tingkat provinsi |
| **WithRegency** | Pusat layanan, hub distribusi | Operasi tingkat kota/kabupaten |
| **WithDistrict** | Fasilitas lokal, layanan komunitas | Pengiriman layanan tingkat kecamatan |
| **WithVillage** | Alamat pelanggan, lokasi presisi | Presisi dan penargetan tingkat desa/kelurahan |

[→ Jelajahi Semua Trait Relasi](/id/api/concerns/)

### 📍 **Manajemen Alamat**

Tangani persyaratan alamat yang kompleks:

| Trait | Terbaik Untuk | Fitur Utama |
|-------|----------|--------------|
| **WithAddress** | Profil pengguna, lokasi tunggal | Satu alamat per model, hierarki lengkap |
| **WithAddresses** | Bisnis multi-lokasi | Beberapa alamat, kategorisasi tipe |

[→ Kuasai Manajemen Alamat](/id/guide/addresses)

### 🌍 **Fitur Geografis**

Tambahkan intelijen lokasi:

| Trait | Kemampuan | Aplikasi Bisnis |
|-------|--------------|----------------------|
| **WithCoordinate** | Koordinat GPS, perhitungan jarak | Pencari toko, optimasi pengiriman |
| **WithVillages** | Beberapa desa/kelurahan, kode pos | Cakupan layanan, manajemen wilayah |
| **WithDistricts** | Beberapa kecamatan | Administrasi regional, area layanan |

[→ Solusi Geografis](/id/api/concerns/with-coordinate)

## Pola Implementasi

### 🎯 **Pola Mulai Cepat**

Untuk asosiasi lokasi sederhana:

```php
class YourModel extends Model
{
    use WithVillage; // Lokasi paling spesifik
    
    protected $fillable = ['name', 'village_code'];
}
```

### 🏢 **Pola Perusahaan**

Untuk persyaratan bisnis yang kompleks:

```php
class BusinessModel extends Model
{
    use WithAddresses, WithCoordinate;
    
    // Beberapa lokasi dengan koordinat GPS
    // Sempurna untuk bisnis multi-cabang
}
```

### 🚀 **Pola Berfitur Lengkap**

Untuk solusi lokasi yang komprehensif:

```php
class AdvancedModel extends Model
{
    use WithProvince, WithAddresses, WithCoordinate;
    
    // Asosiasi provinsi + beberapa alamat + GPS
    // Ideal untuk aplikasi perusahaan
}
```

## Memulai

### 1. **Pilih *Trait* Anda**

Pilih *trait* berdasarkan kebutuhan bisnis Anda:
- **Lokasi tunggal**: Gunakan `WithVillage` atau `WithAddress`
- **Beberapa lokasi**: Gunakan `WithAddresses`
- **Fitur geografis**: Tambahkan `WithCoordinate`
- **Manajemen regional**: Gunakan `WithProvince` atau `WithRegency`

### 2. **Implementasikan di Model Anda**

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class Customer extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
}
```

### 3. **Perbarui Database Anda**

```php
Schema::table('customers', function (Blueprint $table) {
    $table->string('village_code')->nullable();
    $table->foreign('village_code')->references('code')->on('villages');
});
```

### 4. **Mulai Membangun**

```php
$customer = Customer::with('village.regency.province')->first();
echo "Pelanggan dari: {$customer->village->name}, {$customer->village->regency->name}";
```

## Contoh Implementasi Umum

### 📈 **Aplikasi E-Commerce**
Menggunakan *trait* untuk membangun manajemen alamat, zona pengiriman, dan fitur lokasi pelanggan untuk toko *online*.

### 🏥 **Sistem Kesehatan**
Mengimplementasikan manajemen fasilitas, demografi pasien, dan cakupan layanan menggunakan data wilayah administratif.

### 🚚 **Aplikasi Logistik**
Membangun optimasi rute, manajemen area layanan, dan sistem perencanaan pengiriman.

## Langkah Selanjutnya

Siap menambahkan fitur lokasi ke aplikasi Anda?

1. **[Jelajahi Semua Trait](/id/api/concerns/)** - Jelajahi dokumentasi *trait* yang komprehensif
2. **[Contoh Implementasi](/id/examples/custom-models)** - Lihat pola penggunaan praktis
3. **[Manajemen Alamat](/id/guide/addresses)** - Pelajari tentang fungsionalitas alamat
4. **[Fitur Geografis](/id/examples/geographic-queries)** - Temukan kemampuan berbasis lokasi

---

*Tingkatkan aplikasi Laravel Anda dengan data administratif Indonesia.*