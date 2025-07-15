# Laravel Nusa

[![Version](https://img.shields.io/packagist/v/creasi/laravel-nusa?style=flat-square)](https://packagist.org/packages/creasi/laravel-nusa)
[![License](https://img.shields.io/github/license/creasico/laravel-nusa?style=flat-square)](https://github.com/creasico/laravel-nusa/blob/main/LICENSE)
[![Actions Status](https://img.shields.io/github/actions/workflow/status/creasico/laravel-nusa/tests.yml?branch=main&style=flat-square)](https://github.com/creasico/laravel-nusa/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/creasi/laravel-nusa.svg?style=flat-square)](https://packagist.org/packages/creasi/laravel-nusa)

Laravel Nusa provides complete, ready-to-use Indonesian administrative region data for Laravel applications. This package includes all **38** provinces, **514** regencies, **7,285** districts, and **83,762** villages with their hierarchical relationships, postal codes, and geographic coordinates based on **Kepmendagri No 300.2.2-2138 Tahun 2025**.

Laravel Nusa solves the common challenge of integrating Indonesian administrative data into Laravel applications. Unlike other packages, it requires no data migration or seeding—the data is ready immediately after installation.

### What's Included

Instead of manually importing and maintaining large datasets, you get:

- **Instant Setup**: Pre-packaged SQLite database with all data ready to use
- **Official Data**: Sourced from authoritative Indonesian government databases
- **Complete Models**: Province, Regency, District, Village with relationships
- **RESTful API**: Ready-to-use endpoints for all administrative levels  
- **Address Management**: Built-in address system with validation
- **Geographic Data**: Coordinates and postal codes
- **Customizable**: Extend models and customize to fit your needs

## Documentation

For complete usage instructions, API reference, examples, and guides, visit our comprehensive documentation:

**📚 [Laravel Nusa Documentation](https://nusa.creasi.dev/)**

### Quick Start

Install the package via Composer:

```bash
composer require creasi/laravel-nusa
```

Start using it immediately:

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

That's all! The package is ready to use immediately after installation.

## Contributing

For development setup, contribution guidelines, and detailed information about the project structure, see the [full documentation](https://nusa.creasi.dev/en/guide/development).

## Credits
- [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) [![License](https://img.shields.io/github/license/cahyadsn/wilayah?style=flat-square)](https://github.com/cahyadsn/wilayah/blob/master/LICENSE)
- [cahyadsn/wilayah_kodepos](https://github.com/cahyadsn/wilayah_kodepos) [![License](https://img.shields.io/github/license/cahyadsn/wilayah_kodepos?style=flat-square)](https://github.com/cahyadsn/wilayah_kodepos/blob/master/LICENSE)
- [cahyadsn/wilayah_boundaries](https://github.com/cahyadsn/wilayah_boundaries) [![License](https://img.shields.io/github/license/cahyadsn/wilayah_boundaries?style=flat-square)](https://github.com/cahyadsn/wilayah_boundaries/blob/master/LICENSE)
- [w3appdev/kodepos](https://github.com/w3appdev/kodepos)
- [edwardsamuel/Wilayah-Administratif-Indonesia](https://github.com/edwardsamuel/Wilayah-Administratif-Indonesia) [![License](https://img.shields.io/github/license/edwardsamuel/Wilayah-Administratif-Indonesia?style=flat-square)](https://github.com/edwardsamuel/Wilayah-Administratif-Indonesia/blob/master/license.md)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
