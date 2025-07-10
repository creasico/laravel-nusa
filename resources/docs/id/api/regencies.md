# API Kabupaten/Kota

Dokumentasi lengkap untuk endpoint API yang berkaitan dengan data kabupaten dan kota di Indonesia, termasuk cara mengakses data kabupaten/kota, kecamatan, dan desa dalam wilayah tertentu.

## Endpoints

### Dapatkan Semua Kabupaten/Kota

Mengambil daftar semua kabupaten dan kota di Indonesia dengan pagination.

```http
GET /nusa/regencies
```

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, max: 100) |
| `search` | string | Pencarian berdasarkan nama atau kode kabupaten/kota |
| `province_code` | string | Filter berdasarkan kode provinsi |
| `sort` | string | Urutkan berdasarkan field (`name`, `code`, `-name`, `-code`) |
| `include` | string | Sertakan relasi (`province`, `districts`, `villages`) |

#### Contoh Request

```bash
curl -X GET "https://your-app.com/nusa/regencies?province_code=33&search=semarang" \
     -H "Accept: application/json"
```

#### Contoh Response

```json
{
    "data": [
        {
            "code": "33.74",
            "name": "Kota Semarang",
            "province_code": "33",
            "latitude": -6.966667,
            "longitude": 110.416664,
            "districts_count": 16,
            "villages_count": 177
        }
        // ... more regencies
    ],
    "meta": {
        "current_page": 1,
        "total": 514
    }
}
```

### Dapatkan Kabupaten/Kota Tertentu

Mengambil informasi detail tentang kabupaten atau kota tertentu.

```http
GET /nusa/regencies/{code}
```

#### Parameter Path

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `code` | string | Kode kabupaten/kota (contoh: "33.74" untuk Kota Semarang) |

#### Contoh Request

```bash
curl -X GET "https://your-app.com/nusa/regencies/33.74?include=province,districts" \
     -H "Accept: application/json"
```

#### Contoh Response

```json
{
    "data": {
        "code": "33.74",
        "name": "Kota Semarang",
        "province_code": "33",
        "latitude": -6.966667,
        "longitude": 110.416664,
        "districts_count": 16,
        "villages_count": 177,
        "province": {
            "code": "33",
            "name": "Jawa Tengah"
        },
        "districts": [
            {
                "code": "33.74.01",
                "name": "Semarang Tengah",
                "regency_code": "33.74"
            }
            // ... more districts
        ]
    }
}
```

### Dapatkan Kecamatan dalam Kabupaten/Kota

Mengambil semua kecamatan dalam kabupaten/kota tertentu.

```http
GET /nusa/regencies/{code}/districts
```

#### Contoh Request

```bash
curl -X GET "https://your-app.com/nusa/regencies/33.74/districts" \
     -H "Accept: application/json"
```

### Dapatkan Kelurahan/Desa dalam Kabupaten/Kota

Mengambil semua kelurahan/desa dalam kabupaten/kota tertentu.

```http
GET /nusa/regencies/{code}/villages
```

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `postal_code` | string | Filter berdasarkan kode pos |
| `search` | string | Pencarian berdasarkan nama kelurahan/desa |

#### Contoh Request

```bash
curl -X GET "https://your-app.com/nusa/regencies/33.74/villages?postal_code=50132" \
     -H "Accept: application/json"
```

## Field Response

### Object Kabupaten/Kota

| Field | Type | Deskripsi |
|-------|------|-----------|
| `code` | string | Kode kabupaten/kota lima karakter (format xx.xx) |
| `name` | string | Nama resmi kabupaten/kota |
| `province_code` | string | Kode provinsi induk |
| `latitude` | float | Latitude titik tengah |
| `longitude` | float | Longitude titik tengah |
| `districts_count` | integer | Jumlah kecamatan dalam kabupaten/kota |
| `villages_count` | integer | Jumlah kelurahan/desa dalam kabupaten/kota |

## Contoh Penggunaan

### Load Kabupaten/Kota berdasarkan Provinsi

```javascript
// Load kabupaten/kota ketika provinsi dipilih
async function loadRegencies(provinceCode) {
    const response = await fetch(`/nusa/provinces/${provinceCode}/regencies?sort=name`);
    const data = await response.json();

    const select = document.getElementById('regency-select');
    select.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';

    data.data.forEach(regency => {
        const option = new Option(regency.name, regency.code);
        select.appendChild(option);
    });
}
```

### Pencarian Kabupaten/Kota

```javascript
// Cari kabupaten/kota dengan autocomplete
async function searchRegencies(query, provinceCode = null) {
    let url = `/nusa/regencies?search=${encodeURIComponent(query)}`;
    if (provinceCode) {
        url += `&province_code=${provinceCode}`;
    }

    const response = await fetch(url);
    const data = await response.json();
    return data.data;
}
```

### Dapatkan Detail Kabupaten/Kota

```javascript
// Dapatkan informasi lengkap kabupaten/kota
async function getRegencyDetails(regencyCode) {
    const response = await fetch(`/nusa/regencies/${regencyCode}?include=province`);
    const regency = await response.json();

    return {
        name: regency.data.name,
        province: regency.data.province.name,
        districts: regency.data.districts_count,
        villages: regency.data.villages_count,
        coordinates: [regency.data.latitude, regency.data.longitude]
    };
}
```

## Response Error

### Kabupaten/Kota Tidak Ditemukan

```http
HTTP/1.1 404 Not Found
Content-Type: application/json

{
    "message": "Regency not found",
    "error": "REGENCY_NOT_FOUND"
}
```

## Langkah Selanjutnya

- **[API Kecamatan](/id/api/districts)** - Endpoint kecamatan
- **[API Kelurahan/Desa](/id/api/villages)** - Endpoint kelurahan/desa
- **[API Provinsi](/id/api/provinces)** - Endpoint provinsi
- **[Models](/id/api/models/regency)** - Dokumentasi model kabupaten/kota
