# Development Setup

This guide covers setting up Laravel Nusa for development, contributing, and working with upstream data sources.

## Prerequisites

### Required Software

- **PHP** ≥ 8.2 with extensions:
  - `ext-sqlite3` - For SQLite database support
  - `ext-json` - For JSON handling
  - `ext-mbstring` - For string manipulation
- **Node.js** ≥ 20 with pnpm package manager
- **Git** with submodule support
- **Docker** (recommended) or local MySQL server
- **SQLite CLI Tool** with `sqldiff` for SQLite database management

### Development Tools

- **Composer** for PHP dependency management
- **pnpm** for Node.js dependencies (faster than npm)
- **Docker Compose** for containerized development environment

## Quick Start

### 1. Clone Repository

```bash
# Clone with submodules (important!)
git clone --recurse-submodules https://github.com/creasico/laravel-nusa.git
cd laravel-nusa

# If you forgot --recurse-submodules
git submodule update --init --recursive
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
pnpm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp workbench/.env.example workbench/.env

# Edit configuration as needed
nano workbench/.env
```

### 4. Database Setup

**Option A: Docker (Recommended)**

Laravel Nusa provides a complete Docker setup for development:

```bash
# Start Docker services
composer upstream:up

# Data is automatically imported by upstream:up
# To manually import: composer testbench nusa:import

# Generate distribution database
composer testbench nusa:dist
```

**Option B: Local MySQL**

```bash
# Create required databases
mysql -e 'CREATE DATABASE testing;'
mysql -e 'CREATE DATABASE nusantara;'

# Import data
composer testbench nusa:import --fresh
```

## Docker Development Environment

### Available Commands

Laravel Nusa includes convenient Composer scripts for Docker management:

```bash
# Start services (MySQL + phpMyAdmin)
composer upstream:up

# Stop services
composer upstream:down

# Import fresh data from upstream
composer testbench nusa:import --fresh

# Create distribution database
composer testbench nusa:dist

# Generate statistics
composer testbench nusa:stat

# View logs (using docker compose)
composer upstream logs

# Access MySQL CLI (using docker compose)
composer upstream exec mysql mysql -u root -psecret nusantara

# Generate static files
composer testbench nusa:generate-static
```

### Docker Services

The development environment includes:

- **MySQL 8.0** - Main database server
- **phpMyAdmin** - Web-based database administration
- **Volumes** - Persistent data storage

Access phpMyAdmin at: `http://localhost:8080`
- Username: `root`
- Password: `secret`

### Docker Configuration

The Docker setup is defined in `docker-compose.yml`:

```yaml
services:
  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: nusantara
    volumes:
      - mysql_data:/var/lib/mysql

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "8080:80"
    environment:
      PMA_HOST: mysql
      PMA_USER: root
      PMA_PASSWORD: secret
```

## Available Commands

### Composer Scripts

Laravel Nusa provides several composer scripts for development:

```bash
# Development environment
composer upstream:up          # Start Docker services + import data
composer upstream:down        # Stop Docker services + cleanup
composer upstream [args]      # Pass arguments to docker-compose

# Testing and quality
composer test                 # Run test suite
composer fix                  # Fix code style with Laravel Pint
composer testbench [args]     # Run testbench commands
composer testbench:purge      # Purge workbench skeleton
composer tinker               # Start tinker session
```

### Nusa Commands

Available via `composer testbench nusa:*`:

#### `nusa:import`
Import data from upstream sources:
```bash
composer testbench nusa:import           # Import data from upstream
composer testbench nusa:import --fresh   # Recreate database + import
composer testbench nusa:import --dist    # Import + create distribution database
```

#### `nusa:dist`
Create distribution database (removes coordinates for privacy):
```bash
composer testbench nusa:dist             # Create distribution database
composer testbench nusa:dist --force     # Force overwrite existing distribution
```

#### `nusa:stat`
Generate database statistics and show changes:
```bash
composer testbench nusa:stat             # Show database stats and changes
```

#### `nusa:generate-static`
Generate static files (CSV, JSON formats):
```bash
composer testbench nusa:generate-static  # Generate static data files
```

## Data Management

### Understanding Data Sources

Laravel Nusa integrates data from multiple upstream repositories:

```
workbench/submodules/
├── wilayah/              # Core administrative data
├── wilayah_kodepos/      # Postal code mappings  
└── wilayah_boundaries/   # Geographic boundaries
```

### Import Process

The import process pulls data from upstream Git submodules and processes it:

```bash
# Full import process (recommended)
composer testbench nusa:import --fresh

# Import without recreating database
composer testbench nusa:import

# Import and create distribution database
composer testbench nusa:import --dist
```

::: tip Import Options
The import command only supports `--fresh` and `--dist` options. It automatically imports all administrative levels (provinces, regencies, districts, villages) from the upstream sources.
:::

### Distribution Database

Create privacy-compliant distribution database (removes coordinate data):

```bash
# Generate distribution database
composer testbench nusa:dist

# Force overwrite existing distribution database
composer testbench nusa:dist --force
```

::: warning Privacy Compliance
The distribution database automatically removes all coordinate data to ensure privacy compliance. This is the database included in the package distribution.
:::

### Data Statistics

View database statistics and changes:

```bash
# Show database statistics and changes from upstream
composer testbench nusa:stat
```

This command compares the current distribution database with the development database to show what has changed.

## Development Workflow

### 1. Making Changes

```bash
# Create feature branch
git checkout -b feature/your-feature

# Make your changes
# ... edit files ...

# Run tests
composer test

# Fix code style
composer fix
```

### 2. Testing

```bash
# Run full test suite
composer test

# Run specific test
vendor/bin/phpunit tests/Models/ProvinceTest.php

# Run with coverage
composer test -- --coverage-html tests/reports/html

# Test specific feature
vendor/bin/phpunit --filter testProvinceRelationships
```

### 3. Code Quality

```bash
# Fix code style with Laravel Pint
composer fix

# Check style without fixing
vendor/bin/pint --test

# Run static analysis (if configured)
composer analyse
```

### 4. Documentation

```bash
# Start documentation server
npm run docs:dev

# Build documentation
npm run docs:build

# Preview built documentation
npm run docs:preview
```

## Working with Submodules

### Updating Upstream Data

```bash
# Update all submodules to latest
git submodule update --remote

# Update specific submodule
git submodule update --remote workbench/submodules/wilayah

# Commit submodule updates
git add workbench/submodules
git commit -m "chore: update upstream data sources"
```

### Submodule Management

```bash
# Check submodule status
git submodule status

# Initialize submodules (if needed)
git submodule init

# Update to specific commit
cd workbench/submodules/wilayah
git checkout specific-commit-hash
cd ../../..
git add workbench/submodules/wilayah
git commit -m "chore: pin wilayah to specific version"
```

## Database Development

### Accessing Databases

```bash
# SQLite (distribution database)
sqlite3 database/nusa.sqlite

# MySQL (development database)
mysql -h 127.0.0.1 -u root -psecret nusantara

# Via Docker
composer upstream exec mysql mysql -u root -psecret nusantara
```

### Database Inspection

```bash
# Check table structures
composer testbench tinker
>>> Schema::connection('nusa')->getColumnListing('provinces')

# Count records
>>> \Creasi\Nusa\Models\Province::count()

# Test relationships
>>> \Creasi\Nusa\Models\Province::find('33')->regencies->count()
```

### Performance Testing

```bash
# Test query performance
composer testbench tinker
>>> DB::connection('nusa')->enableQueryLog()
>>> \Creasi\Nusa\Models\Village::paginate(100)
>>> DB::connection('nusa')->getQueryLog()
```

## Debugging

### Enable Debug Mode

```php
// In workbench/.env
APP_DEBUG=true
LOG_LEVEL=debug

// Enable query logging
DB_LOG_QUERIES=true
```

### Common Debug Commands

```bash
# Check configuration
composer testbench config:show database.connections.nusa

# Test database connection
composer testbench tinker
>>> DB::connection('nusa')->getPdo()

# Check routes
composer testbench route:list | grep nusa

# Clear caches
composer testbench config:clear
composer testbench route:clear
```

## Performance Optimization

### Development Database

```bash
# Use development database with coordinates
cp database/nusa.dev.sqlite database/nusa.sqlite

# Or create from source
composer testbench nusa:import --fresh
composer testbench nusa:dist --force
```

### Query Optimization

```php
// Enable query logging for analysis
DB::connection('nusa')->listen(function ($query) {
    Log::debug('Nusa Query', [
        'sql' => $query->sql,
        'bindings' => $query->bindings,
        'time' => $query->time
    ]);
});
```

## Troubleshooting

### Common Issues

#### Submodules Not Initialized

```bash
# Error: submodule directories are empty
git submodule update --init --recursive
```

#### Docker Permission Issues

```bash
# Fix Docker permissions on Linux
sudo chown -R $USER:$USER database/
sudo chmod -R 755 database/
```

#### Database Connection Errors

```bash
# Check Docker services
docker-compose ps

# Restart services
composer upstream:down
composer upstream:up

# Check MySQL logs
composer upstream:logs mysql
```

#### Memory Issues During Import

```bash
# Increase PHP memory limit
php -d memory_limit=2G vendor/bin/testbench nusa:import -- --fresh
```

### Getting Help

1. **Check logs**: `storage/logs/laravel.log`
2. **GitHub Issues**: [Report bugs](https://github.com/creasico/laravel-nusa/issues)
3. **Discussions**: [Community support](https://github.com/orgs/creasico/discussions)
4. **Documentation**: Check this documentation first

## Next Steps

After setting up your development environment:

1. **Explore the codebase** - Understand the project structure
2. **Run tests** - Ensure everything works correctly
3. **Read contributing guidelines** - Learn the development workflow
4. **Start contributing** - Pick an issue or suggest improvements
