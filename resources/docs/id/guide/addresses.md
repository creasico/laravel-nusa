# Manajemen Alamat

**Revolusikan penanganan alamat Anda** dengan sistem manajemen alamat cerdas Laravel Nusa. Dari alur *checkout e-commerce* hingga manajemen lokasi perusahaan, solusi kami mengubah persyaratan alamat Indonesia yang kompleks menjadi pengalaman pengguna yang mulus.

## Mengapa Manajemen Alamat Laravel Nusa?

### 🎯 **Manfaat Kritis Bisnis**

**Formulir Alamat yang Disederhanakan**: Input yang efisien dengan *dropdown* bertingkat
**Akurasi Pengiriman yang Ditingkatkan**: Validasi alamat terhadap data resmi
**Struktur Data yang Konsisten**: Menangani hierarki administratif Indonesia dengan benar
**Pengalaman Pengguna yang Lebih Baik**: Proses pemilihan alamat yang intuitif

### 🚀 **Fitur Siap Perusahaan**

- **Dukungan Multi-Alamat** - Pelanggan dapat mengelola beberapa alamat pengiriman
- **Validasi Alamat** - Verifikasi *real-time* terhadap data resmi
- ***Smart Auto-Complete*** - *Dropdown* bertingkat dengan saran cerdas
- **Integrasi Fleksibel** - Bekerja dengan model pengguna yang sudah ada

## Solusi Bisnis Nyata

### 🛒 **Aplikasi E-Commerce**

**Tantangan**: Formulir alamat yang kompleks dapat membingungkan pelanggan selama *checkout*, terutama dengan struktur administratif multi-tingkat Indonesia.

**Solusi**: Sederhanakan proses *checkout* dengan manajemen alamat cerdas:

```php
// Manajemen alamat cerdas untuk pelanggan
class Customer extends Model
{
    use WithAddresses;

    public function getDefaultShippingAddress()
    {
        return $this->addresses()
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first();
    }

    public function calculateShippingCost($productWeight)
    {
        $address = $this->getDefaultShippingAddress();
        $zone = $address->getShippingZone();

        return $zone->calculateCost($productWeight);
    }
}
```

**Manfaat**:
- Mengurangi kompleksitas formulir dan kebingungan pengguna
- Meningkatkan kualitas data alamat dan keberhasilan pengiriman
- Pemformatan alamat yang konsisten di seluruh aplikasi
- Pengalaman pelanggan yang lebih baik selama *checkout*

### 🏢 **Manajemen Bisnis Multi-Lokasi**

**Tantangan**: Organisasi dengan banyak lokasi perlu mengelola alamat secara konsisten di berbagai kantor, gudang, dan pusat layanan.

**Solusi**: Manajemen alamat terpusat untuk struktur organisasi yang kompleks:

```php
// Manajemen bisnis multi-lokasi
class Company extends Model
{
    use WithAddresses;

    public function getLocationsByType($type)
    {
        return $this->addresses()
            ->where('type', $type)
            ->with(['village.regency.province'])
            ->get();
    }

    public function getRegionalCoverage()
    {
        return $this->addresses()
            ->with('province')
            ->get()
            ->groupBy('province.name')
            ->map(function ($addresses, $province) {
                return [
                    'province' => $province,
                    'locations' => $addresses->count(),
                    'types' => $addresses->pluck('type')->unique()
                ];
            });
    }
}
```

**Manfaat**:
- Manajemen terpusat dari semua lokasi bisnis
- Analisis dan pelaporan cakupan regional
- Data alamat yang konsisten di seluruh organisasi
- Kepatuhan dan pelaporan administratif yang disederhanakan

## Pengaturan Cepat (2 Menit)

### 1. Instal Tabel Alamat

```bash
php artisan vendor:publish --tag=creasi-migrations
php artisan migrate
```

### 2. Tambahkan ke Model Anda

```php
use Creasi\Nusa\Models\Concerns\WithAddress;

class User extends Model
{
    use WithAddress; // Dukungan alamat tunggal
}
```

### 3. Mulai Menggunakan

```php
$user->address()->create([
    'address_line' => 'Jl. Sudirman No. 123',
    'village_code' => '33.74.01.1001',
    'postal_code' => '50132'
]);
```

## Fitur Bisnis Lanjutan

### 🎯 **Validasi Alamat Cerdas**

Pastikan akurasi pengiriman 100% dengan validasi cerdas:

```php
// Validasi alamat otomatis
class AddressValidator
{
    public function validateAddress(array $addressData)
    {
        $village = Village::find($addressData['village_code']);

        if (!$village) {
            throw new InvalidAddressException('Kode desa tidak valid');
        }

        // Koreksi otomatis kode induk
        $addressData['district_code'] = $village->district_code;
        $addressData['regency_code'] = $village->regency_code;
        $addressData['province_code'] = $village->province_code;

        // Validasi kode pos
        if (!$addressData['postal_code']) {
            $addressData['postal_code'] = $village->postal_code;
        }

        return $addressData;
    }
}
```

**Manfaat**:
- Peningkatan kualitas dan konsistensi data alamat
- Pengisian otomatis kode pos saat tidak ada
- Pemformatan alamat standar
- Mengurangi kesalahan entri data

### 📍 **Manajemen Multi-Alamat**

Sempurna untuk pelanggan dengan banyak lokasi pengiriman:

```php
// Pelanggan dengan banyak alamat
class Customer extends Model
{
    use WithAddresses;

    public function addShippingAddress(array $addressData)
    {
        return $this->addresses()->create(array_merge($addressData, [
            'type' => 'shipping'
        ]));
    }

    public function setDefaultShippingAddress($addressId)
    {
        // Hapus default dari semua alamat pengiriman
        $this->addresses()
            ->where('type', 'shipping')
            ->update(['is_default' => false]);

        // Atur default baru
        return $this->addresses()
            ->where('id', $addressId)
            ->update(['is_default' => true]);
    }

    public function getShippingOptions()
    {
        return $this->addresses()
            ->where('type', 'shipping')
            ->with(['village.regency.province'])
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'label' => $address->name . ' - ' . $address->village->name,
                    'full_address' => $address->full_address,
                    'shipping_cost' => $address->calculateShippingCost(),
                    'is_default' => $address->is_default
                ];
            });
    }
}
```

**Manfaat**:
- Pengalaman pelanggan yang ditingkatkan dengan alamat yang disimpan
- Proses *checkout* yang disederhanakan untuk pelanggan yang kembali
- Manajemen alamat yang fleksibel untuk berbagai kasus penggunaan

## Kasus Penggunaan Umum

### 📊 **Aplikasi Umum**

**Platform E-Commerce**
- Alur *checkout* yang disederhanakan dengan *dropdown* alamat bertingkat
- Peningkatan akurasi pengiriman melalui validasi alamat
- Pengalaman pelanggan yang lebih baik dengan alamat pengiriman yang disimpan
- Pemformatan alamat yang konsisten di seluruh platform

**Bisnis Multi-Lokasi**
- Manajemen terpusat lokasi kantor dan gudang
- Data alamat standar di seluruh unit bisnis yang berbeda
- Kemampuan analisis dan pelaporan regional
- Kepatuhan yang disederhanakan dengan persyaratan administratif

**Aplikasi Berbasis Layanan**
- Manajemen alamat pelanggan untuk pengiriman layanan
- Manajemen wilayah dan area cakupan
- Optimasi layanan berbasis lokasi
- Analisis dan pelaporan geografis

## Pola Implementasi

### 🎯 **Pola Alamat Tunggal**
Sempurna untuk toko, kantor, atau profil pengguna sederhana:

```php
class Store extends Model
{
    use WithAddress;
    // Satu lokasi per toko
}
```

### 🏢 **Pola Multi-Alamat**
Ideal untuk pelanggan, perusahaan, atau penyedia layanan:

```php
class Customer extends Model
{
    use WithAddresses;
    // Beberapa alamat pengiriman/penagihan
}
```

### 🚀 **Pola Perusahaan**
Untuk persyaratan bisnis yang kompleks:

```php
class Enterprise extends Model
{
    use WithAddresses, WithCoordinate;
    // Beberapa lokasi + koordinat GPS
    // Sempurna untuk logistik dan analitik
}
```

## Memulai

Sistem manajemen alamat Laravel Nusa menyediakan fondasi yang kuat untuk menangani alamat Indonesia di aplikasi Anda.

### **Langkah Selanjutnya**:

1. **[Panduan Instalasi](/id/guide/installation)** - Siapkan Laravel Nusa di proyek Anda
2. **[Opsi Kustomisasi](/id/guide/customization)** - Pelajari tentang *trait* dan fitur yang tersedia
3. **[Contoh Formulir Alamat](/id/examples/address-forms)** - Lihat contoh implementasi praktis
4. **[Referensi API](/id/api/models/address)** - Jelajahi dokumentasi teknis terperinci

### **Fitur Utama**:

- **[Validasi Alamat](/id/api/concerns/with-address)** - Memastikan konsistensi dan akurasi data
- **[Dukungan Multi-Alamat](/id/api/concerns/with-addresses)** - Menangani persyaratan alamat yang kompleks
- **[Fitur Geografis](/id/api/concerns/with-coordinate)** - Menambahkan fungsionalitas berbasis lokasi

---

*Sederhanakan manajemen alamat Indonesia di aplikasi Laravel Anda.*