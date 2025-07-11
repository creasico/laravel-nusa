# Model & Relasi

**Bangun aplikasi berbasis lokasi** dengan model Eloquent komprehensif Laravel Nusa. Model-model ini menyediakan fondasi untuk mengintegrasikan struktur administratif Indonesia ke dalam logika bisnis Anda, mulai dari analitik tingkat nasional hingga operasi spesifik desa/kelurahan.

## Mengapa Menggunakan Model Laravel Nusa?

### 🎯 **Cakupan Administratif Lengkap**
Bekerja dengan setiap tingkat hierarki administratif Indonesia - dari 38 provinsi hingga 83.762 desa/kelurahan. Cakupan komprehensif ini memastikan aplikasi Anda dapat menangani setiap persyaratan berbasis lokasi.

### ⚡ **Relasi Siap Pakai**
Relasi Eloquent yang sudah dibangun menangani kompleksitas struktur hierarkis Indonesia, memungkinkan Anda untuk fokus pada logika bisnis Anda daripada manajemen data.

### 🔄 **Sumber Data Resmi**
Model-model ini bekerja dengan data yang disinkronkan dari sumber resmi pemerintah, memastikan aplikasi Anda memiliki informasi administratif yang akurat dan terkini.

## Memahami Hierarki Administratif

### 📊 **Struktur Empat Tingkat**
```
🇮🇩 Indonesia
├── 38 Provinsi → Operasi regional strategis
├── 514 Kabupaten/Kota → Layanan tingkat kota dan kabupaten
├── 7.285 Kecamatan → Layanan komunitas dan lokal
└── 83.762 Desa/Kelurahan → Penargetan lokasi yang tepat
```

::: tip Detail Teknis
Untuk informasi rinci tentang struktur database, relasi, dan implementasi teknis, lihat [Ikhtisar Model](/id/api/models/overview) di Referensi API.
:::

### 🏢 **Aplikasi Bisnis**

**Platform E-Commerce**: Zona pengiriman, optimasi pengiriman, dan segmentasi pelanggan
**Sistem Kesehatan**: Manajemen fasilitas, demografi pasien, dan cakupan layanan
**Layanan Keuangan**: Penilaian risiko, perencanaan cabang, dan kepatuhan regulasi
**Layanan Pemerintah**: Manajemen warga, alokasi sumber daya, dan pelaporan administratif

## Fitur Model yang Kuat

### 🔍 **Pencarian Cerdas**
Temukan lokasi apa pun secara instan dengan kemampuan pencarian cerdas kami:

```php
// Cari berdasarkan nama - berfungsi dengan pencocokan parsial
$provinces = Province::search('jawa')->get();
// Mengembalikan: Jawa Barat, Jawa Tengah, Jawa Timur

// Cari berdasarkan kode - pencocokan persis
$jakarta = Province::search('31')->first();

// Kasus penggunaan bisnis: Pencarian lokasi pelanggan
$customerRegency = Regency::search($userInput)->first();
```

**Manfaat**: Membantu pelanggan dengan cepat menemukan lokasi mereka, meningkatkan kegunaan formulir dan pengalaman pengguna.

### 🌍 **Intelijen Geografis**
Setiap model menyertakan data geografis untuk fitur lokasi canggih:

```php
// Akses kode administratif resmi
$village->code;        // "33.74.01.1001"
$village->name;        // "Medono"

// Batas geografis untuk pemetaan
$province->coordinates; // Array titik batas
$province->latitude;    // Koordinat pusat
$province->longitude;   // Koordinat pusat
```

**Manfaat**: Memungkinkan fitur pemetaan, perhitungan area layanan, dan fungsionalitas berbasis lokasi.

## Solusi Bisnis berdasarkan Tingkat Administratif

### 🏛️ **Tingkat Provinsi: Operasi Strategis**

**Sempurna untuk**: Ekspansi regional, analisis pasar, pelaporan kepatuhan

```php
use Creasi\Nusa\Models\Province;

// Analisis pasar: Temukan wilayah berpotensi tinggi
$javaProvinces = Province::search('jawa')->get();
foreach ($javaProvinces as $province) {
    echo "Pasar: {$province->name}";
    echo "Kota: {$province->regencies->count()}";
    echo "Cakupan: {$province->villages->count()} desa/kelurahan";
}

// Kepatuhan: Hasilkan laporan regional
$centralJava = Province::find('33');
$report = [
    'region' => $centralJava->name,
    'postal_codes' => $centralJava->postal_codes,
    'administrative_units' => $centralJava->regencies->count()
];
```

**Manfaat**:
- **Analisis Regional**: Memahami cakupan pasar dan peluang berdasarkan provinsi
- **Pelaporan Kepatuhan**: Menghasilkan laporan regional yang akurat untuk persyaratan administratif
- **Perencanaan Strategis**: Menganalisis cakupan geografis dan kemungkinan ekspansi

[→ Referensi Model Provinsi Lengkap](/id/api/models/province)

### 🏙️ **Tingkat Kabupaten/Kota: Operasi Kota**

**Sempurna untuk**: Logistik perkotaan, layanan spesifik kota, kemitraan lokal

```php
use Creasi\Nusa\Models\Regency;

// Logistik: Optimalkan pengiriman tingkat kota
$semarang = Regency::search('semarang')->first();
$deliveryZones = $semarang->districts->groupBy('postal_code');

// Ekspansi bisnis: Analisis pasar kota
$jakartaRegencies = Regency::whereHas('province', function ($query) {
    $query->where('code', '31'); // DKI Jakarta
})->get();

foreach ($jakartaRegencies as $regency) {
    echo "Kota: {$regency->name}";
    echo "Kecamatan: {$regency->districts->count()}";
    echo "Ukuran pasar: {$regency->villages->count()} komunitas";
}
```

**Manfaat**:
- **Operasi Perkotaan**: Mengatur logistik tingkat kota dan pengiriman layanan
- **Analisis Lokal**: Memahami karakteristik pasar spesifik kota
- **Perencanaan Regional**: Merencanakan operasi di berbagai wilayah perkotaan

[→ Referensi Model Kabupaten/Kota Lengkap](/id/api/models/regency)

### 🏘️ **Tingkat Kecamatan: Layanan Komunitas**

**Sempurna untuk**: Layanan lokal, keterlibatan komunitas, operasi lapangan

```php
use Creasi\Nusa\Models\District;

// Kesehatan: Kelola area cakupan klinik
$district = District::find('33.75.01');
$serviceArea = [
    'district' => $district->name,
    'regency' => $district->regency->name,
    'villages_served' => $district->villages->count(),
    'estimated_population' => $district->villages->count() * 1000
];

// Operasi lapangan: Optimalkan rute layanan
$districts = District::where('regency_code', '33.74')->get();
foreach ($districts as $district) {
    echo "Area layanan: {$district->name}";
    echo "Desa/Kelurahan: {$district->villages->count()}";
    echo "Koordinat: {$district->latitude}, {$district->longitude}";
}
```

**Manfaat**:
- **Layanan Lokal**: Mengatur pengiriman layanan tingkat komunitas
- **Operasi Lapangan**: Merencanakan rute dan cakupan untuk tim lapangan
- **Perencanaan Layanan**: Memahami area layanan lokal dan cakupan

[→ Referensi Model Kecamatan Lengkap](/id/api/models/district)

### 🏠 **Tingkat Desa/Kelurahan: Penargetan Presisi**

**Sempurna untuk**: Pengiriman *last-mile*, penargetan pelanggan, analitik presisi

```php
use Creasi\Nusa\Models\Village;

// E-commerce: Perencanaan pengiriman yang tepat
$village = Village::find('33.75.01.1002');
$deliveryInfo = [
    'village' => $village->name,
    'postal_code' => $village->postal_code,
    'full_address' => [
        $village->name,
        $village->district->name,
        $village->regency->name,
        $village->province->name
    ],
    'coordinates' => [$village->latitude, $village->longitude]
];

// Analitik pelanggan: Wawasan demografi
$customerVillages = Village::whereIn('code', $customerVillageCodes)
    ->with(['district.regency.province'])
    ->get();

$demographics = $customerVillages->groupBy('province.name')
    ->map(function ($villages, $province) {
        return [
            'province' => $province,
            'customer_villages' => $villages->count(),
            'market_penetration' => $villages->count() / 1000 // desa/kelurahan per 1000
        ];
    });
```

**Manfaat**:
- **Pengiriman Presisi**: Pengalamatan yang akurat dengan dukungan kode pos
- **Segmentasi Pelanggan**: Analisis pelanggan geografis yang terperinci
- **Wawasan Lokal**: Data tingkat desa/kelurahan untuk operasi yang ditargetkan

[→ Referensi Model Desa/Kelurahan Lengkap](/id/api/models/village)
## Relasi Cerdas & Kinerja

### 🔗 **Relasi Hierarkis yang Cerdas**

Setiap model memahami tempatnya dalam struktur administratif Indonesia:

```php
// Navigasi hierarki dengan mudah
$village = Village::find('33.75.01.1002');

// Akses tingkat apa pun secara instan
echo $village->name;              // "Medono"
echo $village->district->name;    // "Pekalongan Barat"
echo $village->regency->name;     // "Kota Pekalongan"
echo $village->province->name;    // "Jawa Tengah"

// Intelijen bisnis dalam satu kueri
$customerAnalysis = $village->province->regencies()
    ->withCount(['villages', 'districts'])
    ->get();
```

### ⚡ **Kinerja Tingkat Perusahaan**

Dibangun untuk skala dengan optimasi cerdas:

```php
// Operasi massal yang efisien
$marketAnalysis = Province::with(['regencies:code,name,province_code'])
    ->whereIn('code', ['31', '32', '33']) // Provinsi Jawa
    ->get();

// Paginasi cerdas untuk dataset besar
$villages = Village::where('regency_code', '33.74')
    ->paginate(50); // Tangani 83K+ desa/kelurahan secara efisien

// Pencarian yang dioptimalkan di jutaan catatan
$locations = Village::search('jakarta')->limit(10)->get();
```

## Skenario Implementasi Umum

### 📈 **Aplikasi E-Commerce**
Fitur berbasis lokasi untuk zona pengiriman, optimasi pengiriman, dan segmentasi pelanggan berdasarkan wilayah administratif.

### 🏥 **Sistem Kesehatan**
Manajemen fasilitas, analisis demografi pasien, dan perencanaan cakupan layanan menggunakan data administratif hierarkis.

### 🚚 **Aplikasi Logistik**
Perencanaan rute, manajemen area layanan, dan optimasi pengiriman menggunakan struktur administratif Indonesia.

## Pola Integrasi

### 🎯 **Integrasi Cepat**
```php
// Tambahkan lokasi ke model yang sudah ada
class Customer extends Model
{
    use WithVillage; // Kemampuan lokasi instan
}
```

### 🏢 **Integrasi Perusahaan**
```php
// Persyaratan bisnis yang kompleks
class BusinessLocation extends Model
{
    use WithAddresses, WithCoordinate;
    // Beberapa lokasi + koordinat GPS
}
```

### 🚀 **Analitik Lanjutan**
```php
// Intelijen bisnis siap
$marketInsights = Province::withCount(['regencies', 'villages'])
    ->with(['regencies' => function ($query) {
        $query->withCount('villages');
    }])
    ->get();
```

## Memulai

Model Laravel Nusa menyediakan fondasi yang kuat untuk membangun aplikasi berbasis lokasi yang bekerja dengan struktur administratif Indonesia.

### **Langkah Selanjutnya**:

1. **[Ikhtisar Model](/id/api/models/overview)** - Detail teknis, struktur database, dan relasi
2. **[Panduan Kustomisasi](/id/guide/customization)** - Pelajari cara mengintegrasikan model dengan aplikasi Anda
3. **[Manajemen Alamat](/id/guide/addresses)** - Jelajahi fungsionalitas alamat
4. **[Contoh Implementasi](/id/examples/custom-models)** - Lihat pola penggunaan praktis

---

*Bangun aplikasi berbasis lokasi dengan data administratif Indonesia yang komprehensif.*