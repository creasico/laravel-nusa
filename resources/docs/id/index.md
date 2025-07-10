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

## Mengapa Memilih Laravel Nusa?

Larave Nusa menyediakan solusi untuk kendala integrasi dengan wilayah - wilayah administratif Indonesia kedalam aplikasi Laravel. Alih-alih membuang waktu untuk mengimpor dan me-maintain data secara manual, Anda akan langsung mendapatkan manfaat sebagai berikut:

- **Siap Pakai**: Database SQLite siap pakai dengan data lengkap tanpa perlu konfigurasi manual
- **Data Resmi Terpercaya**: Bersumber langsung dari database resmi milik instansi pemerintah yang berwenang
- **Performa Optimal**: Struktur database telah dioptimalkan dan dilengkapi dengan indexing yang efisien
- **Maintenance Otomatis**: Update otomatis saat terjadi perubahan data di sumber resmi, tanpa campur tangan manual.
- **Privasi Terjamin**: Versi distribusi tidak menyertakan data koordinat sensitif demi menjaga keamanan informasi


### 🏢 Cocok untuk

- **E-Commerce**: Zona pengiriman dan optimasi logistik
- **Layanan Kesehatan**: Manajemen fasilitas dan demografi pasien
- **Layanan Pemerintah**: Manajemen warga dan pelaporan administratif
- **Aplikasi Bisnis**: Analisis regional dan perencanaan ekspansi

### 🚀 Instalasi
Install package menggunakan Composer:
```bash
composer require creasi/laravel-nusa
```

### 💡 Cara Penggunaan

```php
use Creasi\Nusa\Models\Province;

// Ambil semua provinsi
$provinces = Province::all();

// cari berdasarkan nama atau kode
$jateng = Province::search('Jawa Tengah')->first();
$jateng = Province::search('33')->first();

// akses relasi data
$regencies = $jateng->regencies;
$districts = $jateng->districts;
$villages = $jateng->villages;
```

### 🌐 Contoh API

Akses data melalui endpoint RESTful:

```bash
# Semua provinsi
GET /nusa/provinces

# Provinsi spesifik
GET /nusa/provinces/33

# kabupaten/kota dalam provinsi
GET /nusa/provinces/33/regencies

# Pencarian dengan parameter query
GET /nusa/villages?search=jakarta&codes[]=31.71
```

### 📍 Pengaturan Alamat

Integrasikan fitur alamat ke dalam model Anda dengan mudah:

```php
use Creasi\Nusa\Contracts\HasAddresses;
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model implements HasAddresses
{
    use WithAddresses;
}

// Tambah alamat pada user
$user->addresses()->create([
    'province_code' => '33',
    'regency_code' => '33.75',
    'district_code' => '33.75.01',
    'village_code' => '33.75.01.1002',
    'address_line' => 'Jl. Merdeka No. 123'
]);
```

## Coba Sekarang

<div class="vp-doc">

[Panduan Instalasi →](/id/guide/installation)

[Contoh Penggunaan →](/id/examples/basic-usage)

[Referensi API →](/id/api/overview)

</div>
