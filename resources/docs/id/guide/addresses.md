# Manajemen Alamat

Panduan lengkap untuk mengintegrasikan sistem manajemen alamat Laravel Nusa ke dalam aplikasi Anda, termasuk trait yang tersedia, validasi alamat, dan implementasi form alamat yang interaktif.

## Ikhtisar Manajemen Alamat

Laravel Nusa menyediakan sistem manajemen alamat lengkap yang memungkinkan model Anda memiliki multiple alamat dengan dukungan validasi dan relasi penuh. Sistem ini terintegrasi dengan mulus dengan hierarki administratif Indonesia.

### Fitur Utama

- **Multiple alamat per model** - User, bisnis, atau model apa pun dapat memiliki multiple alamat
- **Validasi otomatis** - Memastikan konsistensi hierarki alamat
- **Auto-fill kode pos** - Otomatis mengisi kode pos berdasarkan pemilihan desa
- **Akses hierarki lengkap** - Akses informasi provinsi, kabupaten/kota, kecamatan, dan desa
- **Implementasi fleksibel** - Gunakan trait atau relasi langsung

## Menggunakan Trait Alamat

### Trait WithAddresses

```php
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model
{
    use WithAddresses;
}

// Sekarang user dapat memiliki multiple alamat
$user = User::find(1);

// Buat alamat baru
$address = $user->addresses()->create([
    'name' => 'John Doe',
    'phone' => '081234567890',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123',
    'is_default' => true
]);

// Dapatkan semua alamat dengan data lokasi
$addresses = $user->addresses()
    ->with(['village.district.regency.province'])
    ->get();
```

### Trait WithAddress (Alamat Tunggal)

```php
use Creasi\Nusa\Models\Concerns\WithAddress;

class Store extends Model
{
    use WithAddress;
}

// Store memiliki satu alamat
$store = Store::find(1);
$store->address()->create([
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Sudirman No. 456'
]);
```

## Model Address

### Membuat Alamat

```php
use Creasi\Nusa\Models\Address;

// Pembuatan langsung
$address = Address::create([
    'addressable_type' => User::class,
    'addressable_id' => 1,
    'name' => 'Alamat Rumah',
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123',
    'postal_code' => '50132',
    'is_default' => true
]);

// Dengan kode pos otomatis
$address = Address::create([
    'addressable_type' => User::class,
    'addressable_id' => 1,
    'village_code' => '33.74.01.1001',
    'address_line' => 'Jl. Merdeka No. 123'
    // postal_code akan diisi otomatis dari village
]);
```

### Relasi Address

```php
$address = Address::with(['village.district.regency.province'])->first();

// Akses hierarki
echo $address->village->name;           // Nama desa
echo $address->village->district->name; // Nama kecamatan
echo $address->village->regency->name;  // Nama kabupaten/kota
echo $address->village->province->name; // Nama provinsi

// Alamat terformat
echo $address->full_address;
```

## Validasi Alamat

### Validasi Form Request

```php
// app/Http/Requests/StoreAddressRequest.php
class StoreAddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'village_code' => [
                'required',
                'exists:nusa.villages,code',
                new ValidAddressHierarchy($this->all())
            ],
            'address_line' => 'required|string|max:500',
            'is_default' => 'boolean'
        ];
    }
}
```

### Rule Validasi Kustom

```php
// app/Rules/ValidAddressHierarchy.php
class ValidAddressHierarchy implements Rule
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function passes($attribute, $value)
    {
        $village = Village::find($value);

        if (!$village) {
            return false;
        }

        // Validasi hierarki jika kode lain disediakan
        if (isset($this->data['district_code'])) {
            if ($village->district_code !== $this->data['district_code']) {
                return false;
            }
        }

        if (isset($this->data['regency_code'])) {
            if ($village->regency_code !== $this->data['regency_code']) {
                return false;
            }
        }

        if (isset($this->data['province_code'])) {
            if ($village->province_code !== $this->data['province_code']) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'Hierarki alamat tidak konsisten.';
    }
}
```

## Form Alamat

### Form Alamat Dasar

```html
<form id="address-form">
    <div class="form-group">
        <label for="name">Nama Penerima</label>
        <input type="text" id="name" name="name" required>
    </div>

    <div class="form-group">
        <label for="phone">Nomor Telepon</label>
        <input type="tel" id="phone" name="phone" required>
    </div>

    <div class="form-group">
        <label for="province">Provinsi</label>
        <select id="province" name="province_code" required>
            <option value="">Pilih Provinsi</option>
        </select>
    </div>

    <div class="form-group">
        <label for="regency">Kabupaten/Kota</label>
        <select id="regency" name="regency_code" required disabled>
            <option value="">Pilih Kabupaten/Kota</option>
        </select>
    </div>

    <div class="form-group">
        <label for="district">Kecamatan</label>
        <select id="district" name="district_code" required disabled>
            <option value="">Pilih Kecamatan</option>
        </select>
    </div>

    <div class="form-group">
        <label for="village">Kelurahan/Desa</label>
        <select id="village" name="village_code" required disabled>
            <option value="">Pilih Kelurahan/Desa</option>
        </select>
    </div>

    <div class="form-group">
        <label for="postal_code">Kode Pos</label>
        <input type="text" id="postal_code" name="postal_code" readonly>
    </div>

    <div class="form-group">
        <label for="address_line">Alamat Lengkap</label>
        <textarea id="address_line" name="address_line" required
                  placeholder="Nama jalan, nomor, RT/RW, nama gedung, dll."></textarea>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="is_default" value="1">
            Jadikan alamat default
        </label>
    </div>

    <button type="submit">Simpan Alamat</button>
</form>
```

### Implementasi JavaScript

```javascript
class AddressForm {
    constructor(formSelector) {
        this.form = document.querySelector(formSelector);
        this.api = new NusaAPI();
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadProvinces();
    }

    bindEvents() {
        this.form.querySelector('#province').addEventListener('change', (e) => {
            this.onProvinceChange(e.target.value);
        });

        this.form.querySelector('#regency').addEventListener('change', (e) => {
            this.onRegencyChange(e.target.value);
        });

        this.form.querySelector('#district').addEventListener('change', (e) => {
            this.onDistrictChange(e.target.value);
        });

        this.form.querySelector('#village').addEventListener('change', (e) => {
            this.onVillageChange(e.target.value);
        });

        this.form.addEventListener('submit', (e) => this.onSubmit(e));
    }

    async loadProvinces() {
        try {
            const response = await this.api.getProvinces({ sort: 'name' });
            this.populateSelect('#province', response.data);
        } catch (error) {
            this.showError('Gagal memuat provinsi');
        }
    }

    async onVillageChange(villageCode) {
        if (!villageCode) return;

        try {
            const response = await this.api.request(`/villages/${villageCode}`);
            const village = response.data;

            // Auto-fill kode pos
            if (village.postal_code) {
                this.form.querySelector('#postal_code').value = village.postal_code;
            }
        } catch (error) {
            this.showError('Gagal memuat data desa');
        }
    }

    async onSubmit(e) {
        e.preventDefault();

        const formData = new FormData(this.form);
        const addressData = Object.fromEntries(formData);

        try {
            const response = await fetch('/addresses', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(addressData)
            });

            if (response.ok) {
                this.showSuccess('Alamat berhasil disimpan');
                this.form.reset();
            } else {
                const error = await response.json();
                this.showError(error.message || 'Gagal menyimpan alamat');
            }
        } catch (error) {
            this.showError('Terjadi error jaringan');
        }
    }
}

// Inisialisasi form
const addressForm = new AddressForm('#address-form');
```

## Langkah Selanjutnya

- **[Kustomisasi](/id/guide/customization)** - Kustomisasi model dan trait alamat
- **[Form Alamat](/id/examples/address-forms)** - Implementasi form alamat lengkap
- **[Referensi API](/id/api/models/address)** - Dokumentasi model alamat
- **[Contoh](/id/examples/basic-usage)** - Contoh implementasi praktis
