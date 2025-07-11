# Apa itu Laravel Nusa?

Laravel Nusa adalah paket Laravel komprehensif yang menyediakan data wilayah administratif Indonesia lengkap, termasuk provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan. Ini dirancang agar "siap digunakan setelah diinstal" tanpa memerlukan pengaturan kompleks atau proses migrasi data.

## Masalah yang Dipecahkan

Saat membangun aplikasi yang melayani pengguna Indonesia, pengembang seringkali membutuhkan akses ke data wilayah administratif untuk:

- **Formulir alamat** dengan *dropdown* bertingkat
- **Layanan berbasis lokasi** dan pemfilteran
- Perhitungan **biaya pengiriman dan logistik**
- Persyaratan **kepatuhan pemerintah**
- **Validasi data** dan standardisasi

Secara tradisional, ini berarti:
- ❌ Mengunduh dan mengimpor dataset besar secara manual
- ❌ Menjalankan *seeder* database yang memakan waktu
- ❌ Memelihara sinkronisasi data dengan sumber resmi
- ❌ Menangani format data yang tidak konsisten
- ❌ Mengelola kinerja database dengan dataset besar

## Solusi Laravel Nusa

Laravel Nusa menghilangkan tantangan-tantangan ini dengan menyediakan:

- ✅ **Database SQLite yang sudah dikemas** dengan semua data siap digunakan
- ✅ **Pengaturan tanpa konfigurasi** - cukup instal dan mulai gunakan
- ✅ **Pembaruan otomatis** dari sumber resmi pemerintah
- ✅ **Kinerja yang dioptimalkan** dengan pengindeksan dan relasi yang tepat
- ✅ **API yang bersih** dengan model Eloquent dan *endpoint* RESTful

## Fitur Utama

### Hierarki Administratif Lengkap

```
Indonesia
└── 34 Provinces (Provinsi)
    └── 514 Regencies (Kabupaten/Kota)
        └── 7,285 Districts (Kecamatan)
            └── 83,762 Villages (Kelurahan/Desa)
```

### Atribut Data yang Kaya

- **Kode resmi** mengikuti standar pemerintah
- **Koordinat geografis** untuk pemetaan dan layanan lokasi
- **Kode pos** untuk pengiriman dan logistik
- **Data batas wilayah** untuk analisis geografis
- **Relasi hierarkis** untuk kueri yang efisien

### Integrasi Laravel-Native

- **Model Eloquent** dengan relasi yang tepat
- **Migrasi database** untuk manajemen alamat
- **Penyedia layanan** untuk konfigurasi otomatis
- **Perintah Artisan** untuk manajemen data
- **Trait** untuk integrasi model yang mudah

## Sumber Data

Laravel Nusa mengintegrasikan data dari beberapa sumber otoritatif:

- **[cahyadsn/wilayah](https://github.com/cahyadsn/wilayah)** - Data administratif inti
- **[cahyadsn/wilayah_kodepos](https://github.com/cahyadsn/wilayah_kodepos)** - Pemetaan kode pos
- **[cahyadsn/wilayah_boundaries](https://github.com/cahyadsn/wilayah_boundaries)** - Batas geografis

Sumber-sumber ini secara otomatis dipantau dan diperbarui melalui alur kerja GitHub Actions.

## Filosofi Arsitektur

### Pendekatan Tanpa Konfigurasi

Tidak seperti paket lain yang memerlukan *seeding* manual, Laravel Nusa dilengkapi dengan database SQLite yang sudah dibuat sebelumnya yang berisi semua data. Ini berarti:

- **Ketersediaan instan** setelah instalasi
- **Tidak ada dampak pada kinerja database utama** Anda
- **Data yang konsisten** di semua instalasi
- **Penerapan yang mudah** tanpa langkah pengaturan tambahan

### Desain Mengutamakan Privasi

Paket ini memelihara dua versi database:

- **Database pengembangan** (~407MB) - Termasuk data koordinat lengkap untuk pengembangan
- **Database distribusi** (~10MB) - Koordinat dihapus untuk kepatuhan privasi

### Optimasi Kinerja

- **Koneksi database terpisah** untuk menghindari konflik
- **Pengindeksan yang tepat** untuk kueri cepat
- **Relasi yang efisien** menggunakan kunci asing
- **Dukungan paginasi** untuk dataset besar

## Kasus Penggunaan

Laravel Nusa sangat cocok untuk:

### Aplikasi E-commerce
- Formulir alamat dengan validasi *real-time*
- Perhitungan biaya pengiriman
- Ketersediaan produk regional

### Sistem Pemerintahan
- Formulir pendaftaran warga
- Pelaporan administratif
- Kepatuhan terhadap standar resmi

### Layanan Berbasis Lokasi
- Pencari toko
- Pemetaan area layanan
- Analisis geografis

### Analisis Data
- Analisis kinerja regional
- Studi demografi
- Riset pasar

## Langkah Selanjutnya

Siap untuk memulai? Periksa panduan [Memulai](/id/guide/getting-started) kami untuk menginstal dan mengkonfigurasi Laravel Nusa di aplikasi Anda.