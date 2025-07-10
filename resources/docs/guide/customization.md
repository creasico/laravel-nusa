# Customization

Enhance your Laravel applications with location-based features using Laravel Nusa's flexible model traits. These customization tools help you integrate Indonesian administrative data into your application's models and business logic.

## Benefits of Laravel Nusa Traits

### 🎯 **Ready-to-Use Solutions**
Pre-built traits that handle common location-based requirements, saving development time and ensuring consistency.

### 🚀 **Quick Integration**
Add location functionality to your existing models with minimal code changes. Traits handle the complexity of administrative relationships.

### 🔧 **Flexible Approach**
Choose only the traits you need. Mix and match different traits to create the right solution for your specific requirements.

## Common Use Cases

### 🏪 **E-Commerce Applications**

**Challenge**: Managing customer addresses, shipping zones, and store locations across Indonesia's complex administrative structure.

**Solution**: Implement location-aware features for better customer experience:

```php
// Customer with multiple shipping addresses
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

// Store locator with distance calculation
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

**Benefits**:
- More accurate shipping cost calculations
- Better customer experience with location-based features
- Efficient store locator and mapping functionality
- Support for regional business operations

[→ See Complete Implementation](/api/concerns/with-addresses)

### 🏢 **Multi-Location Business Management**

**Challenge**: Managing corporate structures with headquarters, branches, and regional offices across multiple provinces.

**Solution**: Organize multi-location business operations:

```php
// Company with multiple office locations
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

// Regional sales territory management
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

**Benefits**:
- Centralized location data management
- Regional analysis and reporting
- Territory planning and organization
- Administrative region-based reporting

[→ Explore Enterprise Solutions](/api/concerns/with-address)

### 🚚 **Logistics & Delivery**

**Challenge**: Optimizing delivery routes, managing service areas, and calculating shipping costs across Indonesia's diverse geography.

**Solution**: Build intelligent logistics systems:

```php
// Delivery zone management
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

// Logistics provider with service coverage
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

**Benefits**:
- Better route planning and optimization
- Clear service coverage mapping
- Location-based pricing capabilities
- Improved delivery planning

[→ Master Logistics Solutions](/api/concerns/with-villages)

### 🏥 **Healthcare & Public Services**

**Challenge**: Managing healthcare facilities, service coverage, and patient demographics across administrative regions.

**Solution**: Enhance public service delivery:

```php
// Healthcare facility management
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

// Patient management with location tracking
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

**Benefits**:
- Better healthcare facility management
- Improved resource planning
- Patient location tracking and analysis
- Service coverage optimization

[→ Healthcare Solutions Guide](/api/concerns/with-district)

### 🏛️ **Government & Administration**

**Challenge**: Managing citizen services, administrative boundaries, and regional governance across Indonesia's administrative hierarchy.

**Solution**: Modernize government services:

```php
// Government service center
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

// Citizen registration with location verification
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

**Benefits**:
- Organized citizen service management
- Accurate demographic and location data
- Better resource planning and distribution
- Improved government service delivery

[→ Government Solutions](/api/concerns/with-regency)

## Available Customization Tools

### 🔗 **Relationship Traits**

Connect your models to Indonesia's administrative hierarchy:

| Trait | Use Case | Business Value |
|-------|----------|----------------|
| **WithProvince** | Regional offices, sales territories | Provincial-level analytics and management |
| **WithRegency** | Service centers, distribution hubs | City/regency-level operations |
| **WithDistrict** | Local facilities, community services | District-level service delivery |
| **WithVillage** | Customer addresses, precise locations | Village-level precision and targeting |

[→ Explore All Relationship Traits](/api/concerns/)

### 📍 **Address Management**

Handle complex address requirements:

| Trait | Best For | Key Features |
|-------|----------|--------------|
| **WithAddress** | User profiles, single locations | One address per model, full hierarchy |
| **WithAddresses** | Multi-location businesses | Multiple addresses, type categorization |

[→ Master Address Management](/guide/addresses)

### 🌍 **Geographic Features**

Add location intelligence:

| Trait | Capabilities | Business Applications |
|-------|--------------|----------------------|
| **WithCoordinate** | GPS coordinates, distance calculations | Store locators, delivery optimization |
| **WithVillages** | Multiple villages, postal codes | Service coverage, territory management |
| **WithDistricts** | Multiple districts | Regional administration, service areas |

[→ Geographic Solutions](/api/concerns/with-coordinate)

## Implementation Patterns

### 🎯 **Quick Start Pattern**

For simple location associations:

```php
class YourModel extends Model
{
    use WithVillage; // Most specific location
    
    protected $fillable = ['name', 'village_code'];
}
```

### 🏢 **Enterprise Pattern**

For complex business requirements:

```php
class BusinessModel extends Model
{
    use WithAddresses, WithCoordinate;
    
    // Multiple locations with GPS coordinates
    // Perfect for multi-branch businesses
}
```

### 🚀 **Full-Featured Pattern**

For comprehensive location solutions:

```php
class AdvancedModel extends Model
{
    use WithProvince, WithAddresses, WithCoordinate;
    
    // Provincial association + multiple addresses + GPS
    // Ideal for enterprise applications
}
```

## Getting Started

### 1. **Choose Your Traits**

Select traits based on your business needs:
- **Single location**: Use `WithVillage` or `WithAddress`
- **Multiple locations**: Use `WithAddresses`
- **Geographic features**: Add `WithCoordinate`
- **Regional management**: Use `WithProvince` or `WithRegency`

### 2. **Implement in Your Models**

```php
use Creasi\Nusa\Models\Concerns\WithVillage;

class Customer extends Model
{
    use WithVillage;
    
    protected $fillable = ['name', 'email', 'village_code'];
}
```

### 3. **Update Your Database**

```php
Schema::table('customers', function (Blueprint $table) {
    $table->string('village_code')->nullable();
    $table->foreign('village_code')->references('code')->on('villages');
});
```

### 4. **Start Building**

```php
$customer = Customer::with('village.regency.province')->first();
echo "Customer from: {$customer->village->name}, {$customer->village->regency->name}";
```

## Common Implementation Examples

### 📈 **E-Commerce Applications**
Using traits to build address management, shipping zones, and customer location features for online stores.

### 🏥 **Healthcare Systems**
Implementing facility management, patient demographics, and service coverage using administrative region data.

### 🚚 **Logistics Applications**
Building route optimization, service area management, and delivery planning systems.

## Next Steps

Ready to add location features to your application?

1. **[Explore All Traits](/api/concerns/)** - Browse comprehensive trait documentation
2. **[Implementation Examples](/examples/custom-models)** - See practical usage patterns
3. **[Address Management](/guide/addresses)** - Learn about address functionality
4. **[Geographic Features](/examples/geographic-queries)** - Discover location-based capabilities

---

*Enhance your Laravel applications with Indonesia's administrative data.*
