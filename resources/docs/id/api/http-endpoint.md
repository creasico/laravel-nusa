# Endpoint HTTP

Laravel Nusa menyediakan **Static Data API** yang lengkap yang memungkinkan Anda mengakses data wilayah administrasi secara langsung melalui permintaan HTTP. Data ini dibuat sebelumnya (pre-generated) dan disimpan sebagai file statis, membuatnya sangat cepat dan cocok untuk konsumsi sisi klien atau caching.

## URL Dasar

API statis dapat diakses melalui path `/static` pada domain aplikasi Anda. Untuk lingkungan demo:

```
https://nusa.creasi.dev/static/
```

## Format Data

Endpoint mendukung berbagai format untuk menyesuaikan kebutuhan penggunaan yang berbeda. Anda dapat memilih format dengan mengubah ekstensi file pada URL.

| Ekstensi | Format | Deskripsi |
| :--- | :--- | :--- |
| `.json` | JSON | Data terstruktur standar untuk aplikasi web. |
| `.csv` | CSV | Data tabular, berguna untuk impor atau analisis spreadsheet. |
| `.geojson` | GeoJSON | Data geografis termasuk batas wilayah (Polygon/MultiPolygon), digunakan untuk pemetaan. |

## Struktur Endpoint

Struktur URL mengikuti tingkat administrasi hirarkis Indonesia: **Provinsi** > **Kabupaten/Kota** > **Kecamatan** > **Kelurahan/Desa**.

Setiap endpoint mengikuti pola yang sama yang dapat diakses dengan atau tanpa ekstensi file dan secara default akan merespons sebagai `json`. Ekstensi file tambahan yang didukung adalah `csv` untuk mengunduh data sebagai `.csv` dan `geojson` untuk mengambil data geografis.

### 1. Provinsi

Anda dapat mengambil daftar utama semua provinsi atau detail untuk provinsi tertentu.

**Daftar semua Provinsi**
```http
GET /static
GET /static/index.json
GET /static/index.csv
```

**Detail Provinsi Tertentu**
Mengembalikan data provinsi dan daftar semua kabupaten/kota di dalamnya.
```http
GET /static/{province_code}
GET /static/{province_code}.json
GET /static/{province_code}.csv
GET /static/{province_code}.geojson
```

**Contoh:**
- `https://nusa.creasi.dev/static/11` (Aceh)

### 2. Kabupaten/Kota

Mengambil detail untuk kabupaten/kota tertentu, termasuk daftar semua kecamatan di dalamnya.

**Pola URL**
```http
GET /static/{province_code}/{regency_code}
GET /static/{province_code}/{regency_code}.json
GET /static/{province_code}/{regency_code}.csv
GET /static/{province_code}/{regency_code}.geojson
```

**Contoh:**
- `https://nusa.creasi.dev/static/11/01` (Kab. Aceh Selatan)

### 3. Kecamatan

Mengambil detail untuk kecamatan tertentu, termasuk daftar semua desa/kelurahan di dalamnya.

**Pola URL**
```http
GET /static/{province_code}/{regency_code}/{district_code}
GET /static/{province_code}/{regency_code}/{district_code}.json
GET /static/{province_code}/{regency_code}/{district_code}.csv
GET /static/{province_code}/{regency_code}/{district_code}.geojson
```

**Contoh:**
- `https://nusa.creasi.dev/static/11/01/01` (Kec. Bakongan)

### 4. Kelurahan/Desa

Mengambil detail untuk desa/kelurahan tertentu.

**Pola URL**
```http
GET /static/{province_code}/{regency_code}/{district_code}/{village_code}
GET /static/{province_code}/{regency_code}/{district_code}/{village_code}.json
GET /static/{province_code}/{regency_code}/{district_code}/{village_code}.csv
GET /static/{province_code}/{regency_code}/{district_code}/{village_code}.geojson
```

**Contoh:**
- `https://nusa.creasi.dev/static/11/01/01/2001` (Gampong Keude Bakongan)

## Contoh Respons

### Respons JSON (Provinsi)
Permintaan: `GET /static/11.json`

```json
{
    "code": "11",
    "name": "Aceh",
    "latitude": 4.2257285830382,
    "longitude": 96.9118740861,
    "regencies": [
        {
            "code": "11.01",
            "province_code": "11",
            "name": "Kabupaten Aceh Selatan",
            "latitude": 3.1618538408941,
            "longitude": 97.436517718652
        },
        ...
    ]
}
```

### Respons GeoJSON
Permintaan: `GET /static/11.geojson`

```json
{
    "type": "Feature",
    "properties": {
        "code": "11",
        "name": "Aceh"
    },
    "geometry": {
        "type": "MultiPolygon",
        "coordinates": [ ... ]
    }
}
```

## Penggunaan Sisi Klien

Karena ini adalah file statis, Anda dapat mengambilnya secara langsung menggunakan `fetch` atau `axios` di aplikasi frontend Anda.

```javascript
// Contoh: Mengambil data kecamatan untuk kabupaten yang dipilih
fetch(`https://nusa.creasi.dev/static/11/01`)
    .then(response => response.json())
    .then(data => {
        console.log("Regency:", data.name);
        console.log("Districts:", data.districts);
    });
```

## Langkah Selanjutnya

Jelajahi dokumentasi API terperinci untuk setiap *endpoint*:

- **[Demo Batas Wilayah](/id/examples/boundaries)** - Contoh implementasi GeoJSON
- **[API Provinsi](/id/api/provinces)** - *Endpoint* dan contoh provinsi
- **[API Kabupaten/Kota](/id/api/regencies)** - *Endpoint* dan contoh kabupaten/kota
- **[API Kecamatan](/id/api/districts)** - *Endpoint* dan contoh kecamatan
- **[API Desa/Kelurahan](/id/api/villages)** - *Endpoint* dan contoh desa/kelurahan