# Memulai

Panduan ini akan membantu Anda dengan cepat menginstal dan mulai menggunakan Laravel Nusa di aplikasi Laravel Anda.

## Instalasi Cepat

Instal Laravel Nusa melalui Composer:

```bash
composer require creasi/laravel-nusa
```

Itu saja! Laravel Nusa sekarang siap digunakan. Paket ini meliputi:

- ✅ Database SQLite yang sudah dibuat sebelumnya dengan semua data administratif Indonesia
- ✅ Registrasi penyedia layanan otomatis
- ✅ Konfigurasi koneksi database
- ✅ Rute API RESTful (opsional)

::: tip Persyaratan
Laravel Nusa membutuhkan **PHP ≥ 8.2** dengan ekstensi `php-sqlite3` dan **Laravel ≥ 9.0**. Untuk persyaratan sistem dan pemecahan masalah yang lebih rinci, lihat [Panduan Instalasi](/id/guide/installation).
:::

## Verifikasi Instalasi

Mari kita verifikasi instalasi berfungsi dengan benar:

```php
use Creasi\Nusa\Models\Province;

// Uji fungsionalitas dasar
$provinces = Province::all();
echo "Total provinsi: " . $provinces->count(); // Seharusnya menghasilkan: 34

// Uji fungsionalitas pencarian
$jateng = Province::search('Jawa Tengah')->first();
echo "Ditemukan: " . $jateng->name; // Seharusnya menghasilkan: Jawa Tengah
```

Jika ini berfungsi, Anda siap! Jika Anda mengalami masalah, periksa [Panduan Instalasi](/id/guide/installation) untuk pemecahan masalah.

## Langkah Pertama dengan Laravel Nusa

### 1. Memahami Struktur Data

Laravel Nusa menyediakan hierarki lengkap wilayah administratif Indonesia:

```
Indonesia
🇮🇩 Indonesia
├── 38 Provinsi
├── 514 Kabupaten/Kota
├── 7.285 Kecamatan
└── 83.762 Desa/Kelurahan
```

Setiap tingkat memiliki format kode tertentu:
- **Provinsi**: `33` (2 digit)
- **Kabupaten/Kota**: `33.75` (provinsi.kabupaten/kota)
- **Kecamatan**: `33.75.01` (provinsi.kabupaten/kota.kecamatan)
- **Desa/Kelurahan**: `33.75.01.1002` (provinsi.kabupaten/kota.kecamatan.desa/kelurahan)

### 2. Kueri Dasar

```php
use Creasi\Nusa\Models\{Province, Regency, District, Village};

// Temukan berdasarkan kode
$province = Province::find('33');              // Jawa Tengah
$regency = Regency::find('33.75');            // Kota Pekalongan
$district = District::find('33.75.01');       // Pekalongan Barat
$village = Village::find('33.75.01.1002');    // Desa Medono

// Cari berdasarkan nama (tidak peka huruf besar/kecil)
$provinces = Province::search('jawa')->get();
$regencies = Regency::search('semarang')->get();

// Dapatkan dengan relasi
$province = Province::with('regencies')->find('33');
$regencies = $province->regencies; // Semua kabupaten/kota di Jawa Tengah
```

### 3. Membangun Formulir Alamat

Kasus penggunaan umum adalah membangun formulir *dropdown* bertingkat:

```php
// Dapatkan provinsi untuk dropdown pertama
$provinces = Province::orderBy('name')->get(['code', 'name']);

// Ketika pengguna memilih provinsi, dapatkan kabupaten/kotanya
$regencies = Regency::where('province_code', '33')
    ->orderBy('name')
    ->get(['code', 'name']);

// Ketika pengguna memilih kabupaten/kota, dapatkan kecamatannya
$districts = District::where('regency_code', '33.75')
    ->orderBy('name')
    ->get(['code', 'name']);

// Ketika pengguna memilih kecamatan, dapatkan desa/kelurahannya
$villages = Village::where('district_code', '33.75.01')
    ->orderBy('name')
    ->get(['code', 'name', 'postal_code']);
```

### 4. Menggunakan API

Laravel Nusa menyediakan *endpoint* API RESTful secara *out of the box*:

```bash
# Dapatkan semua provinsi
curl http://your-app.test/nusa/provinces

# Dapatkan kabupaten/kota di provinsi
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

Sekarang setelah Anda memahami dasar-dasarnya, jelajahi panduan ini:

- **[Contoh Penggunaan Dasar](/id/examples/basic-usage)** - Pola penggunaan dan contoh yang lebih rinci
- **[Formulir Alamat](/id/examples/address-forms)** - Implementasi formulir alamat lengkap
- **[Model & Relasi](/id/guide/models)** - Penjelasan mendalam tentang model Eloquent
- **[API RESTful](/id/guide/api)** - Menggunakan *endpoint* API bawaan
- **[Konfigurasi](/id/guide/configuration)** - Menyesuaikan Laravel Nusa untuk kebutuhan Anda

## Butuh Bantuan?

- **Masalah Instalasi**: Lihat [Panduan Instalasi](/id/guide/installation) untuk pengaturan dan pemecahan masalah yang rinci
- **Pertanyaan Penggunaan**: Periksa bagian [Contoh](/id/examples/basic-usage) untuk pola umum
- **Referensi API**: Jelajahi [Dokumentasi API](/id/api/overview) untuk detail *endpoint* lengkap