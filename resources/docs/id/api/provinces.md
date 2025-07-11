# API Provinsi

API Provinsi menyediakan akses ke semua 38 provinsi Indonesia dengan data geografis dan hubungan administratif mereka.

## Endpoints

### Daftar Provinsi

```http
GET /nusa/provinces
```

Mengembalikan daftar provinsi dengan pagination.

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, max: 100) |
| `search` | string | Pencarian berdasarkan nama atau kode |
| `codes[]` | array | Filter berdasarkan kode provinsi tertentu |

#### Contoh Request

```bash
curl "https://your-app.com/nusa/provinces?search=jawa&per_page=10"
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
      "coordinates": [...],
      "postal_codes": ["16110", "16111", "..."]
    },
    {
      "code": "33",
      "name": "Jawa Tengah",
      "latitude": -6.9934809206806,
      "longitude": 110.42024335421,
      "coordinates": [...],
      "postal_codes": ["50111", "50112", "..."]
    }
  ],
  "links": {
    "first": "https://your-app.com/nusa/provinces?page=1",
    "last": "https://your-app.com/nusa/provinces?page=3",
    "prev": null,
    "next": "https://your-app.com/nusa/provinces?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 38
  }
}
```

### Dapatkan Provinsi Spesifik

```http
GET /nusa/provinces/{code}
```

Mengembalikan detail provinsi berdasarkan kode.

#### Parameter Path

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `code` | string | Kode provinsi (contoh: "33") |

#### Contoh Request

```bash
curl "https://your-app.com/nusa/provinces/33"
```

#### Contoh Response

```json
{
  "data": {
    "code": "33",
    "name": "Jawa Tengah",
    "latitude": -6.9934809206806,
    "longitude": 110.42024335421,
    "coordinates": [
      [110.1234, -6.5678],
      [110.2345, -6.6789],
      ...
    ],
    "postal_codes": [
      "50111", "50112", "50113", "50114", "50115",
      "50116", "50117", "50118", "50119", "50121"
    ],
    "regencies_count": 35,
    "districts_count": 573,
    "villages_count": 7809
  }
}
```

### Kabupaten/Kota dalam Provinsi

```http
GET /nusa/provinces/{code}/regencies
```

Mengembalikan semua kabupaten/kota dalam provinsi tertentu.

#### Parameter Path

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `code` | string | Kode provinsi |

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, max: 100) |
| `search` | string | Pencarian berdasarkan nama |
| `type` | string | Filter berdasarkan tipe (`city`, `regency`) |

#### Contoh Request

```bash
curl "https://your-app.com/nusa/provinces/33/regencies?type=city"
```

#### Contoh Response

```json
{
  "data": [
    {
      "code": "33.71",
      "name": "Kota Magelang",
      "province_code": "33",
      "latitude": -7.4697,
      "longitude": 110.2175,
      "type": "city"
    },
    {
      "code": "33.72",
      "name": "Kota Surakarta",
      "province_code": "33",
      "latitude": -7.5755,
      "longitude": 110.8243,
      "type": "city"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 6,
    "per_page": 15
  }
}
```

### Kecamatan dalam Provinsi

```http
GET /nusa/provinces/{code}/districts
```

Mengembalikan semua kecamatan dalam provinsi tertentu.

#### Parameter Path

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `code` | string | Kode provinsi |

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, max: 100) |
| `search` | string | Pencarian berdasarkan nama |
| `regency_code` | string | Filter berdasarkan kode kabupaten/kota |

#### Contoh Request

```bash
curl "https://your-app.com/nusa/provinces/33/districts?regency_code=33.74"
```

### Kelurahan/Desa dalam Provinsi

```http
GET /nusa/provinces/{code}/villages
```

Mengembalikan semua kelurahan/desa dalam provinsi tertentu.

#### Parameter Path

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `code` | string | Kode provinsi |

#### Parameter Query

| Parameter | Type | Deskripsi |
|-----------|------|-----------|
| `page` | integer | Nomor halaman (default: 1) |
| `per_page` | integer | Item per halaman (default: 15, max: 100) |
| `search` | string | Pencarian berdasarkan nama |
| `regency_code` | string | Filter berdasarkan kode kabupaten/kota |
| `district_code` | string | Filter berdasarkan kode kecamatan |
| `postal_code` | string | Filter berdasarkan kode pos |

## Model Eloquent

### Atribut

| Atribut | Type | Deskripsi |
|---------|------|-----------|
| `code` | string | Kode provinsi unik (2 digit) |
| `name` | string | Nama provinsi |
| `latitude` | decimal | Koordinat lintang pusat provinsi |
| `longitude` | decimal | Koordinat bujur pusat provinsi |
| `coordinates` | array | Array koordinat batas wilayah |
| `postal_codes` | array | Array kode pos dalam provinsi |

### Relasi

#### Kabupaten/Kota

```php
$province = Province::find('33');
$regencies = $province->regencies;

// Dengan eager loading
$province = Province::with('regencies')->find('33');
```

#### Kecamatan

```php
$province = Province::find('33');
$districts = $province->districts;

// Dengan eager loading
$province = Province::with('districts')->find('33');
```

#### Kelurahan/Desa

```php
$province = Province::find('33');
$villages = $province->villages;

// Dengan eager loading dan pagination
$villages = $province->villages()->paginate(100);
```

### Scope

#### Pencarian

```php
// Pencarian berdasarkan nama
$provinces = Province::search('jawa')->get();

// Pencarian dengan multiple terms
$provinces = Province::search('jawa tengah')->get();
```

#### Filter Berdasarkan Wilayah

```php
// Provinsi di Pulau Jawa
$javaProvinces = Province::whereIn('code', ['31', '32', '33', '34', '35', '36'])->get();

// Provinsi di luar Jawa
$outsideJava = Province::whereNotIn('code', ['31', '32', '33', '34', '35', '36'])->get();
```

### Accessor

#### Jumlah Wilayah

```php
$province = Province::find('33');

echo $province->regencies_count;  // Jumlah kabupaten/kota
echo $province->districts_count;  // Jumlah kecamatan
echo $province->villages_count;   // Jumlah kelurahan/desa
```

#### Informasi Geografis

```php
$province = Province::find('33');

echo $province->center_coordinates; // [latitude, longitude]
echo $province->bounding_box;       // [min_lat, min_lng, max_lat, max_lng]
```

## Contoh Penggunaan

### Dasar

```php
use Creasi\Nusa\Models\Province;

// Dapatkan semua provinsi
$provinces = Province::all();

// Dapatkan provinsi berdasarkan kode
$jateng = Province::find('33');

// Pencarian provinsi
$javaProvinces = Province::search('jawa')->get();
```

### Dengan Relasi

```php
// Dapatkan provinsi dengan kabupaten/kota
$province = Province::with('regencies')->find('33');

foreach ($province->regencies as $regency) {
    echo $regency->name . "\n";
}

// Dapatkan provinsi dengan semua level
$province = Province::with([
    'regencies.districts.villages'
])->find('33');
```

### Pagination

```php
// Pagination sederhana
$provinces = Province::paginate(10);

// Pagination dengan pencarian
$provinces = Province::search('jawa')->paginate(5);

// Pagination dengan relasi
$provinces = Province::with('regencies')->paginate(10);
```

### Statistik

```php
// Hitung total wilayah per provinsi
$stats = Province::withCount([
    'regencies',
    'districts', 
    'villages'
])->get();

foreach ($stats as $province) {
    echo "{$province->name}:\n";
    echo "- Kabupaten/Kota: {$province->regencies_count}\n";
    echo "- Kecamatan: {$province->districts_count}\n";
    echo "- Kelurahan/Desa: {$province->villages_count}\n\n";
}
```

## Error Handling

### Error Umum

#### Provinsi Tidak Ditemukan (404)

```json
{
  "message": "No query results for model [Creasi\\Nusa\\Models\\Province] 99",
  "exception": "Illuminate\\Database\\Eloquent\\ModelNotFoundException"
}
```

#### Error Validasi (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "per_page": ["The per page must not be greater than 100."]
  }
}
```

### Contoh Error Handling

```js
async function getProvince(code) {
  try {
    const response = await fetch(`/nusa/provinces/${code}`);

    if (!response.ok) {
      if (response.status === 404) {
        throw new Error(`Provinsi dengan kode ${code} tidak ditemukan`);
      }
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Error mengambil data provinsi:', error);
    throw error;
  }
}
```

```php
// PHP Error Handling
try {
    $province = Province::findOrFail('99');
} catch (ModelNotFoundException $e) {
    return response()->json([
        'error' => 'Provinsi tidak ditemukan',
        'message' => 'Kode provinsi yang diminta tidak valid'
    ], 404);
}
```

## Tips Performa

### Caching

```php
use Illuminate\Support\Facades\Cache;

// Cache daftar provinsi
$provinces = Cache::remember('provinces.all', 3600, function () {
    return Province::all();
});

// Cache provinsi dengan relasi
$province = Cache::remember("province.{$code}.with_regencies", 3600, function () use ($code) {
    return Province::with('regencies')->find($code);
});
```

### Eager Loading

```php
// ❌ N+1 Problem
$provinces = Province::all();
foreach ($provinces as $province) {
    echo $province->regencies->count(); // Query untuk setiap provinsi
}

// ✅ Eager Loading
$provinces = Province::with('regencies')->get();
foreach ($provinces as $province) {
    echo $province->regencies->count(); // Tidak ada query tambahan
}
```

### Select Kolom Spesifik

```php
// ❌ Select semua kolom
$provinces = Province::all();

// ✅ Select kolom yang diperlukan saja
$provinces = Province::select(['code', 'name'])->get();

// ✅ Untuk dropdown
$provinceOptions = Province::pluck('name', 'code');
```

### Pagination Efisien

```php
// ❌ Pagination dengan count() yang mahal
$provinces = Province::with('regencies')->paginate(15);

// ✅ Pagination tanpa count untuk dataset besar
$provinces = Province::with('regencies')->simplePaginate(15);

// ✅ Cursor pagination untuk performa terbaik
$provinces = Province::with('regencies')->cursorPaginate(15);
```

### Database Indexing

```php
// Pastikan index ada untuk pencarian
// Migration example:
Schema::table('provinces', function (Blueprint $table) {
    $table->index(['name']); // Untuk pencarian nama
    $table->index(['code']); // Untuk pencarian kode (biasanya sudah ada)
});
```

### Optimisasi Query

```php
// ❌ Query terpisah
$province = Province::find('33');
$regencyCount = $province->regencies()->count();
$districtCount = $province->districts()->count();

// ✅ Query dengan aggregate
$province = Province::withCount(['regencies', 'districts'])->find('33');
echo $province->regencies_count;
echo $province->districts_count;
```

### Memory Management

```php
// ❌ Load semua data sekaligus
$allVillages = Village::where('province_code', '33')->get(); // Bisa jutaan record!

// ✅ Gunakan chunking
Village::where('province_code', '33')->chunk(1000, function ($villages) {
    foreach ($villages as $village) {
        // Process village
    }
});

// ✅ Atau lazy loading
foreach (Village::where('province_code', '33')->lazy() as $village) {
    // Process village satu per satu
}
```
