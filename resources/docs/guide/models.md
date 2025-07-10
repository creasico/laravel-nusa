# Models & Relationships

**Build location-aware applications** with Laravel Nusa's comprehensive Eloquent models. These models provide the foundation for integrating Indonesia's administrative structure into your business logic, from national-level analytics to village-specific operations.

## Why Use Laravel Nusa Models?

### 🎯 **Complete Administrative Coverage**
Work with every level of Indonesia's administrative hierarchy - from 34 provinces down to 83,467 villages. This comprehensive coverage ensures your application can handle any location-based requirement.

### ⚡ **Ready-to-Use Relationships**
Pre-built Eloquent relationships handle the complexity of Indonesia's hierarchical structure, allowing you to focus on your business logic rather than data management.

### 🔄 **Official Data Sources**
Models work with data synchronized from official government sources, ensuring your applications have accurate and current administrative information.

## Understanding the Administrative Hierarchy

### 📊 **Four-Level Structure**
```
🇮🇩 Indonesia
├── 34 Provinces → Strategic regional operations
├── 514 Regencies → City and regency-level services
├── 7,266 Districts → Community and local services
└── 83,467 Villages → Precise location targeting
```

::: tip Technical Details
For detailed information about database structure, relationships, and technical implementation, see the [Models Overview](/api/models/overview) in the API Reference.
:::

### 🏢 **Business Applications**

**E-Commerce Platforms**: Shipping zones, delivery optimization, and customer segmentation
**Healthcare Systems**: Facility management, patient demographics, and service coverage
**Financial Services**: Risk assessment, branch planning, and regulatory compliance
**Government Services**: Citizen management, resource allocation, and administrative reporting

## Powerful Model Features

### 🔍 **Intelligent Search**
Find any location instantly with our smart search capabilities:

```php
// Find by name - works with partial matches
$provinces = Province::search('jawa')->get();
// Returns: Jawa Barat, Jawa Tengah, Jawa Timur

// Find by code - exact matches
$jakarta = Province::search('31')->first();

// Business use case: Customer location lookup
$customerRegency = Regency::search($userInput)->first();
```

**Benefits**: Helps customers quickly find their locations, improving form usability and user experience.

### 🌍 **Geographic Intelligence**
Every model includes geographic data for advanced location features:

```php
// Access official administrative codes
$village->code;        // "33.74.01.1001"
$village->name;        // "Medono"

// Geographic boundaries for mapping
$province->coordinates; // Array of boundary points
$province->latitude;    // Center coordinates
$province->longitude;   // Center coordinates
```

**Benefits**: Enables mapping features, service area calculations, and location-based functionality.

## Business Solutions by Administrative Level

### 🏛️ **Province Level: Strategic Operations**

**Perfect for**: Regional expansion, market analysis, compliance reporting

```php
use Creasi\Nusa\Models\Province;

// Market analysis: Find high-potential regions
$javaProvinces = Province::search('jawa')->get();
foreach ($javaProvinces as $province) {
    echo "Market: {$province->name}";
    echo "Cities: {$province->regencies->count()}";
    echo "Coverage: {$province->villages->count()} villages";
}

// Compliance: Generate regional reports
$centralJava = Province::find('33');
$report = [
    'region' => $centralJava->name,
    'postal_codes' => $centralJava->postal_codes,
    'administrative_units' => $centralJava->regencies->count()
];
```

**Benefits**:
- **Regional Analysis**: Understand market coverage and opportunities by province
- **Compliance Reporting**: Generate accurate regional reports for administrative requirements
- **Strategic Planning**: Analyze geographic coverage and expansion possibilities

[→ Complete Province Model Reference](/api/models/province)

### 🏙️ **Regency Level: City Operations**

**Perfect for**: Urban logistics, city-specific services, local partnerships

```php
use Creasi\Nusa\Models\Regency;

// Logistics: Optimize city-level delivery
$semarang = Regency::search('semarang')->first();
$deliveryZones = $semarang->districts->groupBy('postal_code');

// Business expansion: Analyze city markets
$jakartaRegencies = Regency::whereHas('province', function ($query) {
    $query->where('code', '31'); // DKI Jakarta
})->get();

foreach ($jakartaRegencies as $regency) {
    echo "City: {$regency->name}";
    echo "Districts: {$regency->districts->count()}";
    echo "Market size: {$regency->villages->count()} communities";
}
```

**Benefits**:
- **Urban Operations**: Organize city-level logistics and service delivery
- **Local Analysis**: Understand city-specific market characteristics
- **Regional Planning**: Plan operations across different urban areas

[→ Complete Regency Model Reference](/api/models/regency)

### 🏘️ **District Level: Community Services**

**Perfect for**: Local services, community engagement, field operations

```php
use Creasi\Nusa\Models\District;

// Healthcare: Manage clinic coverage areas
$district = District::find('33.75.01');
$serviceArea = [
    'district' => $district->name,
    'regency' => $district->regency->name,
    'villages_served' => $district->villages->count(),
    'estimated_population' => $district->villages->count() * 1000
];

// Field operations: Optimize service routes
$districts = District::where('regency_code', '33.74')->get();
foreach ($districts as $district) {
    echo "Service area: {$district->name}";
    echo "Villages: {$district->villages->count()}";
    echo "Coordinates: {$district->latitude}, {$district->longitude}";
}
```

**Benefits**:
- **Local Services**: Organize community-level service delivery
- **Field Operations**: Plan routes and coverage for field teams
- **Service Planning**: Understand local service areas and coverage

[→ Complete District Model Reference](/api/models/district)

### 🏠 **Village Level: Precision Targeting**

**Perfect for**: Last-mile delivery, customer targeting, precise analytics

```php
use Creasi\Nusa\Models\Village;

// E-commerce: Precise delivery planning
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

// Customer analytics: Demographic insights
$customerVillages = Village::whereIn('code', $customerVillageCodes)
    ->with(['district.regency.province'])
    ->get();

$demographics = $customerVillages->groupBy('province.name')
    ->map(function ($villages, $province) {
        return [
            'province' => $province,
            'customer_villages' => $villages->count(),
            'market_penetration' => $villages->count() / 1000 // villages per 1000
        ];
    });
```

**Benefits**:
- **Precise Delivery**: Accurate addressing with postal code support
- **Customer Segmentation**: Detailed geographic customer analysis
- **Local Insights**: Village-level data for targeted operations

[→ Complete Village Model Reference](/api/models/village)
## Smart Relationships & Performance

### 🔗 **Intelligent Hierarchical Relationships**

Every model understands its place in Indonesia's administrative structure:

```php
// Navigate the hierarchy effortlessly
$village = Village::find('33.75.01.1002');

// Access any level instantly
echo $village->name;              // "Medono"
echo $village->district->name;    // "Pekalongan Barat"
echo $village->regency->name;     // "Kota Pekalongan"
echo $village->province->name;    // "Jawa Tengah"

// Business intelligence in one query
$customerAnalysis = $village->province->regencies()
    ->withCount(['villages', 'districts'])
    ->get();
```

### ⚡ **Enterprise-Grade Performance**

Built for scale with intelligent optimization:

```php
// Efficient bulk operations
$marketAnalysis = Province::with(['regencies:code,name,province_code'])
    ->whereIn('code', ['31', '32', '33']) // Java provinces
    ->get();

// Smart pagination for large datasets
$villages = Village::where('regency_code', '33.74')
    ->paginate(50); // Handle 83K+ villages efficiently

// Optimized search across millions of records
$locations = Village::search('jakarta')->limit(10)->get();
```

## Common Implementation Scenarios

### 📈 **E-Commerce Applications**
Location-aware features for shipping zones, delivery optimization, and customer segmentation based on administrative regions.

### 🏥 **Healthcare Systems**
Facility management, patient demographics analysis, and service coverage planning using hierarchical administrative data.

### 🚚 **Logistics Applications**
Route planning, service area management, and delivery optimization using Indonesia's administrative structure.

## Integration Patterns

### 🎯 **Quick Integration**
```php
// Add location to existing models
class Customer extends Model
{
    use WithVillage; // Instant location capability
}
```

### 🏢 **Enterprise Integration**
```php
// Complex business requirements
class BusinessLocation extends Model
{
    use WithAddresses, WithCoordinate;
    // Multiple locations + GPS coordinates
}
```

### 🚀 **Advanced Analytics**
```php
// Business intelligence ready
$marketInsights = Province::withCount(['regencies', 'villages'])
    ->with(['regencies' => function ($query) {
        $query->withCount('villages');
    }])
    ->get();
```

## Getting Started

Laravel Nusa's models provide a solid foundation for building location-aware applications that work with Indonesia's administrative structure.

### **Next Steps**:

1. **[Models Overview](/api/models/overview)** - Technical details, database structure, and relationships
2. **[Customization Guide](/guide/customization)** - Learn how to integrate models with your application
3. **[Address Management](/guide/addresses)** - Explore address functionality
4. **[Implementation Examples](/examples/custom-models)** - See practical usage patterns

---

*Build location-aware applications with Indonesia's comprehensive administrative data.*
