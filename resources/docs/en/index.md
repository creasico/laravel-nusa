---
layout: home

hero:
  name: "Laravel Nusa"
  text: "Indonesian Administrative Data"
  tagline: Ready-to-use Indonesian provinces, regencies, districts, and villages data for Laravel applications
  image:
    src: /logo.svg
    alt: Laravel Nusa
  actions:
    - theme: brand
      text: Get Started
      link: /en/guide/getting-started
    - theme: alt
      text: View on GitHub
      link: https://github.com/creasico/laravel-nusa

features:
  - icon: 🗺️
    title: Complete Administrative Data
    details: All 38 provinces, 514 regencies, 7,285 districts, and 83,762 villages with official codes and names
  - icon: 🚀
    title: Zero Configuration
    details: Ready-to-use SQLite database included. No seeding or migration required - just install and use
  - icon: 🌐
    title: RESTful API
    details: Built-in API endpoints with pagination, search, and filtering for all administrative levels
  - icon: 📍
    title: Geographic Data
    details: Includes coordinates, boundaries, and postal codes for comprehensive location services
  - icon: 🔧
    title: Laravel Integration
    details: Eloquent models with relationships, traits for address management, and Laravel-native features
  - icon: 🔄
    title: Auto-Updated
    details: Automatically synchronized with official government data sources through automated workflows
---

## Why Laravel Nusa?

Laravel Nusa solves the common challenge of integrating Indonesian administrative data into Laravel applications. Instead of manually importing and maintaining large datasets, you get:

- **Instant Setup**: Pre-packaged SQLite database with all data ready to use
- **Official Data**: Sourced from authoritative government databases
- **Performance**: Optimized database structure with proper indexing
- **Maintenance**: Automated updates when official data changes
- **Privacy**: Distribution version excludes sensitive coordinate data


### 🏢 Best Suited For

- **E-Commerce**: Optimize delivery routes and define efficient shipping zones to streamline logistics.
- **Healthcare**: Manage facility coverage and understand patient demographics for better service planning
- **Public Services**: Empower citizen management and simplify administrative reporting with spatial insights.
- **Business Applications**: Analyze regional performance and plan strategic expansion with location-based analytics

## 🚀 Quick Start

Install the package via Composer:

```bash
composer require creasi/laravel-nusa
```

Start using immediately:

```php
use Creasi\Nusa\Models\Province;

// Get all provinces
$provinces = Province::all();

// Search by name or code
$jateng = Province::search('Jawa Tengah')->first();
$jateng = Province::search('33')->first();

// Get related data
$regencies = $jateng->regencies;
$districts = $jateng->districts;
$villages = $jateng->villages;
```

## 🌐 API Example

Access data through clean RESTful endpoints:

```http
# Get all provinces
GET /nusa/provinces

# Get specific province
GET /nusa/provinces/33

# Get regencies in a province
GET /nusa/provinces/33/regencies

# Search with query parameters
GET /nusa/villages?search=jakarta&codes[]=31.71
```

## 📍 Address Management

Easily integrate address functionality into your models:

```php
use Creasi\Nusa\Contracts\HasAddresses;
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model implements HasAddresses
{
    use WithAddresses;
}

// Now your users can have addresses
$user->addresses()->create([
    'province_code' => '33',
    'regency_code' => '33.75',
    'district_code' => '33.75.01',
    'village_code' => '33.75.01.1002',
    'address_line' => 'Jl. Merdeka No. 123'
]);
```
