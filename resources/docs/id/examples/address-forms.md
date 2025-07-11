# Formulir Alamat

Panduan ini menunjukkan cara membangun formulir alamat lengkap dengan *dropdown* bertingkat menggunakan Laravel Nusa. Kami akan membahas implementasi *backend* dan *frontend* dengan contoh-contoh praktis.

## Contoh Formulir Alamat Lengkap

### Implementasi *Backend*

#### *Controller*

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Creasi\Nusa\Models\{Province, Regency, District, Village};

class AddressController extends Controller
{
    public function index()
    {
        $provinces = Province::orderBy('name')->get(['code', 'name']);
        
        return view('address.form', compact('provinces'));
    }
    
    public function getRegencies(Request $request)
    {
        $request->validate([
            'province_code' => 'required|string|exists:nusa.provinces,code'
        ]);
        
        $regencies = Regency::where('province_code', $request->province_code)
            ->orderBy('name')
            ->get(['code', 'name']);
            
        return response()->json($regencies);
    }
    
    public function getDistricts(Request $request)
    {
        $request->validate([
            'regency_code' => 'required|string|exists:nusa.regencies,code'
        ]);
        
        $districts = District::where('regency_code', $request->regency_code)
            ->orderBy('name')
            ->get(['code', 'name']);
            
        return response()->json($districts);
    }
    
    public function getVillages(Request $request)
    {
        $request->validate([
            'district_code' => 'required|string|exists:nusa.districts,code'
        ]);
        
        $villages = Village::where('district_code', $request->district_code)
            ->orderBy('name')
            ->get(['code', 'name', 'postal_code']);
            
        return response()->json($villages);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_code' => 'required|string|exists:nusa.provinces,code',
            'regency_code' => 'required|string|exists:nusa.regencies,code',
            'district_code' => 'required|string|exists:nusa.districts,code',
            'village_code' => 'required|string|exists:nusa.villages,code',
            'address_line' => 'required|string|max:500',
            'postal_code' => 'nullable|string|size:5',
        ]);
        
        // Validasi konsistensi alamat
        $village = Village::find($validated['village_code']);
        if (!$village || 
            $village->district_code !== $validated['district_code'] ||
            $village->regency_code !== $validated['regency_code'] ||
            $village->province_code !== $validated['province_code']) {
            return back()->withErrors(['address' => 'Komponen alamat tidak konsisten.']);
        }
        
        // Isi otomatis kode pos jika tidak disediakan
        if (!$validated['postal_code']) {
            $validated['postal_code'] = $village->postal_code;
        }
        
        // Simpan alamat (contoh dengan relasi pengguna)
        auth()->user()->addresses()->create($validated);
        
        return redirect()->route('address.index')->with('success', 'Alamat berhasil disimpan!');
    }
}
```

#### Rute

```php
// routes/web.php
use App\Http\Controllers\AddressController;

Route::middleware('auth')->group(function () {
    Route::get('/address', [AddressController::class, 'index'])->name('address.form');
    Route::post('/address', [AddressController::class, 'store'])->name('address.store');
    
    // Endpoint AJAX untuk dropdown bertingkat
    Route::get('/api/regencies', [AddressController::class, 'getRegencies']);
    Route::get('/api/districts', [AddressController::class, 'getDistricts']);
    Route::get('/api/villages', [AddressController::class, 'getVillages']);
});
```

### Implementasi *Frontend*

#### Template Blade

```blade
{{-- resources/views/address/form.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Tambah Alamat Baru</div>
                
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('address.store') }}" id="addressForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="province" class="form-label">Provinsi</label>
                                    <select class="form-select @error('province_code') is-invalid @enderror" 
                                            id="province" name="province_code" required>
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->code }}" 
                                                    {{ old('province_code') == $province->code ? 'selected' : '' }}>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('province_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="regency" class="form-label">Kabupaten/Kota</label>
                                    <select class="form-select @error('regency_code') is-invalid @enderror" 
                                            id="regency" name="regency_code" required disabled>
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                    </select>
                                    @error('regency_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="district" class="form-label">Kecamatan</label>
                                    <select class="form-select @error('district_code') is-invalid @enderror" 
                                            id="district" name="district_code" required disabled>
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                    @error('district_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="village" class="form-label">Desa/Kelurahan</label>
                                    <select class="form-select @error('village_code') is-invalid @enderror" 
                                            id="village" name="village_code" required disabled>
                                        <option value="">-- Pilih Desa/Kelurahan --</option>
                                    </select>
                                    @error('village_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address_line" class="form-label">Baris Alamat</label>
                            <textarea class="form-control @error('address_line') is-invalid @enderror" 
                                      id="address_line" name="address_line" rows="3" required 
                                      placeholder="Alamat jalan, nomor bangunan, dll.">{{ old('address_line') }}</textarea>
                            @error('address_line')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Kode Pos</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}" 
                                           maxlength="5" placeholder="Otomatis terisi dari desa/kelurahan">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Akan terisi otomatis saat Anda memilih desa/kelurahan</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Simpan Alamat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/address-form.js') }}"></script>
@endpush
```

#### Implementasi JavaScript

```js
// public/js/address-form.js
class AddressForm {
    constructor() {
        this.provinceSelect = document.getElementById('province');
        this.regencySelect = document.getElementById('regency');
        this.districtSelect = document.getElementById('district');
        this.villageSelect = document.getElementById('village');
        this.postalCodeInput = document.getElementById('postal_code');
        
        this.loadingClass = 'loading';
        this.init();
    }
    
    init() {
        this.bindEvents();
        
        // Jika ada nilai lama (error validasi), pulihkan status formulir
        if (this.provinceSelect.value) {
            this.loadRegencies(this.provinceSelect.value, () => {
                if (this.regencySelect.value) {
                    this.loadDistricts(this.regencySelect.value, () => {
                        if (this.districtSelect.value) {
                            this.loadVillages(this.districtSelect.value);
                        }
                    });
                }
            });
        }
    }
    
    bindEvents() {
        this.provinceSelect.addEventListener('change', (e) => {
            const provinceCode = e.target.value;
            this.resetDependentSelects(['regency', 'district', 'village']);
            
            if (provinceCode) {
                this.loadRegencies(provinceCode);
            }
        });
        
        this.regencySelect.addEventListener('change', (e) => {
            const regencyCode = e.target.value;
            this.resetDependentSelects(['district', 'village']);
            
            if (regencyCode) {
                this.loadDistricts(regencyCode);
            }
        });
        
        this.districtSelect.addEventListener('change', (e) => {
            const districtCode = e.target.value;
            this.resetDependentSelects(['village']);
            
            if (districtCode) {
                this.loadVillages(districtCode);
            }
        });
        
        this.villageSelect.addEventListener('change', (e) => {
            const selectedOption = e.target.selectedOptions[0];
            if (selectedOption && selectedOption.dataset.postalCode) {
                this.postalCodeInput.value = selectedOption.dataset.postalCode;
            }
        });
    }
    
    async loadRegencies(provinceCode, callback = null) {
        try {
            this.setLoading(this.regencySelect, true);
            
            const response = await fetch(`/api/regencies?province_code=${provinceCode}`);
            if (!response.ok) throw new Error('Gagal memuat kabupaten/kota');
            
            const regencies = await response.json();
            this.populateSelect(this.regencySelect, regencies, 'Pilih Kabupaten/Kota');
            this.regencySelect.disabled = false;
            
            if (callback) callback();
        } catch (error) {
            console.error('Error loading regencies:', error);
            this.showError('Gagal memuat kabupaten/kota. Silakan coba lagi.');
        } finally {
            this.setLoading(this.regencySelect, false);
        }
    }
    
    async loadDistricts(regencyCode, callback = null) {
        try {
            this.setLoading(this.districtSelect, true);
            
            const response = await fetch(`/api/districts?regency_code=${regencyCode}`);
            if (!response.ok) throw new Error('Gagal memuat kecamatan');
            
            const districts = await response.json();
            this.populateSelect(this.districtSelect, districts, 'Pilih Kecamatan');
            this.districtSelect.disabled = false;
            
            if (callback) callback();
        } catch (error) {
            console.error('Error loading districts:', error);
            this.showError('Gagal memuat kecamatan. Silakan coba lagi.');
        } finally {
            this.setLoading(this.districtSelect, false);
        }
    }
    
    async loadVillages(districtCode, callback = null) {
        try {
            this.setLoading(this.villageSelect, true);
            
            const response = await fetch(`/api/villages?district_code=${districtCode}`);
            if (!response.ok) throw new Error('Gagal memuat desa/kelurahan');
            
            const villages = await response.json();
            this.populateSelect(this.villageSelect, villages, 'Pilih Desa/Kelurahan', true);
            this.villageSelect.disabled = false;
            
            if (callback) callback();
        } catch (error) {
            console.error('Error loading villages:', error);
            this.showError('Gagal memuat desa/kelurahan. Silakan coba lagi.');
        } finally {
            this.setLoading(this.villageSelect, false);
        }
    }
    
    populateSelect(select, options, placeholder, includePostalCode = false) {
        // Hapus opsi yang ada
        select.innerHTML = `<option value="">${placeholder}</option>`;
        
        // Tambahkan opsi baru
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option.code;
            optionElement.textContent = option.name;
            
            if (includePostalCode && option.postal_code) {
                optionElement.textContent += ` (${option.postal_code})`;
                optionElement.dataset.postalCode = option.postal_code;
            }
            
            // Pulihkan nilai yang dipilih jika ada
            if (select.dataset.oldValue === option.code) {
                optionElement.selected = true;
            }
            
            select.appendChild(optionElement);
        });
    }
    
    resetDependentSelects(selectNames) {
        selectNames.forEach(name => {
            const select = document.getElementById(name);
            select.innerHTML = `<option value="">-- Pilih ${this.capitalize(name)} --</option>`;
            select.disabled = true;
        });
        
        // Hapus kode pos jika desa/kelurahan direset
        if (selectNames.includes('village')) {
            this.postalCodeInput.value = '';
        }
    }
    
    setLoading(select, isLoading) {
        if (isLoading) {
            select.classList.add(this.loadingClass);
            select.disabled = true;
        } else {
            select.classList.remove(this.loadingClass);
        }
    }
    
    showError(message) {
        // Anda dapat mengimplementasikan notifikasi toast atau alert di sini
        alert(message);
    }
    
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

// Inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    new AddressForm();
});
```

## Implementasi Vue.js

Untuk *framework frontend* modern, berikut adalah komponen Vue.js:

```vue
<template>
  <form @submit.prevent="submitForm" class="address-form">
    <div class="form-group">
      <label for="province">Provinsi</label>
      <select 
        id="province" 
        v-model="form.province_code" 
        @change="onProvinceChange"
        :disabled="loading.provinces"
        required
      >
        <option value="">-- Pilih Provinsi --</option>
        <option 
          v-for="province in provinces" 
          :key="province.code" 
          :value="province.code"
        >
          {{ province.name }}
        </option>
      </select>
    </div>
    
    <div class="form-group">
      <label for="regency">Kabupaten/Kota</label>
      <select 
        id="regency" 
        v-model="form.regency_code" 
        @change="onRegencyChange"
        :disabled="!form.province_code || loading.regencies"
        required
      >
        <option value="">-- Pilih Kabupaten/Kota --</option>
        <option 
          v-for="regency in regencies" 
          :key="regency.code" 
          :value="regency.code"
        >
          {{ regency.name }}
        </option>
      </select>
    </div>
    
    <div class="form-group">
      <label for="district">Kecamatan</label>
      <select 
        id="district" 
        v-model="form.district_code" 
        @change="onDistrictChange"
        :disabled="!form.regency_code || loading.districts"
        required
      >
        <option value="">-- Pilih Kecamatan --</option>
        <option 
          v-for="district in districts" 
          :key="district.code" 
          :value="district.code"
        >
          {{ district.name }}
        </option>
      </select>
    </div>
    
    <div class="form-group">
      <label for="village">Desa/Kelurahan</label>
      <select 
        id="village" 
        v-model="form.village_code" 
        @change="onVillageChange"
        :disabled="!form.district_code || loading.villages"
        required
      >
        <option value="">-- Pilih Desa/Kelurahan --</option>
        <option 
          v-for="village in villages" 
          :key="village.code" 
          :value="village.code"
          :data-postal-code="village.postal_code"
        >
          {{ village.name }} {{ village.postal_code ? `(${village.postal_code})` : '' }}
        </option>
      </select>
    </div>
    
    <div class="form-group">
      <label for="postal_code">Kode Pos</label>
      <input 
        id="postal_code" 
        v-model="form.postal_code" 
        type="text" 
        maxlength="5"
        placeholder="Otomatis terisi dari desa/kelurahan"
      >
    </div>
    
    <button type="submit" :disabled="submitting">
      {{ submitting ? 'Menyimpan...' : 'Simpan Alamat' }}
    </button>
  </form>
</template>

<script>
export default {
  name: 'AddressForm',
  data() {
    return {
      form: {
        province_code: '',
        regency_code: '',
        district_code: '',
        village_code: '',
        postal_code: ''
      },
      provinces: [],
      regencies: [],
      districts: [],
      villages: [],
      loading: {
        provinces: false,
        regencies: false,
        districts: false,
        villages: false
      },
      submitting: false
    }
  },
  async mounted() {
    await this.loadProvinces();
  },
  methods: {
    async loadProvinces() {
      this.loading.provinces = true;
      try {
        const response = await fetch('/nusa/provinces');
        const data = await response.json();
        this.provinces = data.data;
      } catch (error) {
        console.error('Error loading provinces:', error);
      } finally {
        this.loading.provinces = false;
      }
    },
    
    async onProvinceChange() {
      this.resetDependentData(['regencies', 'districts', 'villages']);
      this.form.regency_code = '';
      this.form.district_code = '';
      this.form.village_code = '';
      this.form.postal_code = '';
      
      if (this.form.province_code) {
        await this.loadRegencies();
      }
    },
    
    async loadRegencies() {
      this.loading.regencies = true;
      try {
        const response = await fetch(`/nusa/provinces/${this.form.province_code}/regencies`);
        const data = await response.json();
        this.regencies = data.data;
      } catch (error) {
        console.error('Error loading regencies:', error);
      } finally {
        this.loading.regencies = false;
      }
    },
    
    async onRegencyChange() {
      this.resetDependentData(['districts', 'villages']);
      this.form.district_code = '';
      this.form.village_code = '';
      this.form.postal_code = '';
      
      if (this.form.regency_code) {
        await this.loadDistricts();
      }
    },
    
    async loadDistricts() {
      this.loading.districts = true;
      try {
        const response = await fetch(`/nusa/regencies/${this.form.regency_code}/districts`);
        const data = await response.json();
        this.districts = data.data;
      } catch (error) {
        console.error('Error loading districts:', error);
      } finally {
        this.loading.districts = false;
      }
    },
    
    async onDistrictChange() {
      this.resetDependentData(['villages']);
      this.form.village_code = '';
      this.form.postal_code = '';
      
      if (this.form.district_code) {
        await this.loadVillages();
      }
    },
    
    async loadVillages() {
      this.loading.villages = true;
      try {
        const response = await fetch(`/nusa/districts/${this.form.district_code}/villages`);
        const data = await response.json();
        this.villages = data.data;
      } catch (error) {
        console.error('Error loading villages:', error);
      } finally {
        this.loading.villages = false;
      }
    },
    
    onVillageChange() {
      const selectedVillage = this.villages.find(v => v.code === this.form.village_code);
      if (selectedVillage && selectedVillage.postal_code) {
        this.form.postal_code = selectedVillage.postal_code;
      }
    },
    
    resetDependentData(arrays) {
      arrays.forEach(array => {
        this[array] = [];
      });
    },
    
    async submitForm() {
      this.submitting = true;
      try {
        const response = await fetch('/address', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(this.form)
        });
        
        if (response.ok) {
          this.$emit('address-saved');
          this.resetForm();
        } else {
          throw new Error('Gagal menyimpan alamat');
        }
      } catch (error) {
        console.error('Error saving address:', error);
        alert('Gagal menyimpan alamat. Silakan coba lagi.');
      } finally {
        this.submitting = false;
      }
    },
    
    resetForm() {
      this.form = {
        province_code: '',
        regency_code: '',
        district_code: '',
        village_code: '',
        postal_code: ''
      };
      this.resetDependentData(['regencies', 'districts', 'villages']);
    }
  }
}
</script>
```

Implementasi formulir alamat yang komprehensif ini menyediakan:

- **Dropdown bertingkat** yang memuat data secara dinamis
- **Validasi** di *frontend* dan *backend*
- **Penanganan error** dengan pesan yang mudah dipahami pengguna
- **Pengisian otomatis** kode pos
- **Optimasi kinerja** dengan status pemuatan
- **Aksesibilitas** dengan label dan atribut ARIA yang tepat
- **Fleksibilitas *framework*** dengan contoh JavaScript murni dan Vue.js

Formulir ini memastikan konsistensi data dengan memvalidasi bahwa semua komponen alamat termasuk dalam hierarki yang benar dan memberikan pengalaman pengguna yang lancar dengan status pemuatan dan penanganan error yang tepat.