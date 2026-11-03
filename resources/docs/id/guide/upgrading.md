# Pembaruan Versi

Panduan ini membahas peningkatan Laravel Nusa antar versi. Untuk daftar lengkap perubahan
setiap versi, lihat [changelog](https://github.com/creasico/laravel-nusa/blob/main/CHANGELOG.md).

## Meningkatkan dari 0.1 ke 0.2

`0.2.0` adalah rilis pertama yang *breaking*. Rilis ini menambahkan komponen UI
Blade/Alpine, menerbitkan database dan aset statis pada setiap rilis, serta menaikkan
persyaratan platform.

### Persyaratan

0.2 mendukung PHP 8.3+ dan Laravel 12 atau 13:

| Persyaratan | 0.1 | 0.2 |
|---|---|---|
| PHP | `^8.2` | `^8.3` |
| Laravel | `^9.0 \| ^10.0 \| ^11.0 \| ^12.0 \| ^13.0` | `^12.0 \| ^13.0` |

Tingkatkan PHP dan Laravel terlebih dahulu, lalu pasang lini baru. Batasan `^0.1` tidak
akan memasang 0.2 dengan sendirinya:

```bash
composer require creasi/laravel-nusa:^0.2
```

Jika aplikasi Anda harus tetap memakai PHP 8.2 atau Laravel 9–11, tetap gunakan lini 0.1:

```bash
composer require creasi/laravel-nusa:^0.1
```

### Validasi ukuran halaman

`per_page` adalah parameter yang didokumentasikan. Di 0.1 hanya `per-page` yang dibaca.
Parameter itu tetap berfungsi sebagai alias dan tidak ada rencana untuk menghapusnya.
Keduanya divalidasi sebagai bilangan bulat antara 1 dan 200:

| Permintaan | 0.1 | 0.2 |
|---|---|---|
| `?per-page=50` | berfungsi | berfungsi (alias) |
| `?per_page=50` | diabaikan, ukuran halaman default | berfungsi |
| `?per-page=0` | ukuran halaman default | `422` |
| `?per-page=-1` | mengembalikan semua baris | `422` |
| `?per-page=1.5` | diam-diam menjadi `1` | `422` |
| `?per-page=201` | diterima | `422` |

Ganti `0` dengan ukuran halaman yang eksplisit, atau paginasi/*chunk* kuerinya. Jika klien
tidak dapat menangani `422`, batasi nilainya sebelum dikirim.

### Komponen UI

Baru di 0.2: `<x-nusa::address-form>`, `<x-nusa::select>`, dan `<x-nusa::postal>`. Komponen
ini memerlukan JavaScript yang dipublikasikan sekali:

```bash
php artisan vendor:publish --tag=nusa-assets
```

Lalu muat sebelum Alpine di setiap layout:

```blade
@nusaAssets

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
```

Tidak ada *fallback* inline: aset yang belum dipublikasikan membuat komponen tampil sebagai
*select* biasa yang tidak pernah bertingkat. Lihat
[Instalasi](/id/guide/installation#komponen-ui-opsional) untuk catatan CSP dan URL aset.

Kustomisasi opsional:

- `--tag=nusa-views` mempublikasikan *view* komponen untuk diedit.
- `--tag=creasi-lang` mempublikasikan `lang/vendor/creasico/{en,id}/nusa.php` untuk label dan pesan.

Tidak ada perubahan lain saat runtime. Model, rute, dan tabel alamat yang ada tetap berfungsi.
