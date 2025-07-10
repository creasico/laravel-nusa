# Ikhtisar API

Laravel Nusa menyediakan RESTful API yang lengkap untuk mengakses data wilayah administratif Indonesia, dengan endpoint yang mudah digunakan dan response yang konsisten untuk semua tingkat administratif.

## Base URL

Semua endpoint API menggunakan prefix `/nusa` secara default:

```
https://your-app.com/nusa/
```

## Autentikasi

Secara default, endpoint API bersifat publik. Anda dapat menambahkan autentikasi dengan mengkonfigurasi middleware di file `config/nusa.php`:

```php
'api' => [
    'middleware' => ['api', 'auth:sanctum'],
],
```

## Format Response

Semua response API mengikuti format JSON yang konsisten:

```json
{
    "data": [...],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 15,
        "to": 15,
        "total": 150
    }
}
```

## Endpoint yang Tersedia

### Provinsi

```bash
# Dapatkan semua provinsi
GET /nusa/provinces

# Dapatkan provinsi tertentu
GET /nusa/provinces/{code}

# Dapatkan kabupaten/kota dalam provinsi
GET /nusa/provinces/{code}/regencies

# Dapatkan kecamatan dalam provinsi
GET /nusa/provinces/{code}/districts

# Dapatkan kelurahan/desa dalam provinsi
GET /nusa/provinces/{code}/villages
```

### Kabupaten/Kota

```bash
# Dapatkan semua kabupaten/kota
GET /nusa/regencies

# Dapatkan kabupaten/kota tertentu
GET /nusa/regencies/{code}

# Dapatkan kecamatan dalam kabupaten/kota
GET /nusa/regencies/{code}/districts

# Dapatkan kelurahan/desa dalam kabupaten/kota
GET /nusa/regencies/{code}/villages
```

### Kecamatan

```bash
# Dapatkan semua kecamatan
GET /nusa/districts

# Dapatkan kecamatan tertentu
GET /nusa/districts/{code}

# Dapatkan kelurahan/desa dalam kecamatan
GET /nusa/districts/{code}/villages
```

### Kelurahan/Desa

```bash
# Dapatkan semua kelurahan/desa
GET /nusa/villages

# Dapatkan kelurahan/desa tertentu
GET /nusa/villages/{code}
```

## Parameter Query

### Pagination

```bash
# Parameter page dan per_page
GET /nusa/provinces?page=2&per_page=20
```

### Pencarian

```bash
# Pencarian berdasarkan nama atau kode
GET /nusa/provinces?search=jawa
GET /nusa/regencies?search=semarang
```

### Filter

```bash
# Filter berdasarkan kode induk
GET /nusa/regencies?province_code=33
GET /nusa/districts?regency_code=33.74
GET /nusa/villages?district_code=33.74.01

# Filter berdasarkan multiple kode
GET /nusa/villages?codes[]=33.74.01.1001&codes[]=33.74.01.1002

# Filter berdasarkan kode pos
GET /nusa/villages?postal_code=50132
```

### Pengurutan

```bash
# Urutkan berdasarkan nama atau kode
GET /nusa/provinces?sort=name
GET /nusa/provinces?sort=-name  # Descending
```

### Menyertakan Relasi

```bash
# Sertakan data relasi
GET /nusa/villages?include=district,regency,province
GET /nusa/districts?include=regency.province
```

## Contoh Request

### Dapatkan Semua Provinsi

```bash
curl -X GET "https://your-app.com/nusa/provinces" \
     -H "Accept: application/json"
```

Response:
```json
{
    "data": [
        {
            "code": "11",
            "name": "Aceh",
            "latitude": 4.695135,
            "longitude": 96.7493993,
            "regencies_count": 23,
            "districts_count": 289,
            "villages_count": 6497
        },
        // ...
    ],
    "meta": {
        "current_page": 1,
        "total": 34
    }
}
```

### Pencarian Provinsi

```bash
curl -X GET "https://your-app.com/nusa/provinces?search=jawa" \
     -H "Accept: application/json"
```

### Dapatkan Provinsi dengan Kabupaten/Kota

```bash
curl -X GET "https://your-app.com/nusa/provinces/33?include=regencies" \
     -H "Accept: application/json"
```

### Dapatkan Kelurahan/Desa berdasarkan Kode Pos

```bash
curl -X GET "https://your-app.com/nusa/villages?postal_code=50132" \
     -H "Accept: application/json"
```

## Rate Limiting

API menyertakan pembatasan rate untuk mencegah penyalahgunaan:

- **Default**: 60 request per menit
- **Dapat dikonfigurasi** di `config/nusa.php`

Header rate limit disertakan dalam response:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

## Penanganan Error

API mengembalikan kode status HTTP standar:

- `200` - Sukses
- `404` - Resource tidak ditemukan
- `422` - Error validasi
- `429` - Rate limit terlampaui
- `500` - Server error

Response error menyertakan detail:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "search": ["The search field must be at least 2 characters."]
    }
}
```

## Dukungan CORS

CORS diaktifkan secara default untuk endpoint API. Anda dapat menyesuaikan pengaturan CORS di aplikasi Laravel Anda.

## Caching

Response API di-cache untuk meningkatkan performa:

- **Default TTL**: 30 menit
- **Dapat dikonfigurasi** di `config/nusa.php`
- **Cache headers** disertakan dalam response

## Spesifikasi OpenAPI

Laravel Nusa menyediakan spesifikasi OpenAPI (Swagger) untuk API:

```bash
GET /nusa/openapi.json
```

Anda dapat menggunakan ini dengan tools seperti Swagger UI atau Postman untuk dokumentasi dan testing API.

## SDK dan Library

### JavaScript/TypeScript

```bash
npm install laravel-nusa-js
```

```javascript
import { NusaClient } from 'laravel-nusa-js';

const client = new NusaClient('https://your-app.com');
const provinces = await client.provinces.list();
```

### PHP

```bash
composer require laravel-nusa/php-client
```

```php
use LaravelNusa\Client\NusaClient;

$client = new NusaClient('https://your-app.com');
$provinces = $client->provinces()->list();
```

## Contoh Implementasi

### Membangun Location Selector

```javascript
// Ambil provinsi untuk dropdown
fetch('/nusa/provinces')
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('province');
        data.data.forEach(province => {
            const option = new Option(province.name, province.code);
            select.add(option);
        });
    });

// Load kabupaten/kota ketika provinsi berubah
function loadRegencies(provinceCode) {
    fetch(`/nusa/provinces/${provinceCode}/regencies`)
        .then(response => response.json())
        .then(data => {
            // Populate regency dropdown
        });
}
```

### Validasi Alamat

```javascript
async function validateAddress(address) {
    const village = await fetch(`/nusa/villages/${address.village_code}`)
        .then(r => r.json());

    return {
        valid: village.data.district_code === address.district_code,
        village: village.data
    };
}
```

## Langkah Selanjutnya

- **[API Provinsi](/id/api/provinces)** - Dokumentasi endpoint provinsi
- **[API Kabupaten/Kota](/id/api/regencies)** - Dokumentasi endpoint kabupaten/kota
- **[API Kecamatan](/id/api/districts)** - Dokumentasi endpoint kecamatan
- **[API Kelurahan/Desa](/id/api/villages)** - Dokumentasi endpoint kelurahan/desa
