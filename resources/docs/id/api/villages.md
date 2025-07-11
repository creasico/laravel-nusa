# API Desa/Kelurahan

API Desa/Kelurahan menyediakan akses ke semua 83.762 desa dan kelurahan di Indonesia beserta kode pos dan hubungan administratifnya.

## Endpoint

### Daftar Desa/Kelurahan

```http
GET /nusa/villages
```

Mengembalikan daftar semua desa/kelurahan dengan paginasi.

#### Parameter Kueri

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, maks: 100) |
| `search` | string | Cari berdasarkan nama atau kode |
| `codes[]` | array | Filter berdasarkan kode desa/kelurahan tertentu |

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/villages?search=medono&per_page=10"
```

### Dapatkan Desa/Kelurahan

```http
GET /nusa/villages/{code}
```

Mengembalikan desa/kelurahan tertentu berdasarkan kodenya.

#### Parameter Jalur

| Parameter | Tipe | Deskripsi |
|-----------|------|-------------|
| `code` | string | Kode desa/kelurahan 10 digit |

#### Contoh Permintaan

```bash
curl "https://your-app.com/nusa/villages/3375011002"
```

## Atribut Data

### Objek Desa/Kelurahan

| Atribut | Tipe | Deskripsi |
|-----------|------|-------------|
| `code` | string | Kode desa/kelurahan 10 digit |
| `district_code` | string | Kode kecamatan induk |
| `regency_code` | string | Kode kabupaten/kota induk |
| `province_code` | string | Kode provinsi induk |
| `name` | string | Nama desa/kelurahan dalam bahasa Indonesia |
| `postal_code` | string | Kode pos 5 digit |