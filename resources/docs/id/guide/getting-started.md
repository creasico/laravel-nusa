# Memulai

Panduan ini akan membantu Anda dengan cepat menginstal dan mulai menggunakan Laravel Nusa dalam aplikasi Laravel Anda.

## Instalasi Cepat

Instal Laravel Nusa melalui Composer:

```bash
composer require creasi/laravel-nusa
```

Selesai! Laravel Nusa sekarang siap digunakan. Paket ini mencakup:

- ✅ Database SQLite yang sudah dibangun dengan semua data administratif Indonesia
- ✅ Registrasi service provider otomatis
- ✅ Konfigurasi koneksi database
- ✅ Route RESTful API (opsional)

::: tip Persyaratan
Laravel Nusa memerlukan **PHP ≥ 8.2** dengan ekstensi `php-sqlite3` dan **Laravel ≥ 9.0**. Untuk persyaratan sistem detail dan troubleshooting, lihat [Panduan Instalasi](/id/guide/installation).
:::

## Verifikasi Instalasi

Mari verifikasi bahwa instalasi berfungsi dengan benar:

```php
use Creasi\Nusa\Models\Province;

// Tes fungsionalitas dasar
$provinces = Province::all();
echo "Total provinsi: " . $provinces->count(); // Harus menampilkan: 34

// Tes fungsionalitas pencarian
$jateng = Province::search('Jawa Tengah')->first();
echo "Ditemukan: " . $jateng->name; // Harus menampilkan: Jawa Tengah
```

Jika ini berfungsi, Anda siap untuk memulai! Jika Anda mengalami masalah, periksa [Panduan Instalasi](/id/guide/installation) untuk troubleshooting.

## Langkah Pertama dengan Laravel Nusa

### 1. Memahami Struktur Data

Laravel Nusa menyediakan hierarki lengkap wilayah administratif Indonesia:

```
Indonesia
🇮🇩 Indonesia
├── 38 Provinsi (Provinsi)
├── 514 Kabupaten/Kota (Kabupaten/Kota)
├── 7.285 Kecamatan (Kecamatan)
└── 83.762 Kelurahan/Desa (Kelurahan/Desa)
```

Setiap tingkat memiliki format kode spesifik:
- **Provinsi**: `33` (2 digit)
- **Kabupaten/Kota**: `33.75` (provinsi.kabupaten)
- **Kecamatan**: `33.75.01` (provinsi.kabupaten.kecamatan)
- **Kelurahan/Desa**: `33.75.01.1002` (provinsi.kabupaten.kecamatan.desa)

### 2. Query Dasar

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Cari berdasarkan kode
$province = Province::find('33');              // Jawa Tengah
$regency = Regency::find('33.75');            // Kota Pekalongan
$district = District::find('33.75.01');       // Pekalongan Barat
$village = Village::find('33.75.01.1002');    // Kelurahan Medono

// Cari berdasarkan nama (case-insensitive)
$provinces = Province::search('jawa')->get();
$regencies = Regency::search('semarang')->get();

// Dapatkan dengan relasi
$province = Province::with('regencies')->find('33');
$regencies = $province->regencies; // Semua kabupaten/kota di Jawa Tengah
```

### 3. Membangun Form Alamat

Kasus penggunaan umum adalah membangun form dropdown bertingkat:

```php
// Dapatkan provinsi untuk dropdown pertama
$provinces = Province::orderBy('name')->get(['code', 'name']);

// Ketika user memilih provinsi, dapatkan kabupaten/kotanya
$regencies = Regency::where('province_code', '33')
    ->orderBy('name')
    ->get(['code', 'name']);

// Ketika user memilih kabupaten/kota, dapatkan kecamatannya
$districts = District::where('regency_code', '33.75')
    ->orderBy('name')
    ->get(['code', 'name']);

// Ketika user memilih kecamatan, dapatkan kelurahan/desanya
$villages = Village::where('district_code', '33.75.01')
    ->orderBy('name')
    ->get(['code', 'name', 'postal_code']);
```

### 4. Menggunakan API

Laravel Nusa menyediakan endpoint RESTful API siap pakai:

```bash
# Dapatkan semua provinsi
curl http://your-app.test/nusa/provinces

# Dapatkan kabupaten/kota dalam provinsi
curl http://your-app.test/nusa/provinces/33/regencies

# Cari lokasi
curl "http://your-app.test/nusa/regencies?search=jakarta"
```

### 5. Bekerja dengan Data Geografis

Akses koordinat dan kode pos:

```php
$province = Province::find('33');

// Dapatkan koordinat pusat
echo "Pusat: {$province->latitude}, {$province->longitude}";

// Dapatkan semua kode pos di provinsi ini
$postalCodes = $province->postal_codes;
echo "Kode pos: " . implode(', ', $postalCodes);

// Dapatkan koordinat batas (jika tersedia)
if ($province->coordinates) {
    echo "Memiliki " . count($province->coordinates) . " titik batas";
}
```

## Langkah Selanjutnya

Sekarang Anda memahami dasar-dasarnya, jelajahi panduan berikut:

- **[Contoh Penggunaan Dasar](/id/examples/basic-usage)** - Pola penggunaan dan contoh yang lebih detail
- **[Form Alamat](/id/examples/address-forms)** - Implementasi form alamat lengkap
- **[Model & Relasi](/id/guide/models)** - Mendalami model Eloquent
- **[RESTful API](/id/guide/api)** - Menggunakan endpoint API bawaan
- **[Konfigurasi](/id/guide/configuration)** - Menyesuaikan Laravel Nusa untuk kebutuhan Anda

## Butuh Bantuan?

- **Masalah Instalasi**: Lihat [Panduan Instalasi](/id/guide/installation) untuk setup detail dan troubleshooting
- **Pertanyaan Penggunaan**: Periksa bagian [Contoh](/id/examples/basic-usage) untuk pola umum
- **Referensi API**: Jelajahi [Dokumentasi API](/id/api/overview) untuk detail endpoint lengkap
