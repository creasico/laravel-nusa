# What is Laravel Nusa?

Laravel Nusa is a comprehensive Laravel package that provides complete Indonesian administrative region data, including provinces, regencies (kabupaten/kota), districts (kecamatan), and villages (kelurahan/desa). It's designed to be "ready-to-use once installed" without requiring complex setup or data migration processes.

## The Problem It Solves

When building applications that serve Indonesian users, developers often need access to administrative region data for:

- **Address forms** with cascading dropdowns
- **Location-based services** and filtering
- **Shipping and logistics** calculations
- **Government compliance** requirements
- **Data validation** and standardization

Traditionally, this meant:
- ❌ Manually downloading and importing large datasets
- ❌ Running time-consuming database seeders
- ❌ Maintaining data synchronization with official sources
- ❌ Dealing with inconsistent data formats
- ❌ Managing database performance with large datasets

## The Laravel Nusa Solution

Laravel Nusa eliminates these challenges by providing:

- ✅ **Pre-packaged SQLite database** with all data ready to use
- ✅ **Zero-configuration setup** - just install and start using
- ✅ **Automatic updates** from official government sources
- ✅ **Optimized performance** with proper indexing and relationships
- ✅ **Clean API** with Eloquent models and RESTful endpoints

## Key Features

### Complete Administrative Hierarchy

```
Indonesia
└── 34 Provinces (Provinsi)
    └── 514 Regencies (Kabupaten/Kota)
        └── 7,266 Districts (Kecamatan)
            └── 83,467 Villages (Kelurahan/Desa)
```

### Rich Data Attributes

- **Official codes** following government standards
- **Geographic coordinates** for mapping and location services
- **Postal codes** for shipping and logistics
- **Boundary data** for geographic analysis
- **Hierarchical relationships** for efficient querying

### Laravel-Native Integration

- **Eloquent models** with proper relationships
- **Database migrations** for address management
- **Service provider** for automatic configuration
- **Artisan commands** for data management
- **Traits** for easy model integration

## Data Sources

Laravel Nusa integrates data from multiple authoritative sources:

- **[cahyadsn/wilayah](https://github.com/cahyadsn/wilayah)** - Core administrative data
- **[cahyadsn/wilayah_kodepos](https://github.com/cahyadsn/wilayah_kodepos)** - Postal code mappings
- **[cahyadsn/wilayah_boundaries](https://github.com/cahyadsn/wilayah_boundaries)** - Geographic boundaries

These sources are automatically monitored and updated through GitHub Actions workflows.

## Architecture Philosophy

### Zero-Configuration Approach

Unlike other packages that require manual seeding, Laravel Nusa ships with a pre-built SQLite database containing all the data. This means:

- **Instant availability** after installation
- **No impact on your main database** performance
- **Consistent data** across all installations
- **Easy deployment** without additional setup steps

### Privacy-First Design

The package maintains two database versions:

- **Development database** (~407MB) - Includes full coordinate data for development
- **Distribution database** (~10MB) - Coordinates removed for privacy compliance

### Performance Optimization

- **Separate database connection** to avoid conflicts
- **Proper indexing** for fast queries
- **Efficient relationships** using foreign keys
- **Pagination support** for large datasets

## Use Cases

Laravel Nusa is perfect for:

### E-commerce Applications
- Address forms with real-time validation
- Shipping cost calculations
- Regional product availability

### Government Systems
- Citizen registration forms
- Administrative reporting
- Compliance with official standards

### Location-Based Services
- Store locators
- Service area mapping
- Geographic analytics

### Data Analytics
- Regional performance analysis
- Demographic studies
- Market research

## Next Steps

Ready to get started? Check out our [Getting Started](/guide/getting-started) guide to install and configure Laravel Nusa in your application.
