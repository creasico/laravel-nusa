# API Kecamatan

API Kecamatan menyediakan akses ke semua 7.285 kecamatan di Indonesia beserta hubungan administratifnya.

## Endpoint

### Daftar Kecamatan

```http
GET /nusa/districts
```

Mengembalikan daftar semua kecamatan dengan paginasi.

#### Parameter Kueri

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, maks: 100) |
| `search` | string | Cari berdasarkan nama atau kode |
| `codes[]` | array | Filter berdasarkan kode kecamatan tertentu |

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/districts?search=pekalongan&per_page=10"
```

### Dapatkan Kecamatan

```http
GET /nusa/districts/{code}
```

Mengembalikan kecamatan tertentu berdasarkan kodenya.

#### Parameter Jalur

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `code` | string | Kode kecamatan dalam format xx.xx.xx |

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/districts/33.75.01"
```

### Dapatkan Desa/Kelurahan Kecamatan

```http
GET /nusa/districts/{code}/villages
```

Mengembalikan semua desa/kelurahan dalam kecamatan tertentu.

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/districts/33.75.01/villages"
```

## Atribut Data

### Objek Kecamatan

| Atribut | Tipe | Deskripsi |
|-----------|------|-------------|
| `code` | string | Kode kecamatan dalam format xx.xx.xx |
| `regency_code` | string | Kode kabupaten/kota induk |
| `province_code` | string | Kode provinsi induk |
| `name` | string | Nama kecamatan dalam bahasa Indonesia |
| `postal_codes` | array | Semua kode pos di dalam kecamatan |