# Provinces API

The Provinces API provides access to all 34 Indonesian provinces with their geographic data and administrative relationships.

## Endpoints

### List Provinces

```http
GET /nusa/provinces
```

Returns a paginated list of all provinces.

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 15, max: 100) |
| `search` | string | Search by name or code |
| `codes[]` | array | Filter by specific province codes |

#### Example Request

```bash
curl "https://your-app.com/nusa/provinces?search=jawa&per_page=10"
```

#### Example Response

```json
{
  "data": [
    {
      "code": "32",
      "name": "Jawa Barat",
      "latitude": -6.914744,
      "longitude": 107.609810,
      "coordinates": [...],
      "postal_codes": ["16110", "16111", "..."]
    },
    {
      "code": "33",
      "name": "Jawa Tengah",
      "latitude": -6.9934809206806,
      "longitude": 110.42024335421,
      "coordinates": [...],
      "postal_codes": ["50111", "50112", "..."]
    }
  ],
  "links": {
    "first": "https://your-app.com/nusa/provinces?page=1",
    "last": "https://your-app.com/nusa/provinces?page=2",
    "prev": null,
    "next": "https://your-app.com/nusa/provinces?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 2,
    "per_page": 15,
    "to": 15,
    "total": 34
  }
}
```

### Get Province

```http
GET /nusa/provinces/{code}
```

Returns a specific province by its code.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `code` | string | 2-digit province code |

#### Example Request

```bash
curl "https://your-app.com/nusa/provinces/33"
```

#### Example Response

```json
{
  "data": {
    "code": "33",
    "name": "Jawa Tengah",
    "latitude": -6.9934809206806,
    "longitude": 110.42024335421,
    "coordinates": [
      [-6.123, 110.456],
      [-6.234, 110.567],
      "..."
    ],
    "postal_codes": [
      "50111", "50112", "50113", "..."
    ]
  },
  "meta": {}
}
```

### Get Province Regencies

```http
GET /nusa/provinces/{code}/regencies
```

Returns all regencies within a specific province.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `code` | string | 2-digit province code |

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 15, max: 100) |
| `search` | string | Search regencies by name |

#### Example Request

```bash
curl "https://your-app.com/nusa/provinces/33/regencies?search=semarang"
```

#### Example Response

```json
{
  "data": [
    {
      "code": "3374",
      "province_code": "33",
      "name": "Kota Semarang",
      "latitude": -6.9666204,
      "longitude": 110.4166595,
      "coordinates": [...],
      "postal_codes": ["50111", "50112", "..."]
    },
    {
      "code": "3322",
      "province_code": "33",
      "name": "Kabupaten Semarang",
      "latitude": -7.1462912,
      "longitude": 110.4988892,
      "coordinates": [...],
      "postal_codes": ["50511", "50512", "..."]
    }
  ],
  "links": {
    "first": "https://your-app.com/nusa/provinces/33/regencies?page=1",
    "last": "https://your-app.com/nusa/provinces/33/regencies?page=3",
    "prev": null,
    "next": "https://your-app.com/nusa/provinces/33/regencies?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 35
  }
}
```

### Get Province Districts

```http
GET /nusa/provinces/{code}/districts
```

Returns all districts within a specific province.

#### Example Request

```bash
curl "https://your-app.com/nusa/provinces/33/districts?per_page=25"
```

### Get Province Villages

```http
GET /nusa/provinces/{code}/villages
```

Returns all villages within a specific province.

#### Example Request

```bash
curl "https://your-app.com/nusa/provinces/33/villages?per_page=50"
```

## Data Attributes

### Province Object

| Attribute | Type | Description |
|-----------|------|-------------|
| `code` | string | 2-digit province code |
| `name` | string | Province name in Indonesian |
| `latitude` | number | Geographic center latitude |
| `longitude` | number | Geographic center longitude |
| `coordinates` | array | Boundary polygon coordinates (GeoJSON format) |
| `postal_codes` | array | All postal codes within the province |

## Usage Examples

### JavaScript

```javascript
class ProvinceService {
  constructor(baseUrl = '/nusa') {
    this.baseUrl = baseUrl;
  }
  
  async getAll(params = {}) {
    const query = new URLSearchParams(params);
    const response = await fetch(`${this.baseUrl}/provinces?${query}`);
    return response.json();
  }
  
  async getById(code) {
    const response = await fetch(`${this.baseUrl}/provinces/${code}`);
    return response.json();
  }
  
  async getRegencies(code, params = {}) {
    const query = new URLSearchParams(params);
    const response = await fetch(`${this.baseUrl}/provinces/${code}/regencies?${query}`);
    return response.json();
  }
  
  async search(query) {
    const response = await fetch(`${this.baseUrl}/provinces?search=${encodeURIComponent(query)}`);
    return response.json();
  }
}

// Usage
const provinceService = new ProvinceService();

// Get all provinces
const provinces = await provinceService.getAll();

// Search provinces
const javaProvinces = await provinceService.search('jawa');

// Get specific province
const centralJava = await provinceService.getById('33');

// Get regencies in Central Java
const regencies = await provinceService.getRegencies('33');
```

### PHP

```php
<?php

use GuzzleHttp\Client;

class ProvinceService
{
    private $client;
    private $baseUrl;
    
    public function __construct(string $baseUrl = 'https://your-app.com/nusa')
    {
        $this->baseUrl = $baseUrl;
        $this->client = new Client(['base_uri' => $baseUrl]);
    }
    
    public function getAll(array $params = []): array
    {
        $response = $this->client->get('/provinces', [
            'query' => $params
        ]);
        
        return json_decode($response->getBody(), true);
    }
    
    public function getById(string $code): array
    {
        $response = $this->client->get("/provinces/{$code}");
        return json_decode($response->getBody(), true);
    }
    
    public function getRegencies(string $code, array $params = []): array
    {
        $response = $this->client->get("/provinces/{$code}/regencies", [
            'query' => $params
        ]);
        
        return json_decode($response->getBody(), true);
    }
    
    public function search(string $query): array
    {
        return $this->getAll(['search' => $query]);
    }
}

// Usage
$provinceService = new ProvinceService();

// Get all provinces
$provinces = $provinceService->getAll();

// Search provinces
$javaProvinces = $provinceService->search('jawa');

// Get specific province
$centralJava = $provinceService->getById('33');

// Get regencies in Central Java
$regencies = $provinceService->getRegencies('33', ['per_page' => 50]);
```

### Python

```python
import requests
from typing import Dict, List, Optional

class ProvinceService:
    def __init__(self, base_url: str = "https://your-app.com/nusa"):
        self.base_url = base_url
        
    def get_all(self, params: Optional[Dict] = None) -> Dict:
        response = requests.get(f"{self.base_url}/provinces", params=params)
        response.raise_for_status()
        return response.json()
        
    def get_by_id(self, code: str) -> Dict:
        response = requests.get(f"{self.base_url}/provinces/{code}")
        response.raise_for_status()
        return response.json()
        
    def get_regencies(self, code: str, params: Optional[Dict] = None) -> Dict:
        response = requests.get(f"{self.base_url}/provinces/{code}/regencies", params=params)
        response.raise_for_status()
        return response.json()
        
    def search(self, query: str) -> Dict:
        return self.get_all({"search": query})

# Usage
province_service = ProvinceService()

# Get all provinces
provinces = province_service.get_all()

# Search provinces
java_provinces = province_service.search("jawa")

# Get specific province
central_java = province_service.get_by_id("33")

# Get regencies in Central Java
regencies = province_service.get_regencies("33", {"per_page": 50})
```

## Error Handling

### Common Errors

#### Province Not Found (404)

```json
{
  "message": "No query results for model [Creasi\\Nusa\\Models\\Province] 99",
  "exception": "Illuminate\\Database\\Eloquent\\ModelNotFoundException"
}
```

#### Validation Error (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "per_page": ["The per page must not be greater than 100."]
  }
}
```

### Error Handling Example

```javascript
async function getProvince(code) {
  try {
    const response = await fetch(`/nusa/provinces/${code}`);
    
    if (!response.ok) {
      if (response.status === 404) {
        throw new Error(`Province with code ${code} not found`);
      }
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Error fetching province:', error);
    throw error;
  }
}
```

## Performance Tips

### Caching

```javascript
class CachedProvinceService {
  constructor() {
    this.cache = new Map();
    this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
  }
  
  async getById(code) {
    const cacheKey = `province_${code}`;
    const cached = this.cache.get(cacheKey);
    
    if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
      return cached.data;
    }
    
    const response = await fetch(`/nusa/provinces/${code}`);
    const data = await response.json();
    
    this.cache.set(cacheKey, {
      data: data.data,
      timestamp: Date.now()
    });
    
    return data.data;
  }
}
```

### Pagination

```javascript
async function getAllProvinces() {
  const allProvinces = [];
  let page = 1;
  let hasMore = true;
  
  while (hasMore) {
    const response = await fetch(`/nusa/provinces?page=${page}&per_page=50`);
    const data = await response.json();
    
    allProvinces.push(...data.data);
    hasMore = data.meta.current_page < data.meta.last_page;
    page++;
  }
  
  return allProvinces;
}
```
