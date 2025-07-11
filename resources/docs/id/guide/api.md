# RESTful API

Panduan lengkap untuk menggunakan dan mengkustomisasi RESTful API Laravel Nusa, termasuk konfigurasi endpoint, autentikasi, rate limiting, dan integrasi dengan aplikasi frontend.

## Ikhtisar API

Laravel Nusa menyediakan RESTful API lengkap untuk mengakses data wilayah administratif Indonesia. API menawarkan endpoint yang bersih dan konsisten untuk semua tingkat administratif dengan pagination, pencarian, dan kemampuan filtering bawaan.

### Konfigurasi Dasar

```php
// config/nusa.php
'api' => [
    'enabled' => true,
    'prefix' => 'nusa',
    'middleware' => ['api'],
    'rate_limit' => '60,1', // 60 request per menit
],
```

### Endpoint yang Tersedia

```bash
# Provinsi
GET /nusa/provinces
GET /nusa/provinces/{code}
GET /nusa/provinces/{code}/regencies
GET /nusa/provinces/{code}/districts
GET /nusa/provinces/{code}/villages

# Kabupaten/Kota
GET /nusa/regencies
GET /nusa/regencies/{code}
GET /nusa/regencies/{code}/districts
GET /nusa/regencies/{code}/villages

# Kecamatan
GET /nusa/districts
GET /nusa/districts/{code}
GET /nusa/districts/{code}/villages

# Kelurahan/Desa
GET /nusa/villages
GET /nusa/villages/{code}
```

## Konfigurasi API

### Mengaktifkan/Menonaktifkan API

```php
// Nonaktifkan API sepenuhnya
'api' => [
    'enabled' => false,
],

// Aktifkan dengan konfigurasi kustom
'api' => [
    'enabled' => true,
    'prefix' => 'indonesia',
    'middleware' => ['api', 'auth:sanctum'],
],
```

### Middleware Kustom

```php
// Tambahkan autentikasi
'api' => [
    'middleware' => ['api', 'auth:sanctum'],
],

// Tambahkan middleware kustom
'api' => [
    'middleware' => ['api', 'custom.middleware'],
],
```

### Rate Limiting

```php
// Batas rate kustom
'api' => [
    'rate_limit' => '100,1', // 100 request per menit
],

// Batas berbeda untuk endpoint berbeda
'api' => [
    'rate_limits' => [
        'provinces' => '200,1',
        'villages' => '50,1',
    ],
],
```

## Autentikasi

### Autentikasi Sanctum

```php
// Install Laravel Sanctum
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

// Konfigurasi middleware API
'api' => [
    'middleware' => ['api', 'auth:sanctum'],
],
```

### Penggunaan API Token

```js
// Penggunaan frontend dengan token
const response = await fetch('/nusa/provinces', {
    headers: {
        'Authorization': 'Bearer ' + apiToken,
        'Accept': 'application/json'
    }
});
```

### Autentikasi Kustom

```php
// app/Http/Middleware/NusaApiAuth.php
class NusaApiAuth
{
    public function handle($request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');

        if (!$this->isValidApiKey($apiKey)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }

    private function isValidApiKey($key)
    {
        return ApiKey::where('key', $key)->where('active', true)->exists();
    }
}
```

## Endpoint API Kustom

### Menambahkan Route Kustom

```php
// routes/api.php
Route::prefix('nusa')->group(function () {
    // Statistik provinsi kustom
    Route::get('provinces/{code}/statistics', [ProvinceController::class, 'statistics']);

    // Endpoint pencarian kustom
    Route::get('search', [SearchController::class, 'search']);

    // Operasi bulk
    Route::post('villages/bulk', [VillageController::class, 'bulk']);
});
```

### Controller Kustom

```php
// app/Http/Controllers/Api/ProvinceController.php
class ProvinceController extends Controller
{
    public function statistics($code)
    {
        $province = Province::with(['regencies', 'districts', 'villages'])
            ->findOrFail($code);

        return response()->json([
            'province' => $province->name,
            'statistics' => [
                'regencies' => $province->regencies->count(),
                'districts' => $province->districts->count(),
                'villages' => $province->villages->count(),
                'total_area' => $province->regencies->sum('area_km2'),
                'population' => $province->regencies->sum('population')
            ]
        ]);
    }
}
```

### Endpoint Pencarian Kustom

```php
class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all');

        $results = [];

        if ($type === 'all' || $type === 'provinces') {
            $results['provinces'] = Province::search($query)->limit(5)->get();
        }

        if ($type === 'all' || $type === 'regencies') {
            $results['regencies'] = Regency::search($query)->limit(5)->get();
        }

        if ($type === 'all' || $type === 'districts') {
            $results['districts'] = District::search($query)->limit(5)->get();
        }

        if ($type === 'all' || $type === 'villages') {
            $results['villages'] = Village::search($query)->limit(5)->get();
        }

        return response()->json($results);
    }
}
```

## Resource API

### Resource API Kustom

```php
// app/Http/Resources/ProvinceResource.php
class ProvinceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'display_name' => "Provinsi {$this->name}",
            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ],
            'statistics' => [
                'regencies' => $this->regencies_count,
                'districts' => $this->districts_count,
                'villages' => $this->villages_count
            ],
            'links' => [
                'self' => route('api.provinces.show', $this->code),
                'regencies' => route('api.provinces.regencies', $this->code)
            ]
        ];
    }
}
```

### Collection Resource

```php
// app/Http/Resources/ProvinceCollection.php
class ProvinceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => ProvinceResource::collection($this->collection),
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage()
            ]
        ];
    }
}
```

## Integrasi Frontend

### JavaScript SDK

```js
// nusa-api-client.js
class NusaApiClient {
    constructor(baseUrl = '/nusa', options = {}) {
        this.baseUrl = baseUrl;
        this.options = {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const response = await fetch(url, {
            ...this.options,
            ...options
        });

        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }

        return response.json();
    }

    // Method provinsi
    async getProvinces(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/provinces?${query}`);
    }

    async getProvince(code, include = []) {
        const query = include.length ? `?include=${include.join(',')}` : '';
        return this.request(`/provinces/${code}${query}`);
    }
}
```

### React Hook

```js
// useNusaApi.js
import { useState, useEffect } from 'react';

export function useNusaApi() {
    const [client] = useState(() => new NusaApiClient());

    return client;
}

export function useProvinces() {
    const [provinces, setProvinces] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const client = useNusaApi();

    useEffect(() => {
        const loadProvinces = async () => {
            try {
                setLoading(true);
                const response = await client.getProvinces({ sort: 'name' });
                setProvinces(response.data);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        loadProvinces();
    }, [client]);

    return { provinces, loading, error };
}
```

## Langkah Selanjutnya

- **[Referensi API](/id/api/overview)** - Dokumentasi API lengkap
- **[Integrasi API](/id/examples/api-integration)** - Contoh integrasi frontend
- **[Setup Development](/id/guide/development)** - Setup environment development
- **[Troubleshooting](/id/guide/troubleshooting)** - Masalah API umum dan solusi
