# RESTful API

Laravel Nusa provides a comprehensive RESTful API for accessing Indonesian administrative data. The API is automatically available after installation and follows Laravel conventions for consistency and ease of use.

## Quick Start

The API is available immediately after installation at the `/nusa` endpoint:

```bash
# Get all provinces
curl http://your-app.test/nusa/provinces

# Get specific province
curl http://your-app.test/nusa/provinces/33

# Search provinces
curl "http://your-app.test/nusa/provinces?search=jawa"
```

## Base Configuration

### Default Settings

- **Base URL**: `/nusa`
- **Authentication**: None (public by default)
- **Rate Limiting**: None (configurable)
- **Response Format**: JSON with pagination

### Customization

You can customize the API behavior:

```dotenv
# Disable API routes
CREASI_NUSA_ROUTES_ENABLE=false

# Change route prefix
CREASI_NUSA_ROUTES_PREFIX=api/indonesia
```

## Response Format

All API responses follow a consistent structure:

### Collection Response

```json
{
  "data": [
    {
      "code": "33",
      "name": "Jawa Tengah",
      "latitude": -6.9934809206806,
      "longitude": 110.42024335421
    }
  ],
  "links": {
    "first": "http://localhost:8000/nusa/provinces?page=1",
    "last": "http://localhost:8000/nusa/provinces?page=3",
    "prev": null,
    "next": "http://localhost:8000/nusa/provinces?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 34
  }
}
```

### Single Resource Response

```json
{
  "data": {
    "code": "33",
    "name": "Jawa Tengah",
    "latitude": -6.9934809206806,
    "longitude": 110.42024335421
  },
  "meta": {}
}
```

## Available Endpoints

### Province Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/nusa/provinces` | List all provinces |
| GET | `/nusa/provinces/{code}` | Get specific province |
| GET | `/nusa/provinces/{code}/regencies` | Get regencies in province |
| GET | `/nusa/provinces/{code}/districts` | Get districts in province |
| GET | `/nusa/provinces/{code}/villages` | Get villages in province |

### Regency Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/nusa/regencies` | List all regencies |
| GET | `/nusa/regencies/{code}` | Get specific regency |
| GET | `/nusa/regencies/{code}/districts` | Get districts in regency |
| GET | `/nusa/regencies/{code}/villages` | Get villages in regency |

### District Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/nusa/districts` | List all districts |
| GET | `/nusa/districts/{code}` | Get specific district |
| GET | `/nusa/districts/{code}/villages` | Get villages in district |

### Village Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/nusa/villages` | List all villages |
| GET | `/nusa/villages/{code}` | Get specific village |

## Query Parameters

### Pagination

```bash
# Get second page with 25 items per page
GET /nusa/provinces?page=2&per_page=25
```

### Search

```bash
# Search by name or code
GET /nusa/provinces?search=jawa
GET /nusa/regencies?search=33
```

### Filtering

```bash
# Filter by specific codes
GET /nusa/provinces?codes[]=33&codes[]=34&codes[]=35
```

## Usage Examples

### JavaScript/Fetch

```javascript
// Get all provinces
async function getProvinces() {
  const response = await fetch('/nusa/provinces');
  const data = await response.json();
  return data.data;
}

// Search regencies
async function searchRegencies(query) {
  const response = await fetch(`/nusa/regencies?search=${encodeURIComponent(query)}`);
  const data = await response.json();
  return data.data;
}

// Get regencies in a province
async function getRegenciesByProvince(provinceCode) {
  const response = await fetch(`/nusa/provinces/${provinceCode}/regencies`);
  const data = await response.json();
  return data.data;
}
```

### PHP/Guzzle

```php
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'https://your-app.com/']);

// Get provinces
$response = $client->get('nusa/provinces');
$provinces = json_decode($response->getBody(), true);

// Search with parameters
$response = $client->get('nusa/regencies', [
    'query' => [
        'search' => 'jakarta',
        'per_page' => 50
    ]
]);
$regencies = json_decode($response->getBody(), true);
```

### cURL

```bash
# Basic request
curl -X GET "https://your-app.com/nusa/provinces" \
  -H "Accept: application/json"

# With search parameters
curl -X GET "https://your-app.com/nusa/regencies?search=jakarta&per_page=25" \
  -H "Accept: application/json"

# Get nested resources
curl -X GET "https://your-app.com/nusa/provinces/33/regencies" \
  -H "Accept: application/json"
```

## Security Considerations

### Authentication

By default, the API is public. For production applications, consider adding authentication:

```php
// In your RouteServiceProvider
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('nusa/provinces', [ProvinceController::class, 'index']);
    // Other protected routes...
});
```

### Rate Limiting

Apply rate limiting to prevent abuse:

```php
Route::middleware(['throttle:60,1'])->group(function () {
    // Your API routes
});
```

### CORS

Configure CORS for frontend applications:

```php
// config/cors.php
'paths' => ['api/*', 'nusa/*'],
'allowed_methods' => ['GET'],
'allowed_origins' => ['https://yourdomain.com'],
```

## Error Handling

### HTTP Status Codes

- **200 OK** - Successful request
- **404 Not Found** - Resource not found
- **422 Unprocessable Entity** - Validation errors
- **429 Too Many Requests** - Rate limit exceeded

### Error Response Format

```json
{
  "message": "No query results for model [Creasi\\Nusa\\Models\\Province] 99",
  "exception": "Illuminate\\Database\\Eloquent\\ModelNotFoundException"
}
```

## Performance Tips

### Caching

Implement caching for frequently accessed data:

```php
use Illuminate\Support\Facades\Cache;

Route::get('nusa/provinces', function () {
    return Cache::remember('nusa.provinces', 3600, function () {
        return Province::orderBy('name')->paginate();
    });
});
```

### Pagination

Always use pagination for large datasets:

```javascript
// Good: Use pagination
const response = await fetch('/nusa/villages?per_page=50');

// Avoid: Loading all villages at once
const response = await fetch('/nusa/villages'); // 83,467 records!
```

### Selective Fields

Request only the fields you need:

```php
// In your custom controller
public function index(Request $request)
{
    $fields = $request->get('fields', ['code', 'name']);
    return Province::select($fields)->paginate();
}
```

## Custom Implementation

### Disable Default Routes

```dotenv
CREASI_NUSA_ROUTES_ENABLE=false
```

### Create Custom Routes

```php
// routes/api.php
use Creasi\Nusa\Http\Controllers\ProvinceController;

Route::prefix('v1/indonesia')->middleware(['auth:api', 'throttle:100,1'])->group(function () {
    Route::get('provinces', [ProvinceController::class, 'index']);
    Route::get('provinces/{province}', [ProvinceController::class, 'show']);
    Route::get('provinces/{province}/regencies', [ProvinceController::class, 'regencies']);
    // Add more routes as needed
});
```

### Custom Controllers

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Creasi\Nusa\Models\Province;
use Illuminate\Http\Request;

class IndonesiaController extends Controller
{
    public function provinces(Request $request)
    {
        $query = Province::query();
        
        if ($search = $request->get('search')) {
            $query->search($search);
        }
        
        if ($codes = $request->get('codes')) {
            $query->whereIn('code', $codes);
        }
        
        return $query->paginate($request->get('per_page', 15));
    }
    
    public function province(string $code)
    {
        $province = Province::findOrFail($code);
        
        return response()->json([
            'data' => $province,
            'meta' => [
                'regencies_count' => $province->regencies()->count(),
                'districts_count' => $province->districts()->count(),
                'villages_count' => $province->villages()->count(),
            ]
        ]);
    }
}
```

For detailed API reference, see the [API Reference](/api/overview) section.
