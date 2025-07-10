# Form Alamat

Panduan lengkap untuk membangun form alamat yang interaktif dan user-friendly menggunakan Laravel Nusa, dengan contoh implementasi dropdown bertingkat, validasi real-time, dan auto-complete.

## Form Alamat Dasar

### Struktur HTML

```html
<form id="address-form" class="address-form">
    <div class="form-group">
        <label for="province">Provinsi *</label>
        <select id="province" name="province_code" required>
            <option value="">Pilih Provinsi</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="regency">Kabupaten/Kota *</label>
        <select id="regency" name="regency_code" required disabled>
            <option value="">Pilih Kabupaten/Kota</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="district">Kecamatan *</label>
        <select id="district" name="district_code" required disabled>
            <option value="">Pilih Kecamatan</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="village">Kelurahan/Desa *</label>
        <select id="village" name="village_code" required disabled>
            <option value="">Pilih Kelurahan/Desa</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="postal_code">Kode Pos</label>
        <input type="text" id="postal_code" name="postal_code" readonly>
    </div>
    
    <div class="form-group">
        <label for="address_line">Alamat Lengkap *</label>
        <textarea id="address_line" name="address_line" required 
                  placeholder="Jl. Nama Jalan No. XX, RT/RW, Nama Gedung, dll"></textarea>
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
        const province = this.form.querySelector('#province');
        const regency = this.form.querySelector('#regency');
        const district = this.form.querySelector('#district');
        const village = this.form.querySelector('#village');
        
        province.addEventListener('change', (e) => this.onProvinceChange(e.target.value));
        regency.addEventListener('change', (e) => this.onRegencyChange(e.target.value));
        district.addEventListener('change', (e) => this.onDistrictChange(e.target.value));
        village.addEventListener('change', (e) => this.onVillageChange(e.target.value));
        
        this.form.addEventListener('submit', (e) => this.onSubmit(e));
    }
    
    async loadProvinces() {
        try {
            const response = await this.api.getProvinces({ sort: 'name' });
            this.populateSelect('#province', response.data);
        } catch (error) {
            this.showError('Gagal memuat data provinsi');
        }
    }
    
    async onProvinceChange(provinceCode) {
        if (!provinceCode) {
            this.resetSelects(['#regency', '#district', '#village']);
            return;
        }
        
        try {
            this.showLoading('#regency');
            const response = await this.api.getRegenciesByProvince(provinceCode, { sort: 'name' });
            this.populateSelect('#regency', response.data);
            this.enableSelect('#regency');
            this.resetSelects(['#district', '#village']);
        } catch (error) {
            this.showError('Gagal memuat data kabupaten/kota');
        }
    }
    
    async onVillageChange(villageCode) {
        if (!villageCode) {
            this.clearPostalCode();
            return;
        }
        
        try {
            const response = await this.api.request(`/villages/${villageCode}`);
            const village = response.data;
            
            // Auto-fill postal code
            if (village.postal_code) {
                this.form.querySelector('#postal_code').value = village.postal_code;
            }
            
            // Validate address hierarchy
            this.validateAddressHierarchy(village);
        } catch (error) {
            this.showError('Gagal memuat data desa');
        }
    }
    
    validateAddressHierarchy(village) {
        const selectedDistrict = this.form.querySelector('#district').value;
        const selectedRegency = this.form.querySelector('#regency').value;
        const selectedProvince = this.form.querySelector('#province').value;
        
        if (village.district_code !== selectedDistrict ||
            village.regency_code !== selectedRegency ||
            village.province_code !== selectedProvince) {
            
            this.showWarning('Data alamat tidak konsisten. Silakan periksa kembali pilihan Anda.');
        }
    }
    
    populateSelect(selector, data) {
        const select = this.form.querySelector(selector);
        const defaultOption = select.querySelector('option[value=""]');
        
        select.innerHTML = '';
        select.appendChild(defaultOption);
        
        data.forEach(item => {
            const option = new Option(item.name, item.code);
            select.appendChild(option);
        });
    }
    
    resetSelects(selectors) {
        selectors.forEach(selector => {
            const select = this.form.querySelector(selector);
            const defaultOption = select.querySelector('option[value=""]');
            select.innerHTML = '';
            select.appendChild(defaultOption);
            select.disabled = true;
        });
        
        if (selectors.includes('#village')) {
            this.clearPostalCode();
        }
    }
    
    enableSelect(selector) {
        this.form.querySelector(selector).disabled = false;
    }
    
    clearPostalCode() {
        this.form.querySelector('#postal_code').value = '';
    }
    
    showLoading(selector) {
        const select = this.form.querySelector(selector);
        select.innerHTML = '<option value="">Memuat...</option>';
        select.disabled = true;
    }
    
    showError(message) {
        // Implement error display
        console.error(message);
    }
    
    showWarning(message) {
        // Implement warning display
        console.warn(message);
    }
    
    async onSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(this.form);
        const addressData = Object.fromEntries(formData);
        
        // Validate before submit
        if (await this.validateAddress(addressData)) {
            this.submitAddress(addressData);
        }
    }
    
    async validateAddress(addressData) {
        // Implement validation logic
        return true;
    }
    
    async submitAddress(addressData) {
        // Implement form submission
        console.log('Submitting address:', addressData);
    }
}

// Initialize form
const addressForm = new AddressForm('#address-form');
```

## Integrasi Backend Laravel

### Validasi Form Request

```php
// app/Http/Requests/StoreAddressRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class StoreAddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_code' => [
                'required',
                'string',
                'exists:nusa.provinces,code'
            ],
            'regency_code' => [
                'required',
                'string',
                'exists:nusa.regencies,code',
                function ($attribute, $value, $fail) {
                    $regency = Regency::find($value);
                    if (!$regency || $regency->province_code !== $this->province_code) {
                        $fail('Kabupaten/kota tidak sesuai dengan provinsi.');
                    }
                },
            ],
            'district_code' => [
                'required',
                'string',
                'exists:nusa.districts,code',
                function ($attribute, $value, $fail) {
                    $district = District::find($value);
                    if (!$district || $district->regency_code !== $this->regency_code) {
                        $fail('Kecamatan tidak sesuai dengan kabupaten/kota.');
                    }
                },
            ],
            'village_code' => [
                'required',
                'string',
                'exists:nusa.villages,code',
                function ($attribute, $value, $fail) {
                    $village = Village::find($value);
                    if (!$village || $village->district_code !== $this->district_code) {
                        $fail('Kelurahan/desa tidak sesuai dengan kecamatan.');
                    }
                },
            ],
            'address_line' => 'required|string|max:500',
            'postal_code' => 'nullable|string|size:5',
            'is_default' => 'boolean',
        ];
    }
    
    public function messages()
    {
        return [
            'province_code.required' => 'Silakan pilih provinsi.',
            'regency_code.required' => 'Silakan pilih kabupaten/kota.',
            'district_code.required' => 'Silakan pilih kecamatan.',
            'village_code.required' => 'Silakan pilih kelurahan/desa.',
            'address_line.required' => 'Silakan masukkan alamat lengkap.',
        ];
    }
    
    protected function prepareForValidation()
    {
        // Auto-fill postal code if not provided
        if (!$this->postal_code && $this->village_code) {
            $village = Village::find($this->village_code);
            if ($village && $village->postal_code) {
                $this->merge(['postal_code' => $village->postal_code]);
            }
        }
    }
}
```

### Controller

```php
// app/Http/Controllers/AddressController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(StoreAddressRequest $request)
    {
        $address = auth()->user()->addresses()->create($request->validated());
        
        return response()->json([
            'message' => 'Alamat berhasil disimpan',
            'address' => $address->load(['province', 'regency', 'district', 'village'])
        ]);
    }
    
    public function validateAddress(Request $request)
    {
        $request->validate([
            'village_code' => 'required|exists:nusa.villages,code',
            'district_code' => 'required',
            'regency_code' => 'required',
            'province_code' => 'required',
        ]);
        
        $village = Village::find($request->village_code);
        
        $isValid = $village &&
                   $village->district_code === $request->district_code &&
                   $village->regency_code === $request->regency_code &&
                   $village->province_code === $request->province_code;
        
        return response()->json([
            'valid' => $isValid,
            'village' => $village,
            'suggested_postal_code' => $village?->postal_code
        ]);
    }
}
```

## Langkah Selanjutnya

- **[Query Geografis](/id/examples/geographic-queries)** - Query berbasis lokasi dan pemetaan
- **[Model Kustom](/id/examples/custom-models)** - Memperluas model Laravel Nusa
- **[Integrasi API](/id/examples/api-integration)** - Pola penggunaan API lanjutan
