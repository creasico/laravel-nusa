# API Kecamatan

Dokumentasi lengkap untuk endpoint API yang berkaitan dengan data kecamatan di Indonesia, termasuk cara mengakses data kecamatan dan desa dalam wilayah kecamatan tertentu.

## Endpoints

### Dapatkan Semua Kecamatan

Mengambil daftar semua kecamatan di Indonesia dengan pagination.

```http
GET /nusa/districts
```

### Dapatkan Kecamatan Tertentu

Mengambil informasi detail tentang kecamatan tertentu.

```http
GET /nusa/districts/{code}
```

### Dapatkan Kelurahan/Desa dalam Kecamatan

Mengambil semua kelurahan/desa dalam kecamatan tertentu.

```http
GET /nusa/districts/{code}/villages
```

## Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, max: 100) |
| `search` | string | Pencarian berdasarkan nama atau kode kecamatan |
| `regency_code` | string | Filter berdasarkan kode kabupaten/kota |
| `province_code` | string | Filter berdasarkan kode provinsi |
| `sort` | string | Urutkan berdasarkan field (`name`, `code`, `-name`, `-code`) |
| `include` | string | Sertakan relasi (`province`, `regency`, `villages`) |

## Field Response

### Object Kecamatan

| Field | Type | Deskripsi |
|-------|------|-----------|
| `code` | string | Kode kecamatan delapan karakter (format xx.xx.xx) |
| `name` | string | Nama resmi kecamatan |
| `regency_code` | string | Kode kabupaten/kota induk |
| `province_code` | string | Kode provinsi induk |
| `latitude` | float | Latitude titik tengah |
| `longitude` | float | Longitude titik tengah |
| `villages_count` | integer | Jumlah kelurahan/desa dalam kecamatan |

## Contoh Request

### Dapatkan Semua Kecamatan dalam Kabupaten/Kota

```bash
curl -X GET "https://your-app.com/nusa/districts?regency_code=33.74" \
     -H "Accept: application/json"
```

### Dapatkan Kecamatan dengan Kelurahan/Desa

```bash
curl -X GET "https://your-app.com/nusa/districts/33.74.01?include=villages" \
     -H "Accept: application/json"
```

### Pencarian Kecamatan

```bash
curl -X GET "https://your-app.com/nusa/districts?search=tengah&province_code=33" \
     -H "Accept: application/json"
```

## Contoh Penggunaan

### Load Kecamatan berdasarkan Kabupaten/Kota

```js
async function loadDistricts(regencyCode) {
    const response = await fetch(`/nusa/regencies/${regencyCode}/districts?sort=name`);
    const data = await response.json();
    return data.data;
}
```

### Dapatkan Detail Kecamatan

```js
async function getDistrictDetails(districtCode) {
    const response = await fetch(`/nusa/districts/${districtCode}?include=regency.province`);
    const district = await response.json();
    return district.data;
}
```

## Langkah Selanjutnya

- **[API Kelurahan/Desa](/id/api/villages)** - Endpoint kelurahan/desa
- **[API Kabupaten/Kota](/id/api/regencies)** - Endpoint kabupaten/kota
- **[Models](/id/api/models/district)** - Dokumentasi model kecamatan
