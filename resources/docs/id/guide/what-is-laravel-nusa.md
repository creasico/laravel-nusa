# Apa itu Laravel Nusa?

Laravel Nusa adalah paket Laravel komprehensif yang menyediakan data wilayah administratif Indonesia lengkap, termasuk provinsi, kabupaten/kota, kecamatan, dan kelurahan/desa. Paket ini dirancang untuk "siap pakai setelah diinstal" tanpa memerlukan proses setup kompleks atau migrasi data.

## Masalah yang Dipecahkan

Ketika membangun aplikasi yang melayani pengguna Indonesia, developer sering membutuhkan akses ke data wilayah administratif untuk:

- **Form alamat** dengan dropdown bertingkat
- **Layanan berbasis lokasi** dan filtering
- **Kalkulasi pengiriman dan logistik**
- **Persyaratan kepatuhan pemerintah**
- **Validasi dan standardisasi data**

Secara tradisional, ini berarti:
- ❌ Mengunduh dan mengimpor dataset besar secara manual
- ❌ Menjalankan database seeder yang memakan waktu
- ❌ Memelihara sinkronisasi data dengan sumber resmi
- ❌ Menangani format data yang tidak konsisten
- ❌ Mengelola performa database dengan dataset besar

## Solusi Laravel Nusa

Laravel Nusa menghilangkan tantangan ini dengan menyediakan:

- ✅ **Database SQLite yang sudah dikemas** dengan semua data siap pakai
- ✅ **Setup tanpa konfigurasi** - cukup instal dan mulai gunakan
- ✅ **Update otomatis** dari sumber resmi pemerintah
- ✅ **Performa yang dioptimalkan** dengan indexing dan relasi yang tepat
- ✅ **API yang bersih** dengan model Eloquent dan endpoint RESTful

## Fitur Utama

### Hierarki Administratif Lengkap

```
🇮🇩 Indonesia
├── 38 Provinsi
├── 514 Kabupaten/Kota
├── 7,285 Kecamatan
└── 83,762 Kelurahan/Desa
```

### Atribut Data Kaya

- **Kode resmi** mengikuti standar pemerintah
- **Koordinat geografis** untuk pemetaan dan layanan lokasi
- **Kode pos** untuk pengiriman dan logistik
- **Data batas** untuk analisis geografis
- **Relasi hierarkis** untuk query yang efisien

### Integrasi Native Laravel

- **Model Eloquent** dengan relasi yang tepat
- **Migrasi database** untuk manajemen alamat
- **Service provider** untuk konfigurasi otomatis
- **Perintah Artisan** untuk manajemen data
- **Trait** untuk integrasi model yang mudah

## Sumber Data

Laravel Nusa mengintegrasikan data dari beberapa sumber otoritatif:

- **[cahyadsn/wilayah](https://github.com/cahyadsn/wilayah)** - Data administratif inti
- **[cahyadsn/wilayah_kodepos](https://github.com/cahyadsn/wilayah_kodepos)** - Pemetaan kode pos
- **[cahyadsn/wilayah_boundaries](https://github.com/cahyadsn/wilayah_boundaries)** - Batas geografis

Sumber-sumber ini dipantau dan diperbarui secara otomatis melalui workflow GitHub Actions.

## Filosofi Arsitektur

### Pendekatan Tanpa Konfigurasi

Tidak seperti paket lain yang memerlukan seeding manual, Laravel Nusa hadir dengan database SQLite yang sudah dibangun berisi semua data. Ini berarti:

- **Ketersediaan instan** setelah instalasi
- **Tidak berdampak pada performa database utama** Anda
- **Data konsisten** di semua instalasi
- **Deployment mudah** tanpa langkah setup tambahan

### Desain Privacy-First

Paket ini memelihara dua versi database:

- **Database development** (~407MB) - Termasuk data koordinat lengkap untuk development
- **Database distribusi** (~10MB) - Koordinat dihapus untuk kepatuhan privasi

### Optimasi Performa

- **Koneksi database terpisah** untuk menghindari konflik
- **Indexing yang tepat** untuk query cepat
- **Relasi efisien** menggunakan foreign key
- **Dukungan pagination** untuk dataset besar

## Kasus Penggunaan

Laravel Nusa sempurna untuk:

### Aplikasi E-commerce
- Form alamat dengan validasi real-time
- Kalkulasi biaya pengiriman
- Ketersediaan produk regional

### Sistem Pemerintah
- Form registrasi warga
- Pelaporan administratif
- Kepatuhan dengan standar resmi

### Layanan Berbasis Lokasi
- Store locator
- Pemetaan area layanan
- Analitik geografis

### Analitik Data
- Analisis performa regional
- Studi demografis
- Riset pasar

## Langkah Selanjutnya

Siap untuk memulai? Lihat panduan [Memulai](/id/guide/getting-started) untuk menginstal dan mengkonfigurasi Laravel Nusa dalam aplikasi Anda.
