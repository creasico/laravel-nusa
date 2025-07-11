# API Kabupaten/Kota

API Kabupaten/Kota menyediakan akses ke semua 514 kabupaten dan kota di Indonesia beserta data geografis dan hubungan administratifnya.

## Endpoint

### Daftar Kabupaten/Kota

```http
GET /nusa/regencies
```

Mengembalikan daftar semua kabupaten/kota dengan paginasi.

#### Parameter Kueri

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, maks: 100) |
| `search` | string | Cari berdasarkan nama atau kode |
| `codes[]` | array | Filter berdasarkan kode kabupaten/kota tertentu |

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/regencies?search=jakarta&per_page=10"
```

#### Contoh Respon

```json
{
  "data": [
    {
      "code": "31.71",
      "province_code": "31",
      "name": "Kota Jakarta Selatan",
      "latitude": -6.2615,
      "longitude": 106.8106,
      "coordinates": [...],
      "postal_codes": ["12110", "12120", "..."]
    }
  ],
  "links": {
    "first": "https://your-app.com/nusa/regencies?page=1",
    "last": "https://your-app.com/nusa/regencies?page=35",
    "prev": null,
    "next": "https://your-app.com/nusa/regencies?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 35,
    "per_page": 15,
    "to": 15,
    "total": 514
  }
}
```

### Dapatkan Kabupaten/Kota

```http
GET /nusa/regencies/{code}
```

Mengembalikan kabupaten/kota tertentu berdasarkan kodenya.

#### Parameter Jalur

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `code` | string | Kode kabupaten/kota dalam format xx.xx |

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/regencies/33.75"
```

### Dapatkan Kecamatan Kabupaten/Kota

```http
GET /nusa/regencies/{code}/districts
```

Mengembalikan semua kecamatan dalam kabupaten/kota tertentu.

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/regencies/33.75/districts"
```

### Dapatkan Desa/Kelurahan Kabupaten/Kota

```http
GET /nusa/regencies/{code}/villages
```

Mengembalikan semua desa/kelurahan dalam kabupaten/kota tertentu.

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/regencies/33.75/villages?per_page=50"
```

## Atribut Data

### Objek Kabupaten/Kota

| Atribut | Tipe | Deskripsi |
|-----------|------|-------------|
| `code` | string | Kode kabupaten/kota dalam format xx.xx |
| `province_code` | string | Kode provinsi induk |
| `name` | string | Nama kabupaten/kota dalam bahasa Indonesia |
| `latitude` | number | Lintang pusat geografis |
| `longitude` | number | Bujur pusat geografis |
| `coordinates` | array | Koordinat poligon batas |
| `postal_codes` | array | Semua kode pos di dalam kabupaten/kota |