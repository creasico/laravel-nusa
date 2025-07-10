# API Provinsi

Dokumentasi lengkap untuk endpoint API yang berkaitan dengan data provinsi di Indonesia, termasuk cara mengakses data provinsi, kabupaten/kota, kecamatan, dan desa dalam provinsi tertentu.

## Endpoints

### Dapatkan Semua Provinsi

Mengambil daftar semua provinsi di Indonesia dengan pagination.

```http
GET /nusa/provinces
```

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, max: 100) |
| `search` | string | Pencarian berdasarkan nama atau kode provinsi |
| `sort` | string | Urutkan berdasarkan field (`name`, `code`, `-name`, `-code`) |
| `include` | string | Sertakan relasi (`regencies`, `districts`, `villages`) |

#### Contoh Request

```bash
curl -X GET "https://your-app.com/nusa/provinces?search=jawa&include=regencies" \
     -H "Accept: application/json"
```

#### Contoh Response

```json
{
    "data": [
        {
            "code": "32",
            "name": "Jawa Barat",
            "latitude": -6.914744,
            "longitude": 107.609810,
            "regencies_count": 27,
            "districts_count": 627,
            "villages_count": 5312,
            "regencies": [
                {
                    "code": "32.01",
                    "name": "Kabupaten Bogor",
                    "province_code": "32"
                }
                // ... more regencies
            ]
        }
        // ... more provinces
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

### Dapatkan Provinsi Tertentu

Mengambil informasi detail tentang provinsi tertentu.

```http
GET /nusa/provinces/{code}
```

#### Parameter Path

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `code` | string | Kode provinsi (contoh: "33" untuk Jawa Tengah) |

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `include` | string | Sertakan relasi (`regencies`, `districts`, `villages`) |

#### Contoh Request

```bash
curl -X GET "https://your-app.com/nusa/provinces/33?include=regencies" \
     -H "Accept: application/json"
```

#### Contoh Response

```json
{
    "data": {
        "code": "33",
        "name": "Jawa Tengah",
        "latitude": -7.150975,
        "longitude": 110.140259,
        "regencies_count": 35,
        "districts_count": 573,
        "villages_count": 7809,
        "postal_codes": ["50xxx", "51xxx", "52xxx", "53xxx", "54xxx", "55xxx", "56xxx", "57xxx", "58xxx", "59xxx"],
        "regencies": [
            {
                "code": "33.01",
                "name": "Kabupaten Cilacap",
                "province_code": "33",
                "latitude": -7.726621,
                "longitude": 109.012909
            }
            // ... more regencies
        ]
    }
}
```

### Dapatkan Kabupaten/Kota dalam Provinsi

Mengambil semua kabupaten/kota dalam provinsi tertentu.

```http
GET /nusa/provinces/{code}/regencies
```

#### Parameter Path

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `code` | string | Kode provinsi |

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `page` | integer | Nomor halaman |
| `per_page` | integer | Item per halaman |
| `search` | string | Pencarian berdasarkan nama kabupaten/kota |
| `sort` | string | Urutkan berdasarkan field |
| `include` | string | Sertakan relasi (`province`, `districts`, `villages`) |

#### Contoh Request

```bash
curl -X GET "https://your-app.com/nusa/provinces/33/regencies?search=semarang" \
     -H "Accept: application/json"
```

### Dapatkan Kecamatan dalam Provinsi

Mengambil semua kecamatan dalam provinsi tertentu.

```http
GET /nusa/provinces/{code}/districts
```

#### Contoh Request

```bash
curl -X GET "https://your-app.com/nusa/provinces/33/districts?per_page=50" \
     -H "Accept: application/json"
```

### Dapatkan Kelurahan/Desa dalam Provinsi

Mengambil semua kelurahan/desa dalam provinsi tertentu.

```http
GET /nusa/provinces/{code}/villages
```

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `postal_code` | string | Filter berdasarkan kode pos |
| `search` | string | Pencarian berdasarkan nama kelurahan/desa |

#### Contoh Request

```bash
curl -X GET "https://your-app.com/nusa/provinces/33/villages?postal_code=50132" \
     -H "Accept: application/json"
```

## Field Response

### Object Provinsi

| Field | Type | Deskripsi |
|-------|------|-----------|
| `code` | string | Kode provinsi dua digit |
| `name` | string | Nama resmi provinsi |
| `latitude` | float | Latitude titik tengah |
| `longitude` | float | Longitude titik tengah |
| `regencies_count` | integer | Jumlah kabupaten/kota dalam provinsi |
| `districts_count` | integer | Jumlah kecamatan dalam provinsi |
| `villages_count` | integer | Jumlah kelurahan/desa dalam provinsi |
| `postal_codes` | array | Array pola kode pos |

## Response Error

### Provinsi Tidak Ditemukan

```http
HTTP/1.1 404 Not Found
Content-Type: application/json

{
    "message": "Province not found",
    "error": "PROVINCE_NOT_FOUND"
}
```

### Parameter Tidak Valid

```http
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/json

{
    "message": "The given data was invalid.",
    "errors": {
        "per_page": ["The per page may not be greater than 100."]
    }
}
```

## Contoh Penggunaan

### Membangun Province Selector

```javascript
// Ambil semua provinsi untuk dropdown
async function loadProvinces() {
    const response = await fetch('/nusa/provinces?sort=name');
    const data = await response.json();

    const select = document.getElementById('province-select');
    data.data.forEach(province => {
        const option = new Option(province.name, province.code);
        select.appendChild(option);
    });
}
```

### Pencarian Provinsi

```javascript
// Cari provinsi berdasarkan nama
async function searchProvinces(query) {
    const response = await fetch(`/nusa/provinces?search=${encodeURIComponent(query)}`);
    const data = await response.json();
    return data.data;
}
```

### Dapatkan Statistik Provinsi

```javascript
// Dapatkan informasi detail provinsi
async function getProvinceStats(provinceCode) {
    const response = await fetch(`/nusa/provinces/${provinceCode}`);
    const province = await response.json();

    return {
        name: province.data.name,
        regencies: province.data.regencies_count,
        districts: province.data.districts_count,
        villages: province.data.villages_count
    };
}
```

## Rate Limiting

Endpoint provinsi tunduk pada pembatasan rate yang sama dengan endpoint API lainnya:

- **60 request per menit** (default)
- Header rate limit disertakan dalam response
- Dapat dikonfigurasi di `config/nusa.php`

## Caching

Data provinsi di-cache untuk meningkatkan performa:

- **Cache TTL**: 30 menit (default)
- **Cache headers** disertakan dalam response
- **ETags** didukung untuk conditional request

## Langkah Selanjutnya

- **[API Kabupaten/Kota](/id/api/regencies)** - Endpoint kabupaten/kota
- **[API Kecamatan](/id/api/districts)** - Endpoint kecamatan
- **[API Kelurahan/Desa](/id/api/villages)** - Endpoint kelurahan/desa
- **[Models](/id/api/models/province)** - Dokumentasi model provinsi
