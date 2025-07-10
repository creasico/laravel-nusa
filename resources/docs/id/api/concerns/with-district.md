# WithDistrict

Trait `WithDistrict` memungkinkan model Anda memiliki relasi ke satu kecamatan, memberikan akses ke data kecamatan dan kemampuan untuk mengelompokkan data berdasarkan tingkat kecamatan.

The `WithDistrict` trait allows your model to have a relationship to a single district, providing access to district data and the ability to group data by district level.

## Overview

The `WithDistrict` trait is perfect for models that need to be associated with a specific district (kecamatan) for community-level operations. This is common for local service points, community centers, or operations that serve a specific district area.

### What You Get

- **District relationship** - Direct access to district data
- **Complete hierarchy access** - Access to regency and province through district
- **Village access** - Access to all villages within the district
- **Geographic coordinates** - Access to district center coordinates

## Basic Usage

### Adding the Trait

```php
use Creasi\Nusa\Models\Concerns\WithDistrict;

class CommunityCenter extends Model
{
    use WithDistrict;
    
    protected $fillable = [
        'name',
        'district_code',
        'coordinator_name',
        'phone'
    ];
}
```

### Database Requirements

Your model's table must have a `district_code` column:

```php
// Migration
Schema::table('community_centers', function (Blueprint $table) {
    $table->string('district_code', 8)->nullable();
    $table->foreign('district_code')->references('code')->on('nusa.districts');
});
```

### Creating Records

```php
// Create community center for Semarang Tengah district
$center = CommunityCenter::create([
    'name' => 'Semarang Tengah Community Center',
    'district_code' => '33.74.01',
    'coordinator_name' => 'Jane Doe',
    'phone' => '0247654321'
]);

// Access district and hierarchy data
echo $center->district->name; // "Semarang Tengah"
echo $center->district->regency->name; // "Kota Semarang"
echo $center->district->province->name; // "Jawa Tengah"
```

## Accessing District Data

### Basic District Access

```php
$center = CommunityCenter::with(['district.regency.province'])->first();

echo $center->district->name; // District name
echo $center->district->regency->name; // Regency name
echo $center->district->province->name; // Province name
echo $center->district->latitude; // District center latitude
echo $center->district->longitude; // District center longitude
```

### Accessing Villages

```php
$center = CommunityCenter::with(['district.villages'])->first();

// Access all villages in the district
foreach ($center->district->villages as $village) {
    echo $village->name;
    echo $village->postal_code;
}

// Get village count
echo "Villages served: " . $center->district->villages->count();
```

### Helper Methods

```php
class CommunityCenter extends Model
{
    use WithDistrict;
    
    // Get full location display
    public function getFullLocationAttribute()
    {
        if ($this->district) {
            return "{$this->district->name}, {$this->district->regency->name}, {$this->district->province->name}";
        }
        return null;
    }
    
    // Get service area statistics
    public function getServiceAreaStats()
    {
        if (!$this->district) {
            return null;
        }
        
        return [
            'district' => $this->district->name,
            'regency' => $this->district->regency->name,
            'province' => $this->district->province->name,
            'villages_served' => $this->district->villages->count(),
            'postal_codes' => $this->district->villages->pluck('postal_code')->unique()->sort()->values()
        ];
    }
    
    // Check if center serves urban area
    public function isInUrbanArea()
    {
        return $this->district && str_contains(strtolower($this->district->name), 'tengah');
    }
}
```

## Querying with District Relationships

### Basic Queries

```php
// Get centers with their districts
$centers = CommunityCenter::with(['district.regency.province'])->get();

// Get centers in specific district
$centers = CommunityCenter::where('district_code', '33.74.01')->get();

// Get centers in central districts
$centralCenters = CommunityCenter::whereHas('district', function ($query) {
    $query->where('name', 'like', '%Tengah%');
})->get();
```

### Advanced Filtering

```php
// Centers in specific regency
$centers = CommunityCenter::whereHas('district', function ($query) {
    $query->where('regency_code', '33.74');
})->get();

// Centers in specific province
$centers = CommunityCenter::whereHas('district', function ($query) {
    $query->where('province_code', '33');
})->get();

// Centers in districts with many villages
$largeCenters = CommunityCenter::whereHas('district', function ($query) {
    $query->has('villages', '>=', 10);
})->get();
```

### Custom Scopes

```php
class CommunityCenter extends Model
{
    use WithDistrict;
    
    // Scope for centers in central districts
    public function scopeInCentralDistricts($query)
    {
        return $query->whereHas('district', function ($q) {
            $q->where('name', 'like', '%Tengah%');
        });
    }
    
    // Scope for centers in specific regency
    public function scopeInRegency($query, $regencyCode)
    {
        return $query->whereHas('district', function ($q) use ($regencyCode) {
            $q->where('regency_code', $regencyCode);
        });
    }
    
    // Scope for centers in specific province
    public function scopeInProvince($query, $provinceCode)
    {
        return $query->whereHas('district', function ($q) use ($provinceCode) {
            $q->where('province_code', $provinceCode);
        });
    }
    
    // Scope for centers serving large areas
    public function scopeServingLargeAreas($query, $minVillages = 15)
    {
        return $query->whereHas('district', function ($q) use ($minVillages) {
            $q->has('villages', '>=', $minVillages);
        });
    }
}

// Usage
$centralCenters = CommunityCenter::inCentralDistricts()->get();
$semarangCenters = CommunityCenter::inRegency('33.74')->get();
$centralJavaCenters = CommunityCenter::inProvince('33')->get();
$largeCenters = CommunityCenter::servingLargeAreas(20)->get();
```

## Community Service Applications

### Health Service Post

```php
class HealthServicePost extends Model
{
    use WithDistrict;
    
    protected $fillable = ['name', 'district_code', 'services_offered', 'staff_count'];
    
    // Get residents in service area
    public function getResidentsInArea()
    {
        return Resident::whereHas('village', function ($query) {
            $query->where('district_code', $this->district_code);
        })->get();
    }
    
    // Calculate service coverage
    public function getCoverageReport()
    {
        $residents = $this->getResidentsInArea();
        $totalVillages = $this->district->villages->count();
        $villagesWithResidents = $residents->pluck('village_code')->unique()->count();
        
        return [
            'health_post' => $this->name,
            'district' => $this->district->name,
            'regency' => $this->district->regency->name,
            'total_residents' => $residents->count(),
            'total_villages' => $totalVillages,
            'villages_with_residents' => $villagesWithResidents,
            'coverage_percentage' => ($villagesWithResidents / $totalVillages) * 100,
            'services_offered' => $this->services_offered
        ];
    }
}
```

### Educational Facility

```php
class School extends Model
{
    use WithDistrict;
    
    protected $fillable = ['name', 'district_code', 'school_type', 'student_capacity'];
    
    // Get students from district
    public function getStudentsFromDistrict()
    {
        return Student::whereHas('village', function ($query) {
            $query->where('district_code', $this->district_code);
        })->get();
    }
    
    // Calculate educational coverage
    public function getEducationalMetrics()
    {
        $students = $this->getStudentsFromDistrict();
        $schoolAgePopulation = $this->estimateSchoolAgePopulation();
        
        return [
            'school' => $this->name,
            'district' => $this->district->name,
            'school_type' => $this->school_type,
            'current_students' => $students->count(),
            'student_capacity' => $this->student_capacity,
            'capacity_utilization' => ($students->count() / $this->student_capacity) * 100,
            'estimated_school_age_population' => $schoolAgePopulation,
            'enrollment_rate' => ($students->count() / $schoolAgePopulation) * 100
        ];
    }
}
```

## Geographic Operations

### Service Area Analysis

```php
class DistrictServiceAnalyzer
{
    public function analyzeServiceCoverage($serviceType)
    {
        return District::with(['villages', 'regency.province'])
            ->get()
            ->map(function ($district) use ($serviceType) {
                $services = $this->getServicesInDistrict($district->code, $serviceType);
                
                return [
                    'district' => $district->name,
                    'regency' => $district->regency->name,
                    'province' => $district->regency->province->name,
                    'villages_count' => $district->villages->count(),
                    'services_count' => $services->count(),
                    'service_density' => $services->count() / $district->villages->count(),
                    'coverage_status' => $this->determineCoverageStatus($services->count(), $district->villages->count())
                ];
            });
    }
    
    private function determineCoverageStatus($servicesCount, $villagesCount)
    {
        $ratio = $servicesCount / $villagesCount;
        
        if ($ratio >= 0.5) return 'Well Covered';
        if ($ratio >= 0.2) return 'Adequately Covered';
        if ($ratio > 0) return 'Under Covered';
        return 'Not Covered';
    }
}
```

### Distance-based Service Planning

```php
class ServicePlanner
{
    public function findOptimalDistrictForNewService($targetCoordinates)
    {
        return District::selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(latitude))
            )) AS distance
        ", [$targetCoordinates['lat'], $targetCoordinates['lng'], $targetCoordinates['lat']])
        ->with(['villages', 'regency.province'])
        ->orderBy('distance')
        ->get()
        ->map(function ($district) {
            return [
                'district' => $district->name,
                'regency' => $district->regency->name,
                'province' => $district->regency->province->name,
                'distance_km' => round($district->distance, 2),
                'villages_count' => $district->villages->count(),
                'existing_services' => $this->countExistingServices($district->code),
                'priority_score' => $this->calculatePriorityScore($district)
            ];
        });
    }
}
```

## Next Steps

- **[WithVillage](/id/api/concerns/with-village)** - Village-level relationships
- **[WithRegency](/id/api/concerns/with-regency)** - Regency-level relationships
- **[District Model](/id/api/models/district)** - Complete district model documentation
- **[WithDistricts](/id/api/concerns/with-districts)** - Multiple districts relationships
