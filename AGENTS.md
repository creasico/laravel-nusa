# Laravel Nusa - Agent Guide

## Quick Start
```
composer install   # Install PHP dependencies
pnpm install       # Install docs dependencies
composer test      # Run PHPUnit tests (testbench package:test)
composer fix       # Run Laravel Pint formatter
```

## Structure
- `src/` - Package source (Contracts, Http, Models, ServiceProvider)
- `tests/` - PHPUnit tests organized by feature (Models/, Features/)
- `database/` - nusa.sqlite pre-built database
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
- `declare(strict_types=1)` in all files
- Laravel Pint `--preset laravel` on save (pre-commit hook via `pnpm exec lint-staged`)

## Configuration
- Config: `config/nusa.php` (publish tag: `creasi-config` or `creasi-nusa-config`)
- Connection: 'nusa' (SQLite)
- Model primary key: 'code' (string, non-incrementing)
- No timestamps on admin models

## Model Hierarchy
- `Province` → hasMany(Regency, District, Village)
- `Regency` → belongsTo(Province), hasMany(District, Village)
- `District` → belongsTo(Province, Regency), hasMany(Village)
- `Village` → belongsTo(Province, Regency, District)

## Documentation
- English first, mirror to `id/` for Indonesian
- Code examples in `<augment_code_snippet>` tags
- Dev: `pnpm docs:dev`, Build: `pnpm docs:build`