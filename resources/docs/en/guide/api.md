# RESTful API

Laravel Nusa provides a comprehensive RESTful API for accessing Indonesian administrative data. This guide covers the essential concepts and common usage patterns for integrating the API into your applications.

## Quick Start

The API is automatically available after installation at the `/nusa` endpoint:

```bash
# Get all provinces
curl http://your-app.test/nusa/provinces

# Get specific province
curl http://your-app.test/nusa/provinces/33

# Search provinces
curl "http://your-app.test/nusa/provinces?search=jawa"
```

## Configuration

### Enable/Disable API Routes

The API routes are enabled by default. You can control this behavior:

```dotenv
# Disable API routes
CREASI_NUSA_ROUTES_ENABLE=false

# Change route prefix
CREASI_NUSA_ROUTES_PREFIX=api/indonesia
```

### Security Considerations

Since the API endpoints are **public by default**, consider these security measures for production:

#### CORS Configuration

Configure CORS for frontend applications:

```php
// config/cors.php
'paths' => ['api/*', 'nusa/*'],
'allowed_methods' => ['GET'],
'allowed_origins' => ['https://yourdomain.com'],
```

#### Rate Limiting

Add rate limiting to prevent abuse:

```php
// routes/api.php
Route::middleware(['throttle:100,1'])->group(function () {
    // Your custom API routes
});
```

## Common Integration Patterns

### Frontend Applications

For building address forms and location-based features:

```javascript
// Fetch provinces for dropdown
async function loadProvinces() {
    const response = await fetch('/nusa/provinces');
    const data = await response.json();
    return data.data;
}

// Cascading dropdown implementation
async function loadRegencies(provinceCode) {
    const response = await fetch(`/nusa/provinces/${provinceCode}/regencies`);
    const data = await response.json();
    return data.data;
}
```

### Backend Services

For server-side data processing and validation:

```php
use GuzzleHttp\Client;

class LocationService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => config('app.url')]);
    }

    public function validateAddress(array $addressData): bool
    {
        $response = $this->client->get("nusa/villages/{$addressData['village_code']}");

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $village = json_decode($response->getBody(), true)['data'];

        return $village['district_code'] === $addressData['district_code'] &&
               $village['regency_code'] === $addressData['regency_code'] &&
               $village['province_code'] === $addressData['province_code'];
    }
}
```

## Performance Optimization

### Caching Strategy

Implement caching for frequently accessed data:

```php
use Illuminate\Support\Facades\Cache;

class CachedLocationService
{
    public function getProvinces()
    {
        return Cache::remember('nusa.provinces', 3600, function () {
            $response = Http::get('/nusa/provinces');
            return $response->json()['data'];
        });
    }
}
```

### Pagination Best Practices

Always use pagination for large datasets:

```javascript
// Good: Use pagination
const response = await fetch('/nusa/villages?per_page=50&page=1');

// Avoid: Loading all villages at once
const response = await fetch('/nusa/villages'); // 83,467 records!
```

## Custom Implementation

If you need more control over the API behavior, you can disable the default routes and create your own:

### Disable Default Routes

```dotenv
CREASI_NUSA_ROUTES_ENABLE=false
```

### Create Custom API Routes

```php
// routes/api.php
use Creasi\Nusa\Models\Province;

Route::prefix('v1/indonesia')->middleware(['auth:api', 'throttle:100,1'])->group(function () {
    Route::get('provinces', function (Request $request) {
        $query = Province::query();

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        return $query->paginate($request->get('per_page', 15));
    });

    Route::get('provinces/{province}/regencies', function (string $code) {
        $province = Province::findOrFail($code);
        return $province->regencies()->paginate(15);
    });
});
```

## Complete API Reference

For detailed information about all available endpoints, parameters, and response formats, see the comprehensive [API Reference](/en/api/overview) documentation.

The API reference includes:

- **Complete endpoint listing** with all available routes
- **Detailed response formats** and data structures
- **Query parameters** for search, filtering, and pagination
- **Error handling** and status codes
- **Usage examples** in multiple programming languages
- **Data attributes** for each administrative level
