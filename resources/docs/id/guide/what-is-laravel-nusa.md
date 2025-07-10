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

### 🏪 **E-Commerce Platforms**
- **Shipping zones** based on administrative regions
- **Delivery cost calculation** by distance and location
- **Customer segmentation** by geographic areas
- **Inventory distribution** across regions

### 🏥 **Healthcare Systems**
- **Facility management** with precise location data
- **Patient demographics** and regional health analytics
- **Service coverage** mapping and optimization
- **Emergency response** coordination

### 🏦 **Financial Services**
- **Branch network** planning and optimization
- **Risk assessment** based on geographic factors
- **Regulatory compliance** with regional requirements
- **Market penetration** analysis

### 🏛️ **Government Services**
- **Citizen management** with accurate address data
- **Resource allocation** based on administrative boundaries
- **Service delivery** optimization
- **Administrative reporting** and analytics

## Key Features

### 📊 **Complete Hierarchy**
Access the full Indonesian administrative structure from province level down to individual villages, with proper parent-child relationships maintained throughout.

### 🔍 **Powerful Search**
Built-in search capabilities allow you to find locations by name, code, or postal code with flexible matching options.

### 📍 **Geographic Data**
Coordinate data for all administrative levels enables distance calculations, mapping, and location-based services.

### 🏠 **Address Management**
Comprehensive address management system with validation, formatting, and integration with the administrative hierarchy.

### 🔧 **Flexible Integration**
Multiple traits and helper methods make it easy to add location functionality to your existing models without major refactoring.

## Technical Highlights

### 🚀 **Performance Optimized**
- Efficient database structure with proper indexing
- Optimized queries for large datasets
- Caching support for frequently accessed data
- Pagination support for handling large result sets

### 🔒 **Data Integrity**
- Foreign key constraints ensure referential integrity
- Validation rules prevent invalid location combinations
- Consistent data formatting across all levels
- Regular data validation and cleanup processes

### 🎨 **Customizable**
- Extend base models with your own functionality
- Add custom relationships and business logic
- Configure API endpoints and middleware
- Customize validation rules and error messages

### 📱 **API Ready**
- RESTful API endpoints for all administrative levels
- JSON responses with proper HTTP status codes
- Rate limiting and authentication support
- OpenAPI documentation for easy integration

## Getting Started

Laravel Nusa is designed to be simple to install and use, while providing powerful features for complex applications.

### Quick Installation

```bash
composer require creasi/laravel-nusa
php artisan nusa:install
```

### Basic Usage

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

### Add to Your Models

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
