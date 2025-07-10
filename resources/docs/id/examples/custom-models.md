# Model Kustom

Panduan lengkap untuk memperluas dan mengkustomisasi model Laravel Nusa sesuai dengan kebutuhan aplikasi Anda, termasuk penambahan trait, relasi kustom, dan integrasi dengan model existing.

## Memperluas Model Dasar

### Model Provinsi Kustom

```php
// app/Models/CustomProvince.php
namespace App\Models;

use Creasi\Nusa\Models\Province as BaseProvince;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomProvince extends BaseProvince
{
    /**
     * Atribut fillable tambahan
     */
    protected $fillable = [
        'code',
        'name',
        'latitude',
        'longitude',
        'timezone',
        'area_km2',
        'population'
    ];

    /**
     * Atribut kustom
     */
    protected $appends = [
        'display_name',
        'population_density'
    ];

    /**
     * Accessor kustom untuk display name
     */
    public function getDisplayNameAttribute()
    {
        return "Provinsi {$this->name}";
    }

    /**
     * Hitung kepadatan populasi
     */
    public function getPopulationDensityAttribute()
    {
        if ($this->area_km2 && $this->population) {
            return round($this->population / $this->area_km2, 2);
        }
        
        return null;
    }
    
    /**
     * Relasi ke lokasi bisnis
     */
    public function businessLocations(): HasMany
    {
        return $this->hasMany(BusinessLocation::class, 'province_code', 'code');
    }

    /**
     * Relasi ke data penjualan
     */
    public function salesData(): HasMany
    {
        return $this->hasMany(SalesData::class, 'province_code', 'code');
    }

    /**
     * Scope untuk provinsi berpopulasi tinggi
     */
    public function scopeHighPopulation($query, $threshold = 5000000)
    {
        return $query->where('population', '>=', $threshold);
    }

    /**
     * Scope untuk provinsi di Jawa
     */
    public function scopeJava($query)
    {
        return $query->whereIn('code', ['31', '32', '33', '34', '35', '36']);
    }

    /**
     * Dapatkan total revenue bisnis untuk provinsi ini
     */
    public function getTotalRevenue()
    {
        return $this->salesData()
            ->sum('revenue');
    }

    /**
     * Dapatkan persentase penetrasi pasar
     */
    public function getMarketPenetration()
    {
        $totalVillages = $this->villages()->count();
        $coveredVillages = $this->businessLocations()
            ->distinct('village_code')
            ->count();

        return $totalVillages > 0 ? ($coveredVillages / $totalVillages) * 100 : 0;
    }
}
```

### Model Village Kustom dengan Logika Bisnis

```php
// app/Models/CustomVillage.php
namespace App\Models;

use Creasi\Nusa\Models\Village as BaseVillage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomVillage extends BaseVillage
{
    protected $appends = [
        'full_address',
        'service_coverage',
        'market_potential'
    ];
    
    /**
     * Get formatted full address
     */
    public function getFullAddressAttribute()
    {
        return "{$this->name}, {$this->district->name}, {$this->regency->name}, {$this->province->name}";
    }
    
    /**
     * Check service coverage
     */
    public function getServiceCoverageAttribute()
    {
        return $this->servicePoints()->exists();
    }
    
    /**
     * Calculate market potential score
     */
    public function getMarketPotentialAttribute()
    {
        $factors = [
            'population' => $this->estimated_population ?? 1000,
            'accessibility' => $this->accessibility_score ?? 5,
            'economic_activity' => $this->economic_score ?? 5,
            'competition' => 10 - ($this->competitors()->count() ?? 0)
        ];
        
        // Weighted scoring
        $score = (
            $factors['population'] * 0.3 +
            $factors['accessibility'] * 0.25 +
            $factors['economic_activity'] * 0.25 +
            $factors['competition'] * 0.2
        ) / 100;
        
        return min(10, max(1, $score));
    }
    
    /**
     * Relationship to service points
     */
    public function servicePoints(): HasMany
    {
        return $this->hasMany(ServicePoint::class, 'village_code', 'code');
    }
    
    /**
     * Relationship to customers
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'village_code', 'code');
    }
    
    /**
     * Relationship to competitors
     */
    public function competitors(): HasMany
    {
        return $this->hasMany(Competitor::class, 'village_code', 'code');
    }
    
    /**
     * Relationship to demographic data
     */
    public function demographics(): HasOne
    {
        return $this->hasOne(VillageDemographics::class, 'village_code', 'code');
    }
    
    /**
     * Scope for villages with high market potential
     */
    public function scopeHighPotential($query, $threshold = 7)
    {
        return $query->whereRaw("
            (estimated_population * 0.3 + 
             accessibility_score * 0.25 + 
             economic_score * 0.25 + 
             (10 - competitor_count) * 0.2) / 100 >= ?
        ", [$threshold]);
    }
    
    /**
     * Scope for underserved villages
     */
    public function scopeUnderserved($query)
    {
        return $query->whereDoesntHave('servicePoints');
    }
    
    /**
     * Calculate distance to nearest service point
     */
    public function distanceToNearestService()
    {
        $nearestService = ServicePoint::selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(latitude))
            )) AS distance
        ", [$this->latitude, $this->longitude, $this->latitude])
        ->orderBy('distance')
        ->first();
        
        return $nearestService ? $nearestService->distance : null;
    }
}
```

## Creating Custom Traits

### Location Analytics Trait

```php
// app/Traits/HasLocationAnalytics.php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasLocationAnalytics
{
    /**
     * Get revenue by time period
     */
    public function getRevenueByPeriod($startDate, $endDate)
    {
        return $this->salesData()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
    }
    
    /**
     * Get customer growth rate
     */
    public function getCustomerGrowthRate($months = 12)
    {
        $currentPeriod = $this->customers()
            ->where('created_at', '>=', now()->subMonths($months))
            ->count();
            
        $previousPeriod = $this->customers()
            ->whereBetween('created_at', [
                now()->subMonths($months * 2),
                now()->subMonths($months)
            ])
            ->count();
        
        if ($previousPeriod == 0) {
            return $currentPeriod > 0 ? 100 : 0;
        }
        
        return (($currentPeriod - $previousPeriod) / $previousPeriod) * 100;
    }
    
    /**
     * Get market share in region
     */
    public function getMarketShare()
    {
        $totalMarketSize = $this->getTotalMarketSize();
        $ourMarketSize = $this->getOurMarketSize();
        
        return $totalMarketSize > 0 ? ($ourMarketSize / $totalMarketSize) * 100 : 0;
    }
    
    /**
     * Scope for performance analysis
     */
    public function scopeWithPerformanceMetrics(Builder $query)
    {
        return $query->withCount([
            'customers',
            'servicePoints',
            'salesData as total_revenue' => function ($query) {
                $query->select(\DB::raw('sum(amount)'));
            }
        ]);
    }
    
    /**
     * Get competitor analysis
     */
    public function getCompetitorAnalysis()
    {
        $competitors = $this->competitors()
            ->with(['services', 'reviews'])
            ->get();
        
        return [
            'total_competitors' => $competitors->count(),
            'average_rating' => $competitors->avg('rating'),
            'service_coverage' => $competitors->flatMap->services->unique('type')->count(),
            'market_leaders' => $competitors->where('market_share', '>', 20)->values()
        ];
    }
}
```

## Configuration Updates

### Update Laravel Nusa Configuration

```php
// config/nusa.php
return [
    'models' => [
        'province' => \App\Models\CustomProvince::class,
        'regency' => \Creasi\Nusa\Models\Regency::class,
        'district' => \Creasi\Nusa\Models\District::class,
        'village' => \App\Models\CustomVillage::class,
        'address' => \Creasi\Nusa\Models\Address::class,
    ],
    
    'custom_traits' => [
        'analytics' => \App\Traits\HasLocationAnalytics::class,
        'geographic_search' => \App\Traits\HasGeographicSearch::class,
    ],
    
    'extensions' => [
        'business_locations' => true,
        'demographic_data' => true,
        'market_analysis' => true,
    ]
];
```

### Service Provider Registration

```php
// app/Providers/NusaServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Village;
use App\Models\CustomProvince;
use App\Models\CustomVillage;

class NusaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register custom models
        $this->app->bind(Province::class, CustomProvince::class);
        $this->app->bind(Village::class, CustomVillage::class);
        
        // Register custom observers
        CustomProvince::observe(ProvinceObserver::class);
        CustomVillage::observe(VillageObserver::class);
    }
}
```

## Next Steps

- **[API Integration](/id/examples/api-integration)** - Advanced API usage patterns
- **[Geographic Queries](/id/examples/geographic-queries)** - Location-based queries and mapping
- **[Address Forms](/id/examples/address-forms)** - Building interactive address forms
- **[Models Documentation](/id/api/models/overview)** - Complete model reference
