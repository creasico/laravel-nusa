# Apa itu Laravel Nusa?

Laravel Nusa adalah paket Laravel yang menyediakan data wilayah administratif Indonesia yang lengkap dan siap pakai, dirancang khusus untuk memudahkan developer dalam mengintegrasikan data provinsi, kabupaten/kota, kecamatan, dan kelurahan/desa ke dalam aplikasi Laravel mereka.

Paket ini mencakup:

- **34 Provinsi** dengan kode dan nama resmi
- **514 Kabupaten/Kota** dengan relasi hierarkis
- **7.266 Kecamatan** dengan struktur terorganisir
- **83.467 Kelurahan/Desa** dengan kode pos

## Mengapa Laravel Nusa?

### 🎯 **Data Administratif Lengkap**
Laravel Nusa menyediakan dataset wilayah administratif Indonesia yang paling komprehensif untuk aplikasi Laravel. Setiap provinsi, kabupaten/kota, kecamatan, dan desa disertakan dengan kode resmi pemerintah dan relasi hierarkis yang akurat.

### ⚡ **Model Siap Pakai**
Model Eloquent yang sudah dibangun dengan relasi lengkap memungkinkan Anda langsung mulai bekerja dengan data administratif Indonesia tanpa kompleksitas setup.

### 🔄 **Selalu Terkini**
Data disinkronisasi dengan sumber resmi pemerintah dan diperbarui secara berkala untuk memastikan akurasi dan kelengkapan.

### 🛠️ **Developer-Friendly**
Dirancang dengan mengikuti best practice Laravel, menampilkan API yang intuitif, dokumentasi komprehensif, dan opsi kustomisasi yang ekstensif.

## Apa yang Bisa Anda Bangun

### 🏪 **Platform E-Commerce**
- **Zona pengiriman** berdasarkan wilayah administratif
- **Kalkulasi biaya pengiriman** berdasarkan jarak dan lokasi
- **Segmentasi pelanggan** berdasarkan area geografis
- **Distribusi inventori** lintas wilayah

### 🏥 **Sistem Kesehatan**
- **Manajemen fasilitas** dengan data lokasi yang presisi
- **Demografi pasien** dan analitik kesehatan regional
- **Pemetaan cakupan layanan** dan optimasi
- **Koordinasi tanggap darurat**

### 🏦 **Layanan Keuangan**
- **Perencanaan jaringan cabang** dan optimasi
- **Penilaian risiko** berdasarkan faktor geografis
- **Kepatuhan regulasi** dengan persyaratan regional
- **Analisis penetrasi pasar**

### 🏛️ **Layanan Pemerintah**
- **Manajemen warga** dengan data alamat yang akurat
- **Alokasi sumber daya** berdasarkan batas administratif
- **Optimasi penyampaian layanan**
- **Pelaporan administratif** dan analitik

## Fitur Utama

### 📊 **Hierarki Lengkap**
Akses struktur administratif Indonesia lengkap dari tingkat provinsi hingga desa individual, dengan relasi parent-child yang terjaga dengan baik.

### 🔍 **Pencarian Powerful**
Kemampuan pencarian bawaan memungkinkan Anda menemukan lokasi berdasarkan nama, kode, atau kode pos dengan opsi pencocokan yang fleksibel.

### 📍 **Data Geografis**
Data koordinat untuk semua tingkat administratif memungkinkan kalkulasi jarak, pemetaan, dan layanan berbasis lokasi.

### 🏠 **Manajemen Alamat**
Sistem manajemen alamat komprehensif dengan validasi, formatting, dan integrasi dengan hierarki administratif.

### 🔧 **Integrasi Fleksibel**
Multiple trait dan helper method memudahkan penambahan fungsionalitas lokasi ke model existing tanpa refactoring besar.

## Highlight Teknis

### 🚀 **Optimasi Performa**
- Struktur database efisien dengan indexing yang tepat
- Query yang dioptimasi untuk dataset besar
- Dukungan caching untuk data yang sering diakses
- Dukungan pagination untuk menangani result set besar

### 🔒 **Integritas Data**
- Foreign key constraint memastikan integritas referensial
- Rule validasi mencegah kombinasi lokasi yang tidak valid
- Format data konsisten di semua tingkat
- Proses validasi dan pembersihan data berkala

### 🎨 **Dapat Dikustomisasi**
- Extend model dasar dengan fungsionalitas Anda sendiri
- Tambahkan relasi kustom dan logika bisnis
- Konfigurasi endpoint API dan middleware
- Kustomisasi rule validasi dan pesan error

### 📱 **Siap API**
- Endpoint RESTful API untuk semua tingkat administratif
- Response JSON dengan kode status HTTP yang tepat
- Dukungan rate limiting dan autentikasi
- Dokumentasi OpenAPI untuk integrasi mudah

## Memulai

Laravel Nusa dirancang untuk mudah diinstal dan digunakan, sambil menyediakan fitur powerful untuk aplikasi kompleks.

### Instalasi Cepat

```bash
composer require creasi/laravel-nusa
php artisan nusa:install
```

### Penggunaan Dasar

```php
use Creasi\Nusa\Models\Province;

// Get all provinces
$provinces = Province::all();

// Find specific province
$jateng = Province::find('33');

// Access relationships
$regencies = $jateng->regencies;
$districts = $jateng->districts;
$villages = $jateng->villages;
```

### Tambahkan ke Model Anda

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class User extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
}

// Now users have location relationships
$user = User::with('village.province')->first();
echo $user->village->province->name; // "Jawa Tengah"
```

## Real-World Applications

### E-Commerce Success Story
*"Laravel Nusa helped us implement accurate shipping cost calculation across Indonesia. The hierarchical data structure made it easy to create delivery zones and optimize our logistics network."*

### Healthcare Implementation
*"We use Laravel Nusa to manage our network of clinics and track patient demographics. The geographic data enables us to identify underserved areas and plan new facility locations."*

### Government Digital Services
*"Laravel Nusa provides the foundation for our citizen services portal. The accurate administrative data ensures proper service delivery and regulatory compliance."*

## Community and Support

### 📚 **Comprehensive Documentation**
- Step-by-step installation guides
- Complete API reference
- Real-world usage examples
- Best practices and patterns

### 🤝 **Active Community**
- GitHub discussions for questions and ideas
- Regular updates and improvements
- Community contributions welcome
- Professional support available

### 🔄 **Continuous Updates**
- Regular data updates from official sources
- New features based on community feedback
- Security updates and bug fixes
- Laravel version compatibility maintenance

## Langkah Selanjutnya

Ready to get started with Laravel Nusa? Here's what to do next:

1. **[Installation](/id/guide/installation)** - Install and configure Laravel Nusa
2. **[Getting Started](/id/guide/getting-started)** - Your first steps with the package
3. **[Models](/id/guide/models)** - Understanding the data structure
4. **[Examples](/id/examples/basic-usage)** - See practical implementation examples

---

*Build better applications with accurate Indonesian administrative data.*
