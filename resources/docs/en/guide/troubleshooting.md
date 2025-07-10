# Troubleshooting

This guide helps you resolve common issues when installing, configuring, or using Laravel Nusa.

## Installation Issues

### PHP SQLite Extension Missing

**Error**: `could not find driver` or `PDO SQLite driver not found`

**Solution**: Install the PHP SQLite extension:

```bash
# Ubuntu/Debian
sudo apt-get install php8.2-sqlite3

# CentOS/RHEL/Fedora
sudo yum install php-sqlite3
# or
sudo dnf install php-sqlite3

# macOS with Homebrew
brew install php@8.2

# Windows (uncomment in php.ini)
extension=pdo_sqlite
extension=sqlite3
```

**Verify installation**:
```bash
php -m | grep sqlite
# Should show: pdo_sqlite, sqlite3
```

### Composer Installation Fails

**Error**: `Package not found` or `Version conflicts`

**Solutions**:

1. **Clear Composer cache**:
   ```bash
   composer clear-cache
   composer install --no-cache
   ```

2. **Update Composer**:
   ```bash
   composer self-update
   ```

3. **Check PHP version**:
   ```bash
   php -v
   # Ensure PHP >= 8.2
   ```

4. **Install with specific version**:
   ```bash
   composer require creasi/laravel-nusa:^0.1
   ```

### Laravel Version Compatibility

**Error**: `Package requires Laravel X.Y but you have Z.A`

**Solution**: Check compatibility matrix:

| Laravel Nusa | Laravel Versions |
|--------------|------------------|
| 0.1.x        | 9.0 - 12.x       |

Update Laravel or use compatible version:
```bash
# Update Laravel
composer update laravel/framework

# Or install compatible Nusa version
composer require creasi/laravel-nusa:^0.1
```

## Database Issues

### SQLite Database Not Found

**Error**: `database disk image is malformed` or `no such file`

**Solutions**:

1. **Check file exists**:
   ```bash
   ls -la vendor/creasi/laravel-nusa/database/nusa.sqlite
   ```

2. **Reinstall package**:
   ```bash
   composer remove creasi/laravel-nusa
   composer require creasi/laravel-nusa
   ```

3. **Check file permissions**:
   ```bash
   chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
   ```

### Database Connection Errors

**Error**: `SQLSTATE[HY000] [14] unable to open database file`

**Solutions**:

1. **Check database path**:
   ```php
   // In tinker
   config('database.connections.nusa.database')
   ```

2. **Verify file permissions**:
   ```bash
   # Make directory writable
   chmod 755 vendor/creasi/laravel-nusa/database/
   
   # Make file readable
   chmod 644 vendor/creasi/laravel-nusa/database/nusa.sqlite
   ```

3. **Test connection**:
   ```bash
   php artisan tinker
   >>> DB::connection('nusa')->getPdo()
   ```

### Foreign Key Constraint Errors

**Error**: `FOREIGN KEY constraint failed`

**Solution**: Enable foreign key constraints:

```php
// In config/database.php
'nusa' => [
    'driver' => 'sqlite',
    'database' => database_path('nusa.sqlite'),
    'foreign_key_constraints' => true, // Add this
],
```

## Configuration Issues

### Routes Not Working

**Error**: `Route [nusa.provinces.index] not defined`

**Solutions**:

1. **Check if routes are enabled**:
   ```bash
   # In .env
   CREASI_NUSA_ROUTES_ENABLE=true
   ```

2. **Clear route cache**:
   ```bash
   php artisan route:clear
   php artisan config:clear
   ```

3. **Verify service provider is loaded**:
   ```bash
   php artisan config:show app.providers | grep Nusa
   ```

4. **Check route registration**:
   ```bash
   php artisan route:list | grep nusa
   ```

### API Endpoints Return 404

**Error**: `404 Not Found` for `/nusa/provinces`

**Solutions**:

1. **Check route prefix**:
   ```bash
   # In .env
   CREASI_NUSA_ROUTES_PREFIX=nusa
   ```

2. **Verify web server configuration**:
   ```apache
   # Apache .htaccess
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

3. **Test with full URL**:
   ```bash
   curl http://your-app.test/index.php/nusa/provinces
   ```

### Configuration Cache Issues

**Error**: Configuration changes not taking effect

**Solution**: Clear configuration cache:

```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
```

## Model and Query Issues

### Model Not Found Errors

**Error**: `Class 'Creasi\Nusa\Models\Province' not found`

**Solutions**:

1. **Check autoloader**:
   ```bash
   composer dump-autoload
   ```

2. **Verify package installation**:
   ```bash
   composer show creasi/laravel-nusa
   ```

3. **Check namespace import**:
   ```php
   use Creasi\Nusa\Models\Province; // Add this
   ```

### Empty Query Results

**Error**: Models return empty collections

**Solutions**:

1. **Test database connection**:
   ```bash
   php artisan tinker
   >>> DB::connection('nusa')->table('provinces')->count()
   ```

2. **Check table names**:
   ```bash
   >>> Schema::connection('nusa')->getTableListing()
   ```

3. **Verify data exists**:
   ```bash
   sqlite3 vendor/creasi/laravel-nusa/database/nusa.sqlite
   .tables
   SELECT COUNT(*) FROM provinces;
   ```

### Relationship Errors

**Error**: `Call to undefined relationship`

**Solution**: Check relationship methods exist:

```php
// Correct usage
$province = Province::find('33');
$regencies = $province->regencies; // Not regency()

// Available relationships
$province->regencies;  // HasMany
$province->districts;  // HasMany  
$province->villages;   // HasMany
```

## Performance Issues

### Slow Query Performance

**Problem**: Queries taking too long

**Solutions**:

1. **Use pagination**:
   ```php
   // Good
   Village::paginate(50);
   
   // Avoid
   Village::all(); // 83,467 records!
   ```

2. **Select specific columns**:
   ```php
   Province::select('code', 'name')->get();
   ```

3. **Use eager loading**:
   ```php
   Province::with('regencies')->get();
   ```

4. **Check indexes**:
   ```sql
   EXPLAIN QUERY PLAN SELECT * FROM villages WHERE province_code = '33';
   ```

### Memory Limit Exceeded

**Error**: `Fatal error: Allowed memory size exhausted`

**Solutions**:

1. **Increase memory limit**:
   ```bash
   php -d memory_limit=512M artisan your:command
   ```

2. **Use chunking for large datasets**:
   ```php
   Village::chunk(1000, function ($villages) {
       foreach ($villages as $village) {
           // Process village
       }
   });
   ```

3. **Optimize queries**:
   ```php
   // Use select() to limit columns
   Village::select('code', 'name')->chunk(1000, $callback);
   ```

## Development Issues

### Submodule Problems

**Error**: `Submodule path 'workbench/submodules/wilayah': checked out 'abc123'`

**Solutions**:

1. **Initialize submodules**:
   ```bash
   git submodule update --init --recursive
   ```

2. **Update submodules**:
   ```bash
   git submodule update --remote
   ```

3. **Reset submodules**:
   ```bash
   git submodule deinit --all
   git submodule update --init --recursive
   ```

### Docker Issues

**Error**: `Cannot connect to the Docker daemon`

**Solutions**:

1. **Start Docker service**:
   ```bash
   # Linux
   sudo systemctl start docker
   
   # macOS
   open -a Docker
   ```

2. **Check Docker Compose**:
   ```bash
   docker-compose --version
   ```

3. **Reset Docker environment**:
   ```bash
   composer upstream:down
   docker system prune -f
   composer upstream:up
   ```

### Import Command Fails

**Error**: Data import commands fail

**Solutions**:

1. **Check database connection**:
   ```bash
   composer testbench tinker
   >>> DB::connection()->getPdo()
   ```

2. **Verify submodules**:
   ```bash
   ls -la workbench/submodules/
   ```

3. **Run with verbose output**:
   ```bash
   composer testbench nusa:import -- --fresh -v
   ```

4. **Check disk space**:
   ```bash
   df -h
   ```

## API Issues

### CORS Errors

**Error**: `Access to fetch at 'http://localhost/nusa/provinces' from origin 'http://localhost:3000' has been blocked by CORS policy`

**Solution**: Configure CORS in Laravel:

```php
// config/cors.php
'paths' => ['api/*', 'nusa/*'],
'allowed_methods' => ['GET'],
'allowed_origins' => ['*'], // Or specific domains
'allowed_headers' => ['*'],
```

### Rate Limiting Issues

**Error**: `429 Too Many Requests`

**Solutions**:

1. **Check rate limits**:
   ```php
   // In routes or middleware
   Route::middleware(['throttle:60,1'])->group(function () {
       // Your routes
   });
   ```

2. **Increase limits**:
   ```php
   Route::middleware(['throttle:1000,1'])->group(function () {
       // Higher limit routes
   });
   ```

### JSON Response Issues

**Error**: Invalid JSON responses

**Solutions**:

1. **Check Accept header**:
   ```bash
   curl -H "Accept: application/json" http://your-app.test/nusa/provinces
   ```

2. **Verify API routes**:
   ```bash
   php artisan route:list | grep nusa
   ```

## Getting Help

### Debug Information

When reporting issues, include:

```bash
# System information
php -v
composer --version
laravel --version

# Package information
composer show creasi/laravel-nusa

# Laravel configuration
php artisan about

# Database connection test
php artisan tinker
>>> DB::connection('nusa')->getPdo()
>>> \Creasi\Nusa\Models\Province::count()
```

### Log Files

Check these log files for errors:

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# PHP logs
tail -f /var/log/php_errors.log
```

### Support Channels

1. **Documentation** - Check this documentation first
2. **GitHub Issues** - [Report bugs](https://github.com/creasico/laravel-nusa/issues)
3. **GitHub Discussions** - [Community support](https://github.com/orgs/creasico/discussions)
4. **Stack Overflow** - Tag with `laravel-nusa`

### Creating Bug Reports

Include this information:

- **Environment**: PHP version, Laravel version, OS
- **Package version**: `composer show creasi/laravel-nusa`
- **Error message**: Full error with stack trace
- **Steps to reproduce**: Minimal code example
- **Expected behavior**: What should happen
- **Actual behavior**: What actually happens

This troubleshooting guide covers the most common issues. If you encounter a problem not listed here, please check the GitHub issues or create a new one with detailed information.
