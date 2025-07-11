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
    details: Akses ke seluruh wilayah administratif Indonesia - 38 provinsi, 514 kabupaten/kota, 7.285 kecamatan, dan 83.762 kelurahan/desa
  - icon: 🚀
    title: Siap Pakai
    details: Dengan embed SQLite database, hanya cukup install dan tinggal pakai
  - icon: 🌐
    title: RESTful API
    details: Tersedia API endpoint dengan pagination, search, dan filter untuk semua wilayah
  - icon: 📍
    title: Data Geografis
    details: Termasuk koordinat, batas wilayah serta lengkap dengan kode pos
  - icon: 🔧
    title: Integrasi dengan Laravel
    details: Eloquent model dengan relasi yang sesuai, traits untuk mengatur alamat dan fitur Laravel-native
  - icon: 🔄
    title: Update Otomatis
    details: Data di-singkronkan secara otomatis dengan data resmi dari Pemerintah
---

## Mengapa Laravel Nusa?

Laravel Nusa memecahkan tantangan umum dalam mengintegrasikan data administratif Indonesia ke dalam aplikasi Laravel. Alih-alih mengimpor dan memelihara dataset besar secara manual, Anda mendapatkan:

- **Setup Instan**: Database SQLite yang sudah dikemas dengan semua data siap pakai
- **Data Resmi**: Bersumber dari database pemerintah yang berwenang
- **Performa**: Struktur database yang dioptimalkan dengan indexing yang tepat
- **Pemeliharaan**: Update otomatis ketika data resmi berubah
- **Privasi**: Versi distribusi mengecualikan data koordinat sensitif


### 🏢 Paling Cocok Untuk

- **E-Commerce**: Optimalkan rute pengiriman dan tentukan zona pengiriman yang efisien untuk merampingkan logistik.
- **Layanan Kesehatan**: Kelola cakupan fasilitas dan pahami demografi pasien untuk perencanaan layanan yang lebih baik
- **Layanan Publik**: Berdayakan manajemen warga dan sederhanakan pelaporan administratif dengan wawasan spasial.
- **Aplikasi Bisnis**: Analisis performa regional dan rencanakan ekspansi strategis dengan analitik berbasis lokasi

## 🚀 Mulai Cepat

Instal paket melalui Composer:

```bash
composer require creasi/laravel-nusa
```

Mulai gunakan langsung:

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

Akses data melalui endpoint RESTful yang bersih:

```http
# Dapatkan semua provinsi
GET /nusa/provinces

# Dapatkan provinsi tertentu
GET /nusa/provinces/33

# Dapatkan kabupaten/kota dalam provinsi
GET /nusa/provinces/33/regencies

# Cari dengan parameter query
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
