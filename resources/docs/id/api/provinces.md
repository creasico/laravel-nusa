# API Provinsi

API Provinsi menyediakan akses ke semua 34 provinsi di Indonesia beserta data geografis dan hubungan administratifnya.

## Endpoint

### Daftar Provinsi

```http
GET /nusa/provinces
```

Mengembalikan daftar semua provinsi dengan paginasi.

#### Parameter Kueri

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, maks: 100) |
| `search` | string | Cari berdasarkan nama atau kode |
| `codes[]` | array | Filter berdasarkan kode provinsi tertentu |

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/provinces?search=jawa&per_page=10"
```

#### Contoh Respon

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

### Dapatkan Provinsi

```http
GET /nusa/provinces/{code}
```

Mengembalikan provinsi tertentu berdasarkan kodenya.

#### Parameter Jalur

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `code` | string | Kode provinsi 2 digit |

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/provinces/33"
```

#### Contoh Respon

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

### Dapatkan Kabupaten/Kota Provinsi

```http
GET /nusa/provinces/{code}/regencies
```

Mengembalikan semua kabupaten/kota dalam provinsi tertentu.

#### Parameter Jalur

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `code` | string | Kode provinsi 2 digit |

#### Parameter Kueri

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, maks: 100) |
| `search` | string | Cari kabupaten/kota berdasarkan nama |

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/provinces/33/regencies?search=semarang"
```

#### Contoh Respon

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

### Dapatkan Kecamatan Provinsi

```http
GET /nusa/provinces/{code}/districts
```

Mengembalikan semua kecamatan dalam provinsi tertentu.

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/provinces/33/districts?per_page=25"
```

### Dapatkan Desa/Kelurahan Provinsi

```http
GET /nusa/provinces/{code}/villages
```

Mengembalikan semua desa/kelurahan dalam provinsi tertentu.

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/provinces/33/villages?per_page=50"
```

## Atribut Data

### Objek Provinsi

| Atribut | Tipe | Deskripsi |
|-----------|------|-------------|
| `code` | string | Kode provinsi 2 digit |
| `name` | string | Nama provinsi dalam bahasa Indonesia |
| `latitude` | number | Lintang pusat geografis |
| `longitude` | number | Bujur pusat geografis |
| `coordinates` | array | Koordinat poligon batas (format GeoJSON) |
| `postal_codes` | array | Semua kode pos di dalam provinsi |

## Contoh Penggunaan

::: code-group

```js [fetch]
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

// Penggunaan
const provinceService = new ProvinceService();

// Dapatkan semua provinsi
const provinces = await provinceService.getAll();

// Cari provinsi
const javaProvinces = await provinceService.search('jawa');

// Dapatkan provinsi tertentu
const centralJava = await provinceService.getById('33');

// Dapatkan kabupaten/kota di Jawa Tengah
const regencies = await provinceService.getRegencies('33');
```

```php [guzzle]
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

// Penggunaan
$provinceService = new ProvinceService();

// Dapatkan semua provinsi
$provinces = $provinceService->getAll();

// Cari provinsi
$javaProvinces = $provinceService->search('jawa');

// Dapatkan provinsi tertentu
$centralJava = $provinceService->getById('33');

// Dapatkan kabupaten/kota di Jawa Tengah
$regencies = $provinceService->getRegencies('33', ['per_page' => 50]);
```

```python [python]
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

# Penggunaan
province_service = ProvinceService();

# Dapatkan semua provinsi
provinces = province_service.get_all();

# Cari provinsi
java_provinces = province_service.search("jawa");

# Dapatkan provinsi tertentu
central_java = province_service.get_by_id("33");

# Dapatkan kabupaten/kota di Jawa Tengah
regencies = province_service.get_regencies("33", {"per_page": 50});
```

:::

## Penanganan Error

### Error Umum

#### Provinsi Tidak Ditemukan (404)

```json
{
  "message": "No query results for model [Creasi\\Nusa\\Models\\Province] 99",
  "exception": "Illuminate\\Database\\Eloquent\\ModelNotFoundException"
}
```

#### Error Validasi (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "per_page": ["The per page must not be greater than 100."]
  }
}
```

### Contoh Penanganan Error

```js
async function getProvince(code) {
  try {
    const response = await fetch(`/nusa/provinces/${code}`);
    
    if (!response.ok) {
      if (response.status === 404) {
        throw new Error(`Provinsi dengan kode ${code} tidak ditemukan`);
      }
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Gagal mengambil provinsi:', error);
    throw error;
  }
}
```

## Tips Kinerja

### Caching

```js
class CachedProvinceService {
  constructor() {
    this.cache = new Map();
    this.cacheTimeout = 5 * 60 * 1000; // 5 menit
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

### Paginasi

```js
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