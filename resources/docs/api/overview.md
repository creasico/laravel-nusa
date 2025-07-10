# API Overview

Laravel Nusa provides a comprehensive RESTful API for accessing Indonesian administrative data. The API is automatically available after installation and follows Laravel conventions for consistency and ease of use.

## Base URL

All API endpoints are prefixed with `/nusa` by default:

```
https://your-app.com/nusa/
```

You can customize this prefix in the [configuration](/guide/configuration).

## Authentication

The API endpoints are **public by default** and don't require authentication. This makes them suitable for:

- Public address forms
- Location-based services
- Geographic data visualization
- Mobile applications

::: warning Security Note
If you need to restrict access, you can apply middleware to the routes or disable them entirely and create your own protected endpoints.
:::

## Response Format

All API responses follow a consistent JSON structure:

### Collection Responses

```json
{
  "data": [
    {
      "code": "33",
      "name": "Jawa Tengah",
      "latitude": -6.9934809206806,
      "longitude": 110.42024335421,
      "coordinates": [...],
      "postal_codes": [...]
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

### Single Resource Responses

```json
{
  "data": {
    "code": "33",
    "name": "Jawa Tengah",
    "latitude": -6.9934809206806,
    "longitude": 110.42024335421,
    "coordinates": [...],
    "postal_codes": [...]
  },
  "meta": {}
}
```

## Pagination

All collection endpoints support pagination:

- **Default page size**: 15 items
- **Maximum page size**: 100 items
- **Page parameter**: `?page=2`
- **Per page parameter**: `?per_page=50`

### Pagination Example

```bash
# Get second page with 25 items per page
GET /nusa/provinces?page=2&per_page=25
```

## Query Parameters

### Search

Search by name or code using the `search` parameter:

```bash
# Search provinces by name
GET /nusa/provinces?search=jawa

# Search by code
GET /nusa/provinces?search=33

# Search regencies
GET /nusa/regencies?search=semarang
```

### Filtering by Codes

Filter results by specific codes using the `codes[]` parameter:

```bash
# Get specific provinces
GET /nusa/provinces?codes[]=33&codes[]=34&codes[]=35

# Get specific regencies
GET /nusa/regencies?codes[]=3375&codes[]=3376
```

### Combining Parameters

You can combine search and filtering:

```bash
# Search for "jakarta" in specific provinces
GET /nusa/regencies?search=jakarta&codes[]=31&codes[]=32
```

## Endpoints Overview

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

## Data Attributes

### Province Attributes

```json
{
  "code": "33",                   // 2-digit province code
  "name": "Jawa Tengah",          // Province name
  "latitude": -6.9934809206806,   // Center latitude
  "longitude": 110.42024335421,   // Center longitude
  "coordinates": [...],           // Boundary coordinates (array)
  "postal_codes": [...]           // All postal codes in province
}
```

### Regency Attributes

```json
{
  "code": "33.75",                // xx.xx regency code
  "province_code": "33",          // Parent province code
  "name": "Kota Pekalongan",      // Regency name
  "latitude": -6.8969497174987,   // Center latitude
  "longitude": 109.66208089654,   // Center longitude
  "coordinates": [...],           // Boundary coordinates (array)
  "postal_codes": [...]           // All postal codes in regency
}
```

### District Attributes

```json
{
  "code": "33.75.01",             // xx.xx.xx district code
  "regency_code": "33.75",        // Parent regency code
  "province_code": "33",          // Parent province code
  "name": "Pekalongan Barat",     // District name
  "postal_codes": [51111, 51112]  // Postal codes in district
}
```

### Village Attributes

```json
{
  "code": "33.75.01.1002",        // xx.xx.xx.xxxx village code
  "district_code": "33.75.01",    // Parent district code
  "regency_code": "33.75",        // Parent regency code
  "province_code": "33",          // Parent province code
  "name": "Medono",               // Village name
  "postal_code": "51111"          // Village postal code
}
```

## Error Handling

The API returns standard HTTP status codes:

### Success Codes

- **200 OK** - Successful request
- **404 Not Found** - Resource not found

### Error Response Format

```json
{
  "message": "No query results for model [Creasi\\Nusa\\Models\\Province] 99",
  "exception": "Illuminate\\Database\\Eloquent\\ModelNotFoundException"
}
```

## CORS Support

If you need to access the API from a browser application on a different domain, configure CORS in your Laravel application:

```php
// config/cors.php
'paths' => ['api/*', 'nusa/*'],
'allowed_methods' => ['GET'],
'allowed_origins' => ['*'],
```

## Usage Examples

::: code-group

```javascript [fetch]
// Get all provinces
const response = await fetch('/nusa/provinces');
const data = await response.json();
console.log(data.data); // Array of provinces

// Search regencies
const searchResponse = await fetch('/nusa/regencies?search=jakarta');
const searchData = await searchResponse.json();
```

```bash [curl]
# Get provinces
curl -X GET "https://your-app.com/nusa/provinces" \
  -H "Accept: application/json"

# Get specific province with regencies
curl -X GET "https://your-app.com/nusa/provinces/33/regencies" \
  -H "Accept: application/json"
```

```php [guzzle]
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'https://your-app.com/']);

// Get provinces
$response = $client->get('nusa/provinces');
$provinces = json_decode($response->getBody(), true);

// Search regencies
$response = $client->get('nusa/regencies', [
    'query' => ['search' => 'jakarta']
]);
$regencies = json_decode($response->getBody(), true);
```

:::

## Next Steps

Explore the detailed API documentation for each endpoint:

- **[Provinces API](/api/provinces)** - Province endpoints and examples
- **[Regencies API](/api/regencies)** - Regency endpoints and examples
- **[Districts API](/api/districts)** - District endpoints and examples
- **[Villages API](/api/villages)** - Village endpoints and examples
