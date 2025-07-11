# Ikhtisar API

Laravel Nusa menyediakan API RESTful yang komprehensif untuk mengakses data administratif Indonesia. API ini secara otomatis tersedia setelah instalasi dan mengikuti konvensi Laravel untuk konsistensi dan kemudahan penggunaan.

## URL Dasar

Semua *endpoint* API diawali dengan `/nusa` secara *default*:

```
https://your-app.com/nusa/
```

Anda dapat menyesuaikan awalan ini di [konfigurasi](/id/guide/configuration).

## Otentikasi

*Endpoint* API **bersifat publik secara *default*** dan tidak memerlukan otentikasi. Ini membuatnya cocok untuk:

- Formulir alamat publik
- Layanan berbasis lokasi
- Visualisasi data geografis
- Aplikasi seluler

::: warning Catatan Keamanan
Jika Anda perlu membatasi akses, Anda dapat menerapkan *middleware* ke rute atau menonaktifkannya sepenuhnya dan membuat *endpoint* terlindungi Anda sendiri.
:::

## Format Respon

Semua respon API mengikuti struktur JSON yang konsisten:

### Respon Koleksi

```json
{
  "data": [
    {
      "code": "33",
      "name": "Jawa Tengah",
      "latitude": -6.9934809206806,
      "longitude": 110.42024335421,
      "coordinates": [...],
      "postal_codes": [...]
    }
  ],
  "links": {
    "first": "http://localhost:8000/nusa/provinces?page=1",
    "last": "http://localhost:8000/nusa/provinces?page=3",
    "prev": null,
    "next": "http://localhost:8000/nusa/provinces?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 34
  }
}
```

### Respon Sumber Daya Tunggal

```json
{
  "data": {
    "code": "33",
    "name": "Jawa Tengah",
    "latitude": -6.9934809206806,
    "longitude": 110.42024335421,
    "coordinates": [...],
    "postal_codes": [...]
  },
  "meta": {}
}
```

## Paginasi

Semua *endpoint* koleksi mendukung paginasi:

- **Ukuran halaman *default***: 15 item
- **Ukuran halaman maksimum**: 100 item
- **Parameter halaman**: `?page=2`
- **Parameter per halaman**: `?per_page=50`

### Contoh Paginasi

```bash
# Dapatkan halaman kedua dengan 25 item per halaman
GET /nusa/provinces?page=2&per_page=25
```

## Parameter Kueri

### Pencarian

Cari berdasarkan nama atau kode menggunakan parameter `search`:

```http
# Cari provinsi berdasarkan nama
GET /nusa/provinces?search=jawa

# Cari berdasarkan kode
GET /nusa/provinces?search=33

# Cari kabupaten/kota
GET /nusa/regencies?search=semarang
```

### Pemfilteran berdasarkan Kode

Filter hasil berdasarkan kode tertentu menggunakan parameter `codes[]`:

```http
# Dapatkan provinsi tertentu
GET /nusa/provinces?codes[]=33&codes[]=34&codes[]=35

# Dapatkan kabupaten/kota tertentu
GET /nusa/regencies?codes[]=3375&codes[]=3376
```

### Menggabungkan Parameter

Anda dapat menggabungkan pencarian dan pemfilteran:

```http
# Cari "jakarta" di provinsi tertentu
GET /nusa/regencies?search=jakarta&codes[]=31&codes[]=32
```

## Ikhtisar Endpoint

### Endpoint Provinsi

| Metode | Endpoint | Deskripsi |
|--------|----------|-------------|
| GET | `/nusa/provinces` | Daftar semua provinsi |
| GET | `/nusa/provinces/{code}` | Dapatkan provinsi tertentu |
| GET | `/nusa/provinces/{code}/regencies` | Dapatkan kabupaten/kota di provinsi |
| GET | `/nusa/provinces/{code}/districts` | Dapatkan kecamatan di provinsi |
| GET | `/nusa/provinces/{code}/villages` | Dapatkan desa/kelurahan di provinsi |

### Endpoint Kabupaten/Kota

| Metode | Endpoint | Deskripsi |
|--------|----------|-------------|
| GET | `/nusa/regencies` | Daftar semua kabupaten/kota |
| GET | `/nusa/regencies/{code}` | Dapatkan kabupaten/kota tertentu |
| GET | `/nusa/regencies/{code}/districts` | Dapatkan kecamatan di kabupaten/kota |
| GET | `/nusa/regencies/{code}/villages` | Dapatkan desa/kelurahan di kabupaten/kota |

### Endpoint Kecamatan

| Metode | Endpoint | Deskripsi |
|--------|----------|-------------|
| GET | `/nusa/districts` | Daftar semua kecamatan |
| GET | `/nusa/districts/{code}` | Dapatkan kecamatan tertentu |
| GET | `/nusa/districts/{code}/villages` | Dapatkan desa/kelurahan di kecamatan |

### Endpoint Desa/Kelurahan

| Metode | Endpoint | Deskripsi |
|--------|----------|-------------|
| GET | `/nusa/villages` | Daftar semua desa/kelurahan |
| GET | `/nusa/villages/{code}` | Dapatkan desa/kelurahan tertentu |

## Atribut Data

### Atribut Provinsi

```json
{
  "code": "33",                   // Kode provinsi 2 digit
  "name": "Jawa Tengah",          // Nama provinsi
  "latitude": -6.9934809206806,   // Lintang pusat
  "longitude": 110.42024335421,   // Bujur pusat
  "coordinates": [...],           // Koordinat batas (array)
  "postal_codes": [...]           // Semua kode pos di provinsi
}
```

### Atribut Kabupaten/Kota

```json
{
  "code": "33.75",                // Kode kabupaten/kota xx.xx
  "province_code": "33",          // Kode provinsi induk
  "name": "Kota Pekalongan",      // Nama kabupaten/kota
  "latitude": -6.8969497174987,   // Lintang pusat
  "longitude": 109.66208089654,   // Bujur pusat
  "coordinates": [...],           // Koordinat batas (array)
  "postal_codes": [...]           // Semua kode pos di kabupaten/kota
}
```

### Atribut Kecamatan

```json
{
  "code": "33.75.01",             // Kode kecamatan xx.xx.xx
  "regency_code": "33.75",        // Kode kabupaten/kota induk
  "province_code": "33",          // Kode provinsi induk
  "name": "Pekalongan Barat",     // Nama kecamatan
  "postal_codes": [51111, 51112]  // Kode pos di kecamatan
}
```

### Atribut Desa/Kelurahan

```json
{
  "code": "33.75.01.1002",        // Kode desa/kelurahan xx.xx.xx.xxxx
  "district_code": "33.75.01",    // Kode kecamatan induk
  "regency_code": "33.75",        // Kode kabupaten/kota induk
  "province_code": "33",          // Kode provinsi induk
  "name": "Medono",               // Nama desa/kelurahan
  "postal_code": "51111"          // Kode pos desa/kelurahan
}
```

## Penanganan Error

API mengembalikan kode status HTTP standar:

### Kode Sukses

- **200 OK** - Permintaan berhasil
- **404 Not Found** - Sumber daya tidak ditemukan

### Format Respon Error

```json
{
  "message": "No query results for model [Creasi\\Nusa\\Models\\Province] 99",
  "exception": "Illuminate\\Database\\Eloquent\\ModelNotFoundException"
}
```

## Dukungan CORS

Jika Anda perlu mengakses API dari aplikasi *browser* di domain yang berbeda, konfigurasikan CORS di aplikasi Laravel Anda:

```php
// config/cors.php
'paths' => ['api/*', 'nusa/*'],
'allowed_methods' => ['GET'],
'allowed_origins' => ['*'],
```

## Contoh Penggunaan

::: code-group

```js [fetch]
// Dapatkan semua provinsi
const response = await fetch('/nusa/provinces');
const data = await response.json();
console.log(data.data); // Array provinsi

// Cari kabupaten/kota
const searchResponse = await fetch('/nusa/regencies?search=jakarta');
const searchData = await searchResponse.json();
```

```bash [curl]
# Dapatkan provinsi
curl -X GET "https://your-app.com/nusa/provinces" \
  -H "Accept: application/json"

# Dapatkan provinsi tertentu dengan kabupaten/kota
curl -X GET "https://your-app.com/nusa/provinces/33/regencies" \
  -H "Accept: application/json"
```

```php [guzzle]
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'https://your-app.com/']);

// Dapatkan provinsi
$response = $client->get('nusa/provinces');
$provinces = json_decode($response->getBody(), true);

// Cari kabupaten/kota
$response = $client->get('nusa/regencies', [
    'query' => ['search' => 'jakarta']
]);
$regencies = json_decode($response->getBody(), true);
```

:::

## Langkah Selanjutnya

Jelajahi dokumentasi API terperinci untuk setiap *endpoint*:

- **[API Provinsi](/id/api/provinces)** - *Endpoint* dan contoh provinsi
- **[API Kabupaten/Kota](/id/api/regencies)** - *Endpoint* dan contoh kabupaten/kota
- **[API Kecamatan](/id/api/districts)** - *Endpoint* dan contoh kecamatan
- **[API Desa/Kelurahan](/id/api/villages)** - *Endpoint* dan contoh desa/kelurahan