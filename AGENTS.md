# Laravel Nusa - Agent Guide

Laravel Nusa is a Laravel package that provides Indonesia's administrative divisions (provinces, regencies, districts, and villages) with coordinates and postal codes, using a pre-compiled SQLite database. The data is imported from upstream SQL sources (cahyadsn's wilayah & postal codes).

## Quick Start
```
composer install      # Install PHP dependencies
pnpm install          # Install docs dependencies
composer upstream:up  # Start Docker + import fresh data (required before composer test)
composer test         # Run PHPUnit tests (testbench package:test)
composer fix          # Run Laravel Pint formatter
```

## Structure
- `src/` - Package source (Contracts, Http, Models, ServiceProvider, Support)
- `tests/` - PHPUnit tests organized by category/feature (Models/, Features/, Fixtures/)
- `database/` - SQLite database files (`nusa.sqlite`, etc.)
- `resources/static/` - Static export data (CSV, JSON, GeoJSON)
- `resources/docs/` - VitePress documentation (bilingual EN/ID)

## Development Commands
```
composer upstream:up             # Start Docker + import fresh data
composer upstream:down           # Stop Docker + purge
composer testbench nusa:import   # Import from upstream sources
composer testbench nusa:dist     # Create distribution database (no coordinates)
composer testbench nusa:generate-static  # Generate static files
composer tinker                  # Start Tinker
```

## Testing
- Use `composer test` (runs testbench package:test)
- Tests use `Creasi\Tests\TestCase` with `WithWorkbench` trait
- PHPUnit attributes: `#[Test]`, `#[Group('models')]`, etc.

## Code Standards
- PHP ≥ 8.2, Laravel ≥ 9.0–13.0
- `declare(strict_types=1)` in all files (auto-enforced by Pint's `declare_strict_types` rule)
- Laravel Pint is configured in `pint.json` (preset `laravel` + extra rules); run via `composer fix`, and it also runs automatically on commit (pre-commit hook via `pnpm exec lint-staged`)

## Configuration
- Config: `config/nusa.php` (published to `config/creasi/nusa.php` via tag `creasi-config` or `creasi-nusa-config`)
- Translations: `resources/lang` (published via tag `creasi-lang`)
- Migrations: `database/migrations` (published via tag `creasi-migrations` for address tables)
- Connection: 'nusa' (SQLite)
- Model primary key: 'code' (string, non-incrementing)
- No timestamps on admin models

## Model Hierarchy
- `Province` → hasMany(Regency, District, Village)
- `Regency` → belongsTo(Province), hasMany(District, Village)
- `District` → belongsTo(Province, Regency), hasMany(Village)
- `Village` → belongsTo(Province, Regency, District)

## API Routes
- Route definitions: `routes/nusa.php` (published/loaded automatically under prefix `nusa` by default)
- Controllers: `src/Http/Controllers/` (`ProvinceController`, `RegencyController`, `DistrictController`, `VillageController`, and a nested `ApiController`)

## Documentation
- English first, mirror to `id/` for Indonesian
- Dev: `pnpm docs:dev`, Build: `pnpm docs:build`

## CI
- GitHub Actions in `.github/workflows/` are pinned to full commit SHAs with a trailing version comment (e.g. `uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1 # v7.0.1`); when adding or updating an action, always pin it to its latest stable release's SHA

## Operational Mandates

1.  **Metadata Management**: ALL AI-generated metadata (plans, specs, and design documents) MUST be stored exclusively in the `.agents/` directory (e.g., `.agents/plans/`, `.agents/specs/`). Do not use any other directory for persistent or temporary agent artifacts.
