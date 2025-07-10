---
layout: home

hero:
  name: "Laravel Nusa"
  text: "Data Wilayah Administratif Indonesia"
  tagline: Solusi lengkap untuk mengelola data provinsi, kabupaten/kota, kecamatan, dan kelurahan/desa di aplikasi Laravel Anda
  image:
    src: /logo.svg
    alt: Laravel Nusa
  actions:
    - theme: brand
      text: Mulai Sekarang
      link: /id/panduan/memulai
    - theme: alt
      text: Lihat di GitHub
      link: https://github.com/creasico/laravel-nusa

features:
  - icon: 🗺️
    title: Data Lengkap
    details: Akses ke seluruh hierarki administratif Indonesia - 34 provinsi, 514 kabupaten/kota, 7.266 kecamatan, dan 83.467 kelurahan/desa
  - icon: ⚡
    title: Performa Optimal
    details: Model Eloquent yang dioptimalkan dengan relasi yang efisien untuk menangani dataset besar dengan performa tinggi
  - icon: 🔧
    title: Mudah Dikustomisasi
    details: Trait yang fleksibel untuk mengintegrasikan data wilayah ke dalam model aplikasi Anda dengan mudah
  - icon: 📍
    title: Manajemen Alamat
    details: Sistem manajemen alamat lengkap dengan validasi dan relasi ke data administratif resmi
  - icon: 🌐
    title: RESTful API
    details: API endpoint yang lengkap untuk mengakses data wilayah dengan format JSON yang konsisten
  - icon: 📊
    title: Data Resmi
    details: Data yang disinkronisasi dengan sumber resmi pemerintah Indonesia untuk memastikan akurasi dan kelengkapan
---

## Mengapa Memilih Laravel Nusa?

Laravel Nusa menyediakan solusi komprehensif untuk mengelola data wilayah administratif Indonesia dalam aplikasi Laravel. Dengan lebih dari 83.000 data kelurahan/desa yang akurat dan up-to-date, paket ini membantu developer membangun aplikasi yang membutuhkan fitur lokasi dengan mudah dan efisien.

### 🚀 Instalasi Cepat

```bash
composer require creasi/laravel-nusa
php artisan nusa:install
```

### 💡 Penggunaan Sederhana

```php
use Creasi\Nusa\Models\Province;

// Dapatkan semua provinsi
$provinces = Province::all();

// Cari provinsi berdasarkan nama
$jateng = Province::search('Jawa Tengah')->first();

// Akses relasi hierarkis
$villages = $jateng->villages; // Semua desa di Jawa Tengah
```

### 🏢 Kasus Penggunaan Umum

- **E-Commerce**: Zona pengiriman dan optimasi logistik
- **Layanan Kesehatan**: Manajemen fasilitas dan demografi pasien
- **Layanan Pemerintah**: Manajemen warga dan pelaporan administratif
- **Aplikasi Bisnis**: Analisis regional dan perencanaan ekspansi

### 🌐 Contoh API

Akses data melalui endpoint RESTful yang bersih:

```bash
# Dapatkan semua provinsi
GET /nusa/provinces

# Dapatkan provinsi tertentu
GET /nusa/provinces/33

# Dapatkan kabupaten/kota dalam provinsi
GET /nusa/provinces/33/regencies

# Pencarian dengan parameter query
GET /nusa/villages?search=jakarta&codes[]=31.71
```

### 📍 Manajemen Alamat

Integrasikan fitur alamat ke dalam model Anda dengan mudah:

```php
use Creasi\Nusa\Contracts\HasAddresses;
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model implements HasAddresses
{
    use WithAddresses;
}

// Sekarang user dapat memiliki alamat
$user->addresses()->create([
    'province_code' => '33',
    'regency_code' => '33.75',
    'district_code' => '33.75.01',
    'village_code' => '33.75.01.1002',
    'address_line' => 'Jl. Merdeka No. 123'
]);
```

## Mulai Sekarang

<div class="vp-doc">

[Panduan Instalasi →](/id/guide/installation)

[Contoh Penggunaan →](/id/examples/basic-usage)

[Referensi API →](/id/api/overview)

</div>
