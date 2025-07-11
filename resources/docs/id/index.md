---
layout: home

hero:
  name: "Laravel Nusa"
  text: "Data Administratif Indonesia"
  tagline: Data provinsi, kabupaten, kecamatan, dan desa di Indonesia yang siap pakai untuk aplikasi Laravel
  image:
    src: /logo.svg
    alt: Laravel Nusa
  actions:
    - theme: brand
      text: Mulai
      link: /id/guide/getting-started
    - theme: alt
      text: Lihat di GitHub
      link: https://github.com/creasico/laravel-nusa

features:
  - icon: 🗺️
    title: Data Administratif Lengkap
    details: Semua 38 provinsi, 514 kabupaten/kota, 7.285 kecamatan, dan 83.762 desa/kelurahan dengan kode dan nama resmi
  - icon: 🚀
    title: Tanpa Konfigurasi
    details: Termasuk database SQLite yang siap pakai. Tidak perlu seeding atau migrasi - cukup instal dan gunakan
  - icon: 🌐
    title: API RESTful
    details: Endpoint API bawaan dengan paginasi, pencarian, dan pemfilteran untuk semua tingkat administratif
  - icon: 📍
    title: Data Geografis
    details: Termasuk koordinat, batas wilayah, dan kode pos untuk layanan lokasi yang komprehensif
  - icon: 🔧
    title: Integrasi Laravel
    details: Model Eloquent dengan relasi, trait untuk manajemen alamat, dan fitur-fitur native Laravel
  - icon: 🔄
    title: Pembaruan Otomatis
    details: Secara otomatis disinkronkan dengan sumber data resmi pemerintah melalui alur kerja otomatis
---

## Mengapa Laravel Nusa?

Laravel Nusa memecahkan tantangan umum dalam mengintegrasikan data administratif Indonesia ke dalam aplikasi Laravel. Daripada mengimpor dan memelihara dataset besar secara manual, Anda mendapatkan:

- **Pengaturan Instan**: Database SQLite yang sudah dikemas dengan semua data siap pakai
- **Data Resmi**: Bersumber dari database pemerintah yang berwenang
- **Kinerja**: Struktur database yang dioptimalkan dengan pengindeksan yang tepat
- **Pemeliharaan**: Pembaruan otomatis saat data resmi berubah
- **Privasi**: Versi distribusi tidak termasuk data koordinat sensitif


### 🏢 Paling Cocok Untuk

- **E-Commerce**: Optimalkan rute pengiriman dan tentukan zona pengiriman yang efisien untuk menyederhanakan logistik.
- **Kesehatan**: Kelola cakupan fasilitas dan pahami demografi pasien untuk perencanaan layanan yang lebih baik
- **Layanan Publik**: Berdayakan manajemen warga dan sederhanakan pelaporan administratif dengan wawasan spasial.
- **Aplikasi Bisnis**: Analisis kinerja regional dan rencanakan ekspansi strategis dengan analitik berbasis lokasi

## 🚀 Mulai Cepat

Instal paket melalui Composer:

```bash
composer require creasi/laravel-nusa
```

Mulai gunakan segera:

```php
use Creasi\Nusa\Models\Province;

// Dapatkan semua provinsi
$provinces = Province::all();

// Cari berdasarkan nama atau kode
$jateng = Province::search('Jawa Tengah')->first();
$jateng = Province::search('33')->first();

// Dapatkan data terkait
$regencies = $jateng->regencies;
$districts = $jateng->districts;
$villages = $jateng->villages;
```

## 🌐 Contoh API

Akses data melalui *endpoint* RESTful yang bersih:

```http
# Dapatkan semua provinsi
GET /nusa/provinces

# Dapatkan provinsi tertentu
GET /nusa/provinces/33

# Dapatkan kabupaten/kota di provinsi
GET /nusa/provinces/33/regencies

# Cari dengan parameter kueri
GET /nusa/villages?search=jakarta&codes[]=31.71
```

## 📍 Manajemen Alamat

Integrasikan fungsionalitas alamat dengan mudah ke dalam model Anda:

```php
use Creasi\Nusa\Contracts\HasAddresses;
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model implements HasAddresses
{
    use WithAddresses;
}

// Sekarang pengguna Anda dapat memiliki alamat
$user->addresses()->create([
    'province_code' => '33',
    'regency_code' => '33.75',
    'district_code' => '33.75.01',
    'village_code' => '33.75.01.1002',
    'address_line' => 'Jl. Merdeka No. 123'
]);
```