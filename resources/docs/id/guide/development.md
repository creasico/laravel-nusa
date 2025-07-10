# Setup Pengembangan

Panduan lengkap untuk menyiapkan lingkungan pengembangan Laravel Nusa, termasuk instalasi dependensi, konfigurasi development, testing, dan kontribusi ke proyek open source.

## Setup Environment Development

### Prasyarat

Sebelum menyiapkan environment development, pastikan Anda memiliki:

- **PHP 8.1+** with required extensions
- **Composer** for dependency management
- **Node.js & npm** for frontend tooling
- **Git** for version control
- **SQLite** for the default database

### Cloning the Repository

```bash
# Clone the repository
git clone https://github.com/creasico/laravel-nusa.git
cd laravel-nusa

# Install PHP dependencies
composer install

# Install Node.js dependencies (for documentation)
npm install
```

### Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database (SQLite is default)
touch database/nusa.sqlite
```

### Database Setup

```bash
# Run migrations
php artisan migrate

# Seed with sample data (if available)
php artisan db:seed

# Or download official data
php artisan nusa:download
```

## Development Commands

### Available Artisan Commands

```bash
# Check Laravel Nusa installation
php artisan nusa:check

# Download latest data
php artisan nusa:download

# Update existing data
php artisan nusa:update

# Import data from custom source
php artisan nusa:import --source=custom.csv

# Clear Laravel Nusa caches
php artisan nusa:cache:clear

# Validate configuration
php artisan nusa:config:validate
```

### Development Server

```bash
# Start Laravel development server
php artisan serve

# Start with custom host and port
php artisan serve --host=0.0.0.0 --port=8080
```

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
composer test -- --testsuite=Feature

# Run with coverage
composer test-coverage

# Run specific test file
php artisan test tests/Feature/ProvinceTest.php
```

### Test Configuration

```php
// phpunit.xml
<phpunit>
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

### Writing Tests

```php
// tests/Feature/ProvinceApiTest.php
class ProvinceApiTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_get_all_provinces()
    {
        Province::factory()->count(5)->create();
        
        $response = $this->getJson('/nusa/provinces');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['code', 'name', 'latitude', 'longitude']
                ],
                'meta' => ['current_page', 'total']
            ]);
    }
    
    public function test_can_search_provinces()
    {
        Province::factory()->create(['name' => 'Jawa Tengah']);
        Province::factory()->create(['name' => 'Jawa Barat']);
        
        $response = $this->getJson('/nusa/provinces?search=tengah');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
```

## Code Quality

### Code Style

```bash
# Install PHP CS Fixer
composer require --dev friendsofphp/php-cs-fixer

# Fix code style
vendor/bin/php-cs-fixer fix

# Check code style
vendor/bin/php-cs-fixer fix --dry-run --diff
```

### Static Analysis

```bash
# Install PHPStan
composer require --dev phpstan/phpstan

# Run static analysis
vendor/bin/phpstan analyse

# With configuration
vendor/bin/phpstan analyse --configuration=phpstan.neon
```

### Pre-commit Hooks

```bash
# Install pre-commit hooks
composer install-hooks

# Or manually create .git/hooks/pre-commit
#!/bin/sh
vendor/bin/php-cs-fixer fix --dry-run --diff
vendor/bin/phpstan analyse
composer test
```

## Documentation Development

### VitePress Setup

```bash
# Navigate to docs directory
cd resources/docs

# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

### Documentation Structure

```
resources/docs/
├── .vitepress/
│   ├── config.mjs          # VitePress configuration
│   └── theme/              # Custom theme
├── guide/                  # English guides
├── id/                     # Indonesian content
├── api/                    # API reference
├── examples/               # Usage examples
└── public/                 # Static assets
```

## Contributing

### Contribution Guidelines

1. **Fork the repository** on GitHub
2. **Create a feature branch** from `main`
3. **Make your changes** with proper tests
4. **Ensure code quality** passes all checks
5. **Submit a pull request** with clear description

### Pull Request Process

```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes and commit
git add .
git commit -m "Add new feature"

# Push to your fork
git push origin feature/new-feature

# Create pull request on GitHub
```

### Commit Message Format

```
type(scope): description

[optional body]

[optional footer]
```

Examples:
```
feat(api): add province statistics endpoint
fix(models): resolve village relationship issue
docs(guide): update installation instructions
test(unit): add province model tests
```

## Debugging

### Debug Configuration

```php
// config/app.php
'debug' => env('APP_DEBUG', true),

// Enable query logging
DB::enableQueryLog();

// Log queries
Log::info(DB::getQueryLog());
```

### Common Debug Commands

```bash
# Clear all caches
php artisan optimize:clear

# View routes
php artisan route:list

# Check configuration
php artisan config:show

# View logs
tail -f storage/logs/laravel.log
```

### Debugging Tools

```bash
# Install Laravel Debugbar
composer require --dev barryvdh/laravel-debugbar

# Install Telescope (for advanced debugging)
composer require laravel/telescope
php artisan telescope:install
```

## Next Steps

- **[Troubleshooting](/id/guide/troubleshooting)** - Common development issues
- **[API Reference](/id/api/overview)** - Complete API documentation
- **[Contributing Guide](https://github.com/creasico/laravel-nusa/blob/main/CONTRIBUTING.md)** - Detailed contribution guidelines
- **[GitHub Repository](https://github.com/creasico/laravel-nusa)** - Source code and issues
