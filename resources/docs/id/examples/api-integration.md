# Contoh Integrasi API

Halaman ini menyediakan contoh praktis untuk mengintegrasikan API Laravel Nusa dari berbagai platform dan bahasa pemrograman.

## Integrasi JavaScript/Frontend

### Vanilla JavaScript

```js
class NusaAPI {
  constructor(baseUrl = '/nusa') {
    this.baseUrl = baseUrl;
  }
  
  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    const response = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...options.headers
      },
      ...options
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return response.json();
  }
  
  // Metode Provinsi
  async getProvinces(params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/provinces?${query}`);
  }
  
  async getProvince(code) {
    return this.request(`/provinces/${code}`);
  }
  
  // Metode Kabupaten/Kota
  async getRegencies(params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/regencies?${query}`);
  }
  
  async getRegenciesByProvince(provinceCode, params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/provinces/${provinceCode}/regencies?${query}`);
  }
  
  async getRegency(code) {
    return this.request(`/regencies/${code}`);
  }
  
  // Metode Kecamatan
  async getDistricts(params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/districts?${query}`);
  }
  
  async getDistrictsByRegency(regencyCode, params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/regencies/${regencyCode}/districts?${query}`);
  }
  
  async getDistrict(code) {
    return this.request(`/districts/${code}`);
  }
  
  // Metode Kelurahan/Desa
  async getVillages(params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/villages?${query}`);
  }
  
  async getVillagesByDistrict(districtCode, params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/districts/${districtCode}/villages?${query}`);
  }
  
  async getVillage(code) {
    return this.request(`/villages/${code}`);
  }
  
  // Metode pencarian
  async searchProvinces(query, params = {}) {
    const searchParams = new URLSearchParams({ search: query, ...params });
    return this.request(`/provinces?${searchParams}`);
  }
  
  async searchRegencies(query, params = {}) {
    const searchParams = new URLSearchParams({ search: query, ...params });
    return this.request(`/regencies?${searchParams}`);
  }
  
  async searchDistricts(query, params = {}) {
    const searchParams = new URLSearchParams({ search: query, ...params });
    return this.request(`/districts?${searchParams}`);
  }
  
  async searchVillages(query, params = {}) {
    const searchParams = new URLSearchParams({ search: query, ...params });
    return this.request(`/villages?${searchParams}`);
  }
}

// Penggunaan
const api = new NusaAPI();

// Contoh penggunaan
async function loadAddressData() {
  try {
    // Muat semua provinsi
    const provinces = await api.getProvinces();
    console.log('Provinsi:', provinces);
    
    // Muat kabupaten/kota untuk Jawa Tengah (kode: 33)
    const regencies = await api.getRegenciesByProvince('33');
    console.log('Kabupaten/Kota di Jawa Tengah:', regencies);
    
    // Cari provinsi yang mengandung kata "jawa"
    const javaProvinces = await api.searchProvinces('jawa');
    console.log('Provinsi dengan kata "jawa":', javaProvinces);
    
    // Muat detail provinsi
    const centralJava = await api.getProvince('33');
    console.log('Detail Jawa Tengah:', centralJava);
    
  } catch (error) {
    console.error('Error loading data:', error);
  }
}

loadAddressData();
```

### Fetch API dengan Error Handling

```js
class NusaAPIClient {
  constructor(config = {}) {
    this.baseUrl = config.baseUrl || '/nusa';
    this.timeout = config.timeout || 10000;
    this.retries = config.retries || 3;
  }
  
  async fetchWithTimeout(url, options = {}) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.timeout);
    
    try {
      const response = await fetch(url, {
        ...options,
        signal: controller.signal
      });
      clearTimeout(timeoutId);
      return response;
    } catch (error) {
      clearTimeout(timeoutId);
      throw error;
    }
  }
  
  async requestWithRetry(endpoint, options = {}, attempt = 1) {
    const url = `${this.baseUrl}${endpoint}`;
    
    try {
      const response = await this.fetchWithTimeout(url, {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          ...options.headers
        },
        ...options
      });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      return await response.json();
    } catch (error) {
      if (attempt < this.retries && this.isRetryableError(error)) {
        console.warn(`Attempt ${attempt} failed, retrying...`, error.message);
        await this.delay(1000 * attempt); // Exponential backoff
        return this.requestWithRetry(endpoint, options, attempt + 1);
      }
      throw error;
    }
  }
  
  isRetryableError(error) {
    return error.name === 'AbortError' || 
           error.message.includes('fetch') ||
           error.message.includes('network');
  }
  
  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
  
  // Metode API dengan error handling
  async getProvinces(params = {}) {
    const query = new URLSearchParams(params);
    return this.requestWithRetry(`/provinces?${query}`);
  }
  
  async getProvince(code) {
    if (!code) throw new Error('Province code is required');
    return this.requestWithRetry(`/provinces/${encodeURIComponent(code)}`);
  }
  
  async getRegenciesByProvince(provinceCode, params = {}) {
    if (!provinceCode) throw new Error('Province code is required');
    const query = new URLSearchParams(params);
    return this.requestWithRetry(`/provinces/${encodeURIComponent(provinceCode)}/regencies?${query}`);
  }
  
  async getDistrictsByRegency(regencyCode, params = {}) {
    if (!regencyCode) throw new Error('Regency code is required');
    const query = new URLSearchParams(params);
    return this.requestWithRetry(`/regencies/${encodeURIComponent(regencyCode)}/districts?${query}`);
  }
  
  async getVillagesByDistrict(districtCode, params = {}) {
    if (!districtCode) throw new Error('District code is required');
    const query = new URLSearchParams(params);
    return this.requestWithRetry(`/districts/${encodeURIComponent(districtCode)}/villages?${query}`);
  }
}

// Penggunaan dengan error handling
const client = new NusaAPIClient({
  baseUrl: '/nusa',
  timeout: 5000,
  retries: 3
});

async function loadDataWithErrorHandling() {
  try {
    const provinces = await client.getProvinces({ per_page: 10 });
    console.log('Loaded provinces:', provinces.data.length);
    
    if (provinces.data.length > 0) {
      const firstProvince = provinces.data[0];
      const regencies = await client.getRegenciesByProvince(firstProvince.code);
      console.log(`Regencies in ${firstProvince.name}:`, regencies.data.length);
    }
    
  } catch (error) {
    console.error('Failed to load data:', error.message);
    
    // Handle specific error types
    if (error.message.includes('timeout')) {
      alert('Request timeout. Please check your connection.');
    } else if (error.message.includes('404')) {
      alert('Data not found.');
    } else {
      alert('An error occurred while loading data.');
    }
  }
}
```

### Axios Implementation

```js
import axios from 'axios';

class NusaAxiosClient {
  constructor(config = {}) {
    this.client = axios.create({
      baseURL: config.baseUrl || '/nusa',
      timeout: config.timeout || 10000,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });
    
    this.setupInterceptors();
  }
  
  setupInterceptors() {
    // Request interceptor
    this.client.interceptors.request.use(
      (config) => {
        console.log(`Making request to: ${config.url}`);
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );
    
    // Response interceptor
    this.client.interceptors.response.use(
      (response) => {
        return response.data;
      },
      (error) => {
        if (error.response) {
          // Server responded with error status
          const message = error.response.data?.message || error.response.statusText;
          throw new Error(`API Error (${error.response.status}): ${message}`);
        } else if (error.request) {
          // Request was made but no response received
          throw new Error('Network error: No response from server');
        } else {
          // Something else happened
          throw new Error(`Request error: ${error.message}`);
        }
      }
    );
  }
  
  // API methods
  async getProvinces(params = {}) {
    return this.client.get('/provinces', { params });
  }
  
  async getProvince(code) {
    return this.client.get(`/provinces/${code}`);
  }
  
  async getRegenciesByProvince(provinceCode, params = {}) {
    return this.client.get(`/provinces/${provinceCode}/regencies`, { params });
  }
  
  async getDistrictsByRegency(regencyCode, params = {}) {
    return this.client.get(`/regencies/${regencyCode}/districts`, { params });
  }
  
  async getVillagesByDistrict(districtCode, params = {}) {
    return this.client.get(`/districts/${districtCode}/villages`, { params });
  }
  
  async searchAll(query, params = {}) {
    const searchParams = { search: query, ...params };
    
    const [provinces, regencies, districts, villages] = await Promise.allSettled([
      this.client.get('/provinces', { params: searchParams }),
      this.client.get('/regencies', { params: searchParams }),
      this.client.get('/districts', { params: searchParams }),
      this.client.get('/villages', { params: searchParams })
    ]);
    
    return {
      provinces: provinces.status === 'fulfilled' ? provinces.value.data : [],
      regencies: regencies.status === 'fulfilled' ? regencies.value.data : [],
      districts: districts.status === 'fulfilled' ? districts.value.data : [],
      villages: villages.status === 'fulfilled' ? villages.value.data : []
    };
  }
}

// Penggunaan
const axiosClient = new NusaAxiosClient();

async function demonstrateAxiosUsage() {
  try {
    // Pencarian di semua level
    const searchResults = await axiosClient.searchAll('jakarta');
    console.log('Search results for "jakarta":', searchResults);
    
    // Load data bertingkat
    const provinces = await axiosClient.getProvinces({ per_page: 5 });
    console.log('First 5 provinces:', provinces.data);
    
    if (provinces.data.length > 0) {
      const regencies = await axiosClient.getRegenciesByProvince(provinces.data[0].code);
      console.log('Regencies:', regencies.data);
    }
    
  } catch (error) {
    console.error('Axios client error:', error.message);
  }
}
```

## Integrasi PHP

### Laravel HTTP Client

```php
namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class NusaAPIService
{
    private string $baseUrl;
    private int $timeout;
    private int $cacheTimeout;

    public function __construct()
    {
        $this->baseUrl = config('app.url') . '/nusa';
        $this->timeout = 30;
        $this->cacheTimeout = 3600; // 1 jam
    }

    private function request(string $endpoint, array $params = []): array
    {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . $endpoint, $params);

        if ($response->failed()) {
            throw new \Exception("API request failed: {$response->status()}");
        }

        return $response->json();
    }

    public function getProvinces(array $params = []): array
    {
        $cacheKey = 'nusa.provinces.' . md5(serialize($params));

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($params) {
            return $this->request('/provinces', $params);
        });
    }

    public function getProvince(string $code): array
    {
        $cacheKey = "nusa.province.{$code}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($code) {
            return $this->request("/provinces/{$code}");
        });
    }

    public function getRegenciesByProvince(string $provinceCode, array $params = []): array
    {
        $cacheKey = "nusa.regencies.{$provinceCode}." . md5(serialize($params));

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($provinceCode, $params) {
            return $this->request("/provinces/{$provinceCode}/regencies", $params);
        });
    }

    public function getDistrictsByRegency(string $regencyCode, array $params = []): array
    {
        $cacheKey = "nusa.districts.{$regencyCode}." . md5(serialize($params));

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($regencyCode, $params) {
            return $this->request("/regencies/{$regencyCode}/districts", $params);
        });
    }

    public function getVillagesByDistrict(string $districtCode, array $params = []): array
    {
        $cacheKey = "nusa.villages.{$districtCode}." . md5(serialize($params));

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($districtCode, $params) {
            return $this->request("/districts/{$districtCode}/villages", $params);
        });
    }

    public function searchProvinces(string $query, array $params = []): array
    {
        $params['search'] = $query;
        return $this->request('/provinces', $params);
    }

    public function searchRegencies(string $query, array $params = []): array
    {
        $params['search'] = $query;
        return $this->request('/regencies', $params);
    }

    public function searchDistricts(string $query, array $params = []): array
    {
        $params['search'] = $query;
        return $this->request('/districts', $params);
    }

    public function searchVillages(string $query, array $params = []): array
    {
        $params['search'] = $query;
        return $this->request('/villages', $params);
    }

    public function getCompleteAddress(string $villageCode): array
    {
        $cacheKey = "nusa.complete_address.{$villageCode}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($villageCode) {
            $village = $this->request("/villages/{$villageCode}");

            if (!isset($village['data'])) {
                throw new \Exception('Village not found');
            }

            $villageData = $village['data'];

            // Load related data
            $district = $this->request("/districts/{$villageData['district_code']}")['data'];
            $regency = $this->request("/regencies/{$district['regency_code']}")['data'];
            $province = $this->request("/provinces/{$regency['province_code']}")['data'];

            return [
                'village' => $villageData,
                'district' => $district,
                'regency' => $regency,
                'province' => $province,
                'formatted_address' => $this->formatAddress($villageData, $district, $regency, $province)
            ];
        });
    }

    private function formatAddress(array $village, array $district, array $regency, array $province): string
    {
        return implode(', ', [
            $village['name'],
            $district['name'],
            $regency['name'],
            $province['name'],
            $village['postal_code'] ?? ''
        ]);
    }
}
```

### Penggunaan Service

```php
namespace App\Http\Controllers;

use App\Services\NusaAPIService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    private NusaAPIService $nusaService;

    public function __construct(NusaAPIService $nusaService)
    {
        $this->nusaService = $nusaService;
    }

    public function getProvinces(Request $request)
    {
        try {
            $params = $request->only(['search', 'per_page', 'page']);
            $provinces = $this->nusaService->getProvinces($params);

            return response()->json($provinces);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load provinces',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getRegencies(Request $request, string $provinceCode)
    {
        try {
            $params = $request->only(['search', 'per_page', 'page']);
            $regencies = $this->nusaService->getRegenciesByProvince($provinceCode, $params);

            return response()->json($regencies);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load regencies',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function searchAddress(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'sometimes|in:province,regency,district,village'
        ]);

        $query = $request->input('query');
        $type = $request->input('type', 'all');

        try {
            $results = [];

            if ($type === 'all' || $type === 'province') {
                $results['provinces'] = $this->nusaService->searchProvinces($query);
            }

            if ($type === 'all' || $type === 'regency') {
                $results['regencies'] = $this->nusaService->searchRegencies($query);
            }

            if ($type === 'all' || $type === 'district') {
                $results['districts'] = $this->nusaService->searchDistricts($query);
            }

            if ($type === 'all' || $type === 'village') {
                $results['villages'] = $this->nusaService->searchVillages($query);
            }

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCompleteAddress(string $villageCode)
    {
        try {
            $address = $this->nusaService->getCompleteAddress($villageCode);
            return response()->json($address);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load complete address',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

### Guzzle HTTP Client

```php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;

class NusaGuzzleService
{
    private Client $client;
    private string $baseUrl;
    private int $cacheTimeout;

    public function __construct()
    {
        $this->baseUrl = config('app.url') . '/nusa';
        $this->cacheTimeout = 3600;

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    private function request(string $endpoint, array $params = []): array
    {
        try {
            $response = $this->client->get($endpoint, [
                'query' => $params
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $message = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();

            throw new \Exception("API request failed ({$statusCode}): {$message}");
        }
    }

    public function getProvinces(array $params = []): array
    {
        $cacheKey = 'guzzle.nusa.provinces.' . md5(serialize($params));

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($params) {
            return $this->request('/provinces', $params);
        });
    }

    public function getProvince(string $code): array
    {
        $cacheKey = "guzzle.nusa.province.{$code}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($code) {
            return $this->request("/provinces/{$code}");
        });
    }

    public function batchRequest(array $endpoints): array
    {
        $promises = [];

        foreach ($endpoints as $key => $endpoint) {
            $promises[$key] = $this->client->getAsync($endpoint);
        }

        $responses = \GuzzleHttp\Promise\settle($promises)->wait();
        $results = [];

        foreach ($responses as $key => $response) {
            if ($response['state'] === 'fulfilled') {
                $results[$key] = json_decode($response['value']->getBody()->getContents(), true);
            } else {
                $results[$key] = ['error' => $response['reason']->getMessage()];
            }
        }

        return $results;
    }
}
```

## Integrasi Python

### Menggunakan Requests Library

```python
import requests
from typing import Dict, List, Optional
import time

class NusaAPI:
    def __init__(self, base_url: str = "https://your-app.com/nusa", timeout: int = 30):
        self.base_url = base_url.rstrip('/')
        self.timeout = timeout
        self.session = requests.Session()
        self.session.headers.update({
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        })

    def _request(self, endpoint: str, params: Optional[Dict] = None) -> Dict:
        """Membuat request ke API"""
        url = f"{self.base_url}{endpoint}"

        try:
            response = self.session.get(url, params=params, timeout=self.timeout)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            raise Exception(f"API request failed: {e}")

    def get_provinces(self, params: Optional[Dict] = None) -> Dict:
        """Mendapatkan semua provinsi"""
        return self._request('/provinces', params)

    def get_province(self, code: str) -> Dict:
        """Mendapatkan provinsi berdasarkan kode"""
        return self._request(f'/provinces/{code}')

    def get_regencies_by_province(self, province_code: str, params: Optional[Dict] = None) -> Dict:
        """Mendapatkan kabupaten/kota dalam provinsi"""
        return self._request(f'/provinces/{province_code}/regencies', params)

    def get_districts_by_regency(self, regency_code: str, params: Optional[Dict] = None) -> Dict:
        """Mendapatkan kecamatan dalam kabupaten/kota"""
        return self._request(f'/regencies/{regency_code}/districts', params)

    def get_villages_by_district(self, district_code: str, params: Optional[Dict] = None) -> Dict:
        """Mendapatkan kelurahan/desa dalam kecamatan"""
        return self._request(f'/districts/{district_code}/villages', params)

    def search_provinces(self, query: str) -> Dict:
        """Mencari provinsi berdasarkan nama atau kode"""
        return self.get_provinces({'search': query})

    def search_regencies(self, query: str) -> Dict:
        """Mencari kabupaten/kota berdasarkan nama atau kode"""
        return self._request('/regencies', {'search': query})

    def search_districts(self, query: str) -> Dict:
        """Mencari kecamatan berdasarkan nama atau kode"""
        return self._request('/districts', {'search': query})

    def search_villages(self, query: str) -> Dict:
        """Mencari kelurahan/desa berdasarkan nama atau kode"""
        return self._request('/villages', {'search': query})

    def get_complete_address(self, village_code: str) -> Dict:
        """Mendapatkan alamat lengkap dari kode desa"""
        village = self.get_village(village_code)
        village_data = village['data']

        district = self.get_district(village_data['district_code'])
        district_data = district['data']

        regency = self.get_regency(district_data['regency_code'])
        regency_data = regency['data']

        province = self.get_province(regency_data['province_code'])
        province_data = province['data']

        return {
            'village': village_data,
            'district': district_data,
            'regency': regency_data,
            'province': province_data,
            'formatted_address': self._format_address(village_data, district_data, regency_data, province_data)
        }

    def get_village(self, code: str) -> Dict:
        """Mendapatkan desa berdasarkan kode"""
        return self._request(f'/villages/{code}')

    def get_district(self, code: str) -> Dict:
        """Mendapatkan kecamatan berdasarkan kode"""
        return self._request(f'/districts/{code}')

    def get_regency(self, code: str) -> Dict:
        """Mendapatkan kabupaten/kota berdasarkan kode"""
        return self._request(f'/regencies/{code}')

    def _format_address(self, village: Dict, district: Dict, regency: Dict, province: Dict) -> str:
        """Format alamat lengkap"""
        parts = [
            village['name'],
            district['name'],
            regency['name'],
            province['name']
        ]

        if village.get('postal_code'):
            parts.append(village['postal_code'])

        return ', '.join(parts)

# Penggunaan
api = NusaAPI("https://your-app.com/nusa")

try:
    # Mendapatkan semua provinsi
    provinces = api.get_provinces()
    print(f"Total provinsi: {len(provinces['data'])}")

    # Mencari provinsi yang mengandung "jawa"
    java_provinces = api.search_provinces("jawa")
    print(f"Provinsi dengan kata 'jawa': {len(java_provinces['data'])}")

    # Mendapatkan kabupaten/kota di Jawa Tengah
    central_java_regencies = api.get_regencies_by_province("33")
    print(f"Kabupaten/kota di Jawa Tengah: {len(central_java_regencies['data'])}")

    # Mendapatkan alamat lengkap
    complete_address = api.get_complete_address("33.75.01.1002")
    print(f"Alamat lengkap: {complete_address['formatted_address']}")

except Exception as e:
    print(f"Error: {e}")
```

### Dengan Caching dan Retry Logic

```python
import requests
import time
import json
from functools import wraps
from typing import Dict, List, Optional, Callable

class NusaAPIWithCache:
    def __init__(self, base_url: str = "https://your-app.com/nusa",
                 timeout: int = 30, max_retries: int = 3, cache_ttl: int = 3600):
        self.base_url = base_url.rstrip('/')
        self.timeout = timeout
        self.max_retries = max_retries
        self.cache_ttl = cache_ttl
        self.cache = {}

        self.session = requests.Session()
        self.session.headers.update({
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        })

    def _cache_key(self, endpoint: str, params: Optional[Dict] = None) -> str:
        """Generate cache key"""
        key_parts = [endpoint]
        if params:
            key_parts.append(json.dumps(params, sort_keys=True))
        return '|'.join(key_parts)

    def _is_cache_valid(self, timestamp: float) -> bool:
        """Check if cache is still valid"""
        return time.time() - timestamp < self.cache_ttl

    def _request_with_retry(self, endpoint: str, params: Optional[Dict] = None) -> Dict:
        """Make request with retry logic"""
        last_exception = None

        for attempt in range(self.max_retries):
            try:
                url = f"{self.base_url}{endpoint}"
                response = self.session.get(url, params=params, timeout=self.timeout)
                response.raise_for_status()
                return response.json()
            except requests.exceptions.RequestException as e:
                last_exception = e
                if attempt < self.max_retries - 1:
                    wait_time = 2 ** attempt  # Exponential backoff
                    print(f"Request failed, retrying in {wait_time} seconds... (attempt {attempt + 1})")
                    time.sleep(wait_time)

        raise Exception(f"API request failed after {self.max_retries} attempts: {last_exception}")

    def _request(self, endpoint: str, params: Optional[Dict] = None, use_cache: bool = True) -> Dict:
        """Make cached request"""
        cache_key = self._cache_key(endpoint, params)

        # Check cache
        if use_cache and cache_key in self.cache:
            data, timestamp = self.cache[cache_key]
            if self._is_cache_valid(timestamp):
                return data

        # Make request
        data = self._request_with_retry(endpoint, params)

        # Store in cache
        if use_cache:
            self.cache[cache_key] = (data, time.time())

        return data

    def get_provinces(self, params: Optional[Dict] = None, use_cache: bool = True) -> Dict:
        """Mendapatkan semua provinsi"""
        return self._request('/provinces', params, use_cache)

    def get_province(self, code: str, use_cache: bool = True) -> Dict:
        """Mendapatkan provinsi berdasarkan kode"""
        return self._request(f'/provinces/{code}', use_cache=use_cache)

    def get_regencies_by_province(self, province_code: str,
                                params: Optional[Dict] = None, use_cache: bool = True) -> Dict:
        """Mendapatkan kabupaten/kota dalam provinsi"""
        return self._request(f'/provinces/{province_code}/regencies', params, use_cache)

    def get_districts_by_regency(self, regency_code: str,
                               params: Optional[Dict] = None, use_cache: bool = True) -> Dict:
        """Mendapatkan kecamatan dalam kabupaten/kota"""
        return self._request(f'/regencies/{regency_code}/districts', params, use_cache)

    def get_villages_by_district(self, district_code: str,
                               params: Optional[Dict] = None, use_cache: bool = True) -> Dict:
        """Mendapatkan kelurahan/desa dalam kecamatan"""
        return self._request(f'/districts/{district_code}/villages', params, use_cache)

    def clear_cache(self):
        """Clear all cache"""
        self.cache.clear()

    def get_cache_stats(self) -> Dict:
        """Get cache statistics"""
        total_entries = len(self.cache)
        valid_entries = sum(1 for _, timestamp in self.cache.values()
                          if self._is_cache_valid(timestamp))

        return {
            'total_entries': total_entries,
            'valid_entries': valid_entries,
            'expired_entries': total_entries - valid_entries,
            'cache_hit_ratio': valid_entries / total_entries if total_entries > 0 else 0
        }

# Penggunaan dengan caching
cached_api = NusaAPIWithCache(
    base_url="https://your-app.com/nusa",
    timeout=30,
    max_retries=3,
    cache_ttl=3600  # 1 jam
)

try:
    # Request pertama - akan hit API
    provinces = cached_api.get_provinces()
    print(f"First request: {len(provinces['data'])} provinces")

    # Request kedua - akan menggunakan cache
    provinces_cached = cached_api.get_provinces()
    print(f"Second request (cached): {len(provinces_cached['data'])} provinces")

    # Lihat statistik cache
    stats = cached_api.get_cache_stats()
    print(f"Cache stats: {stats}")

    # Clear cache jika diperlukan
    cached_api.clear_cache()

except Exception as e:
    print(f"Error: {e}")
```

## Integrasi Mobile App

### Flutter/Dart

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class NusaAPI {
  final String baseUrl;
  final Duration timeout;

  NusaAPI({
    this.baseUrl = 'https://your-app.com/nusa',
    this.timeout = const Duration(seconds: 30),
  });

  Future<Map<String, dynamic>> _request(String endpoint, [Map<String, String>? params]) async {
    final uri = Uri.parse('$baseUrl$endpoint').replace(queryParameters: params);

    try {
      final response = await http.get(
        uri,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ).timeout(timeout);

      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      throw Exception('API request failed: $e');
    }
  }

  Future<List<dynamic>> getProvinces({String? search}) async {
    final params = search != null ? {'search': search} : null;
    final response = await _request('/provinces', params);
    return response['data'];
  }

  Future<Map<String, dynamic>> getProvince(String code) async {
    final response = await _request('/provinces/$code');
    return response['data'];
  }

  Future<List<dynamic>> getRegenciesByProvince(String provinceCode) async {
    final response = await _request('/provinces/$provinceCode/regencies');
    return response['data'];
  }

  Future<List<dynamic>> getDistrictsByRegency(String regencyCode) async {
    final response = await _request('/regencies/$regencyCode/districts');
    return response['data'];
  }

  Future<List<dynamic>> getVillagesByDistrict(String districtCode) async {
    final response = await _request('/districts/$districtCode/villages');
    return response['data'];
  }

  Future<Map<String, dynamic>> getCompleteAddress(String villageCode) async {
    final village = await _request('/villages/$villageCode');
    final villageData = village['data'];

    final district = await _request('/districts/${villageData['district_code']}');
    final districtData = district['data'];

    final regency = await _request('/regencies/${districtData['regency_code']}');
    final regencyData = regency['data'];

    final province = await _request('/provinces/${regencyData['province_code']}');
    final provinceData = province['data'];

    return {
      'village': villageData,
      'district': districtData,
      'regency': regencyData,
      'province': provinceData,
      'formatted_address': _formatAddress(villageData, districtData, regencyData, provinceData),
    };
  }

  String _formatAddress(Map<String, dynamic> village, Map<String, dynamic> district,
                       Map<String, dynamic> regency, Map<String, dynamic> province) {
    final parts = [
      village['name'],
      district['name'],
      regency['name'],
      province['name'],
    ];

    if (village['postal_code'] != null) {
      parts.add(village['postal_code']);
    }

    return parts.join(', ');
  }
}

// Widget untuk dropdown alamat
class AddressDropdown extends StatefulWidget {
  final Function(Map<String, dynamic>) onAddressSelected;

  const AddressDropdown({Key? key, required this.onAddressSelected}) : super(key: key);

  @override
  _AddressDropdownState createState() => _AddressDropdownState();
}

class _AddressDropdownState extends State<AddressDropdown> {
  final NusaAPI api = NusaAPI();

  List<dynamic> provinces = [];
  List<dynamic> regencies = [];
  List<dynamic> districts = [];
  List<dynamic> villages = [];

  String? selectedProvince;
  String? selectedRegency;
  String? selectedDistrict;
  String? selectedVillage;

  bool isLoadingProvinces = false;
  bool isLoadingRegencies = false;
  bool isLoadingDistricts = false;
  bool isLoadingVillages = false;

  @override
  void initState() {
    super.initState();
    loadProvinces();
  }

  Future<void> loadProvinces() async {
    setState(() => isLoadingProvinces = true);

    try {
      final data = await api.getProvinces();
      setState(() {
        provinces = data;
        isLoadingProvinces = false;
      });
    } catch (e) {
      setState(() => isLoadingProvinces = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memuat provinsi: $e')),
      );
    }
  }

  Future<void> loadRegencies(String provinceCode) async {
    setState(() {
      isLoadingRegencies = true;
      regencies = [];
      districts = [];
      villages = [];
      selectedRegency = null;
      selectedDistrict = null;
      selectedVillage = null;
    });

    try {
      final data = await api.getRegenciesByProvince(provinceCode);
      setState(() {
        regencies = data;
        isLoadingRegencies = false;
      });
    } catch (e) {
      setState(() => isLoadingRegencies = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memuat kabupaten/kota: $e')),
      );
    }
  }

  Future<void> loadDistricts(String regencyCode) async {
    setState(() {
      isLoadingDistricts = true;
      districts = [];
      villages = [];
      selectedDistrict = null;
      selectedVillage = null;
    });

    try {
      final data = await api.getDistrictsByRegency(regencyCode);
      setState(() {
        districts = data;
        isLoadingDistricts = false;
      });
    } catch (e) {
      setState(() => isLoadingDistricts = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memuat kecamatan: $e')),
      );
    }
  }

  Future<void> loadVillages(String districtCode) async {
    setState(() {
      isLoadingVillages = true;
      villages = [];
      selectedVillage = null;
    });

    try {
      final data = await api.getVillagesByDistrict(districtCode);
      setState(() {
        villages = data;
        isLoadingVillages = false;
      });
    } catch (e) {
      setState(() => isLoadingVillages = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memuat kelurahan/desa: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Dropdown Provinsi
        DropdownButtonFormField<String>(
          value: selectedProvince,
          decoration: InputDecoration(
            labelText: 'Provinsi',
            border: OutlineInputBorder(),
          ),
          items: provinces.map<DropdownMenuItem<String>>((province) {
            return DropdownMenuItem<String>(
              value: province['code'],
              child: Text(province['name']),
            );
          }).toList(),
          onChanged: isLoadingProvinces ? null : (value) {
            setState(() => selectedProvince = value);
            if (value != null) {
              loadRegencies(value);
            }
          },
        ),

        SizedBox(height: 16),

        // Dropdown Kabupaten/Kota
        DropdownButtonFormField<String>(
          value: selectedRegency,
          decoration: InputDecoration(
            labelText: 'Kabupaten/Kota',
            border: OutlineInputBorder(),
          ),
          items: regencies.map<DropdownMenuItem<String>>((regency) {
            return DropdownMenuItem<String>(
              value: regency['code'],
              child: Text(regency['name']),
            );
          }).toList(),
          onChanged: (selectedProvince == null || isLoadingRegencies) ? null : (value) {
            setState(() => selectedRegency = value);
            if (value != null) {
              loadDistricts(value);
            }
          },
        ),

        SizedBox(height: 16),

        // Dropdown Kecamatan
        DropdownButtonFormField<String>(
          value: selectedDistrict,
          decoration: InputDecoration(
            labelText: 'Kecamatan',
            border: OutlineInputBorder(),
          ),
          items: districts.map<DropdownMenuItem<String>>((district) {
            return DropdownMenuItem<String>(
              value: district['code'],
              child: Text(district['name']),
            );
          }).toList(),
          onChanged: (selectedRegency == null || isLoadingDistricts) ? null : (value) {
            setState(() => selectedDistrict = value);
            if (value != null) {
              loadVillages(value);
            }
          },
        ),

        SizedBox(height: 16),

        // Dropdown Kelurahan/Desa
        DropdownButtonFormField<String>(
          value: selectedVillage,
          decoration: InputDecoration(
            labelText: 'Kelurahan/Desa',
            border: OutlineInputBorder(),
          ),
          items: villages.map<DropdownMenuItem<String>>((village) {
            return DropdownMenuItem<String>(
              value: village['code'],
              child: Text('${village['name']} (${village['postal_code'] ?? ''})'),
            );
          }).toList(),
          onChanged: (selectedDistrict == null || isLoadingVillages) ? null : (value) {
            setState(() => selectedVillage = value);
            if (value != null) {
              final selectedVillageData = villages.firstWhere((v) => v['code'] == value);
              widget.onAddressSelected({
                'province_code': selectedProvince,
                'regency_code': selectedRegency,
                'district_code': selectedDistrict,
                'village_code': selectedVillage,
                'postal_code': selectedVillageData['postal_code'],
              });
            }
          },
        ),
      ],
    );
  }
}
```

### React Native

```javascript
import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, Alert } from 'react-native';
import { Picker } from '@react-native-picker/picker';

class NusaAPI {
  constructor(baseUrl = 'https://your-app.com/nusa') {
    this.baseUrl = baseUrl;
  }

  async request(endpoint, params = {}) {
    const url = new URL(`${this.baseUrl}${endpoint}`);
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

    try {
      const response = await fetch(url.toString(), {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      return await response.json();
    } catch (error) {
      throw new Error(`API request failed: ${error.message}`);
    }
  }

  async getProvinces(params = {}) {
    const response = await this.request('/provinces', params);
    return response.data;
  }

  async getRegenciesByProvince(provinceCode, params = {}) {
    const response = await this.request(`/provinces/${provinceCode}/regencies`, params);
    return response.data;
  }

  async getDistrictsByRegency(regencyCode, params = {}) {
    const response = await this.request(`/regencies/${regencyCode}/districts`, params);
    return response.data;
  }

  async getVillagesByDistrict(districtCode, params = {}) {
    const response = await this.request(`/districts/${districtCode}/villages`, params);
    return response.data;
  }
}

const AddressPicker = ({ onAddressChange }) => {
  const [api] = useState(new NusaAPI());

  const [provinces, setProvinces] = useState([]);
  const [regencies, setRegencies] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [villages, setVillages] = useState([]);

  const [selectedProvince, setSelectedProvince] = useState('');
  const [selectedRegency, setSelectedRegency] = useState('');
  const [selectedDistrict, setSelectedDistrict] = useState('');
  const [selectedVillage, setSelectedVillage] = useState('');

  const [loading, setLoading] = useState({
    provinces: false,
    regencies: false,
    districts: false,
    villages: false,
  });

  useEffect(() => {
    loadProvinces();
  }, []);

  const loadProvinces = async () => {
    setLoading(prev => ({ ...prev, provinces: true }));

    try {
      const data = await api.getProvinces();
      setProvinces(data);
    } catch (error) {
      Alert.alert('Error', `Gagal memuat provinsi: ${error.message}`);
    } finally {
      setLoading(prev => ({ ...prev, provinces: false }));
    }
  };

  const loadRegencies = async (provinceCode) => {
    setLoading(prev => ({ ...prev, regencies: true }));
    setRegencies([]);
    setDistricts([]);
    setVillages([]);
    setSelectedRegency('');
    setSelectedDistrict('');
    setSelectedVillage('');

    try {
      const data = await api.getRegenciesByProvince(provinceCode);
      setRegencies(data);
    } catch (error) {
      Alert.alert('Error', `Gagal memuat kabupaten/kota: ${error.message}`);
    } finally {
      setLoading(prev => ({ ...prev, regencies: false }));
    }
  };

  const loadDistricts = async (regencyCode) => {
    setLoading(prev => ({ ...prev, districts: true }));
    setDistricts([]);
    setVillages([]);
    setSelectedDistrict('');
    setSelectedVillage('');

    try {
      const data = await api.getDistrictsByRegency(regencyCode);
      setDistricts(data);
    } catch (error) {
      Alert.alert('Error', `Gagal memuat kecamatan: ${error.message}`);
    } finally {
      setLoading(prev => ({ ...prev, districts: false }));
    }
  };

  const loadVillages = async (districtCode) => {
    setLoading(prev => ({ ...prev, villages: true }));
    setVillages([]);
    setSelectedVillage('');

    try {
      const data = await api.getVillagesByDistrict(districtCode);
      setVillages(data);
    } catch (error) {
      Alert.alert('Error', `Gagal memuat kelurahan/desa: ${error.message}`);
    } finally {
      setLoading(prev => ({ ...prev, villages: false }));
    }
  };

  const handleProvinceChange = (provinceCode) => {
    setSelectedProvince(provinceCode);
    if (provinceCode) {
      loadRegencies(provinceCode);
    }
  };

  const handleRegencyChange = (regencyCode) => {
    setSelectedRegency(regencyCode);
    if (regencyCode) {
      loadDistricts(regencyCode);
    }
  };

  const handleDistrictChange = (districtCode) => {
    setSelectedDistrict(districtCode);
    if (districtCode) {
      loadVillages(districtCode);
    }
  };

  const handleVillageChange = (villageCode) => {
    setSelectedVillage(villageCode);
    if (villageCode) {
      const selectedVillageData = villages.find(v => v.code === villageCode);
      onAddressChange({
        province_code: selectedProvince,
        regency_code: selectedRegency,
        district_code: selectedDistrict,
        village_code: villageCode,
        postal_code: selectedVillageData?.postal_code,
      });
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.label}>Provinsi</Text>
      <Picker
        selectedValue={selectedProvince}
        onValueChange={handleProvinceChange}
        enabled={!loading.provinces}
        style={styles.picker}
      >
        <Picker.Item label="-- Pilih Provinsi --" value="" />
        {provinces.map(province => (
          <Picker.Item
            key={province.code}
            label={province.name}
            value={province.code}
          />
        ))}
      </Picker>

      <Text style={styles.label}>Kabupaten/Kota</Text>
      <Picker
        selectedValue={selectedRegency}
        onValueChange={handleRegencyChange}
        enabled={!loading.regencies && selectedProvince !== ''}
        style={styles.picker}
      >
        <Picker.Item label="-- Pilih Kabupaten/Kota --" value="" />
        {regencies.map(regency => (
          <Picker.Item
            key={regency.code}
            label={regency.name}
            value={regency.code}
          />
        ))}
      </Picker>

      <Text style={styles.label}>Kecamatan</Text>
      <Picker
        selectedValue={selectedDistrict}
        onValueChange={handleDistrictChange}
        enabled={!loading.districts && selectedRegency !== ''}
        style={styles.picker}
      >
        <Picker.Item label="-- Pilih Kecamatan --" value="" />
        {districts.map(district => (
          <Picker.Item
            key={district.code}
            label={district.name}
            value={district.code}
          />
        ))}
      </Picker>

      <Text style={styles.label}>Kelurahan/Desa</Text>
      <Picker
        selectedValue={selectedVillage}
        onValueChange={handleVillageChange}
        enabled={!loading.villages && selectedDistrict !== ''}
        style={styles.picker}
      >
        <Picker.Item label="-- Pilih Kelurahan/Desa --" value="" />
        {villages.map(village => (
          <Picker.Item
            key={village.code}
            label={`${village.name} (${village.postal_code || ''})`}
            value={village.code}
          />
        ))}
      </Picker>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: 16,
  },
  label: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 8,
    marginTop: 16,
  },
  picker: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 4,
  },
});

export default AddressPicker;
```

Contoh-contoh integrasi ini mendemonstrasikan cara mengintegrasikan API Laravel Nusa dari berbagai platform dan bahasa pemrograman, memberikan fondasi yang solid untuk membangun form alamat dan fitur berbasis lokasi dalam aplikasi Anda.
