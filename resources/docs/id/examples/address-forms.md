# Form Alamat

Panduan ini menunjukkan cara membangun form alamat lengkap dengan dropdown bertingkat menggunakan Laravel Nusa. Kami akan membahas implementasi backend dan frontend dengan contoh praktis.

## Contoh Form Alamat Lengkap

### Implementasi Backend

#### Controller

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
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'province_code' => 'required|string|exists:nusa.provinces,code',
            'regency_code' => 'required|string|exists:nusa.regencies,code',
            'district_code' => 'required|string|exists:nusa.districts,code',
            'village_code' => 'required|string|exists:nusa.villages,code',
            'postal_code' => 'required|string|size:5',
        ]);
        
        // Simpan alamat
        $address = auth()->user()->addresses()->create($request->all());
        
        return redirect()->back()->with('success', 'Alamat berhasil disimpan!');
    }
}
```

#### Routes

```php
// routes/web.php
Route::get('/address/form', [AddressController::class, 'index'])->name('address.form');
Route::post('/address', [AddressController::class, 'store'])->name('address.store');

// API routes untuk dropdown
Route::prefix('api/address')->group(function () {
    Route::get('/regencies', [AddressController::class, 'getRegencies']);
    Route::get('/districts', [AddressController::class, 'getDistricts']);
    Route::get('/villages', [AddressController::class, 'getVillages']);
});
```

### Implementasi Frontend

#### Blade Template

```blade
{{-- resources/views/address/form.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Tambah Alamat Baru') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('address.store') }}" id="addressForm">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Nama Lengkap') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="phone" class="col-md-4 col-form-label text-md-end">{{ __('Nomor Telepon') }}</label>

                            <div class="col-md-6">
                                <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       name="phone" value="{{ old('phone') }}" required>

                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="address_line" class="col-md-4 col-form-label text-md-end">{{ __('Alamat Lengkap') }}</label>

                            <div class="col-md-6">
                                <textarea id="address_line" class="form-control @error('address_line') is-invalid @enderror" 
                                          name="address_line" rows="3" required>{{ old('address_line') }}</textarea>

                                @error('address_line')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="province_code" class="col-md-4 col-form-label text-md-end">{{ __('Provinsi') }}</label>

                            <div class="col-md-6">
                                <select id="province_code" class="form-select @error('province_code') is-invalid @enderror" 
                                        name="province_code" required>
                                    <option value="">Pilih Provinsi</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->code }}" 
                                                {{ old('province_code') == $province->code ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('province_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="regency_code" class="col-md-4 col-form-label text-md-end">{{ __('Kabupaten/Kota') }}</label>

                            <div class="col-md-6">
                                <select id="regency_code" class="form-select @error('regency_code') is-invalid @enderror" 
                                        name="regency_code" required disabled>
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>

                                @error('regency_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="district_code" class="col-md-4 col-form-label text-md-end">{{ __('Kecamatan') }}</label>

                            <div class="col-md-6">
                                <select id="district_code" class="form-select @error('district_code') is-invalid @enderror" 
                                        name="district_code" required disabled>
                                    <option value="">Pilih Kecamatan</option>
                                </select>

                                @error('district_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="village_code" class="col-md-4 col-form-label text-md-end">{{ __('Kelurahan/Desa') }}</label>

                            <div class="col-md-6">
                                <select id="village_code" class="form-select @error('village_code') is-invalid @enderror" 
                                        name="village_code" required disabled>
                                    <option value="">Pilih Kelurahan/Desa</option>
                                </select>

                                @error('village_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="postal_code" class="col-md-4 col-form-label text-md-end">{{ __('Kode Pos') }}</label>

                            <div class="col-md-6">
                                <input id="postal_code" type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       name="postal_code" value="{{ old('postal_code') }}" required readonly>

                                @error('postal_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Simpan Alamat') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('province_code');
    const regencySelect = document.getElementById('regency_code');
    const districtSelect = document.getElementById('district_code');
    const villageSelect = document.getElementById('village_code');
    const postalCodeInput = document.getElementById('postal_code');

    // Reset dan disable dropdown
    function resetDropdown(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
        select.value = '';
    }

    // Load regencies berdasarkan province
    provinceSelect.addEventListener('change', function() {
        const provinceCode = this.value;
        
        // Reset dropdown berikutnya
        resetDropdown(regencySelect, 'Pilih Kabupaten/Kota');
        resetDropdown(districtSelect, 'Pilih Kecamatan');
        resetDropdown(villageSelect, 'Pilih Kelurahan/Desa');
        postalCodeInput.value = '';
        
        if (provinceCode) {
            fetch(`/api/address/regencies?province_code=${provinceCode}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(regency => {
                        const option = document.createElement('option');
                        option.value = regency.code;
                        option.textContent = regency.name;
                        regencySelect.appendChild(option);
                    });
                    regencySelect.disabled = false;
                })
                .catch(error => console.error('Error:', error));
        }
    });

    // Load districts berdasarkan regency
    regencySelect.addEventListener('change', function() {
        const regencyCode = this.value;
        
        // Reset dropdown berikutnya
        resetDropdown(districtSelect, 'Pilih Kecamatan');
        resetDropdown(villageSelect, 'Pilih Kelurahan/Desa');
        postalCodeInput.value = '';
        
        if (regencyCode) {
            fetch(`/api/address/districts?regency_code=${regencyCode}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.code;
                        option.textContent = district.name;
                        districtSelect.appendChild(option);
                    });
                    districtSelect.disabled = false;
                })
                .catch(error => console.error('Error:', error));
        }
    });

    // Load villages berdasarkan district
    districtSelect.addEventListener('change', function() {
        const districtCode = this.value;
        
        // Reset dropdown berikutnya
        resetDropdown(villageSelect, 'Pilih Kelurahan/Desa');
        postalCodeInput.value = '';
        
        if (districtCode) {
            fetch(`/api/address/villages?district_code=${districtCode}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(village => {
                        const option = document.createElement('option');
                        option.value = village.code;
                        option.textContent = village.name;
                        option.dataset.postalCode = village.postal_code;
                        villageSelect.appendChild(option);
                    });
                    villageSelect.disabled = false;
                })
                .catch(error => console.error('Error:', error));
        }
    });

    // Auto-fill postal code berdasarkan village
    villageSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.postalCode) {
            postalCodeInput.value = selectedOption.dataset.postalCode;
        } else {
            postalCodeInput.value = '';
        }
    });
});
</script>
@endpush
```

#### JavaScript Advanced Implementation

Untuk implementasi yang lebih robust, berikut adalah class JavaScript yang dapat digunakan kembali:

```js
class AddressForm {
    constructor(options = {}) {
        this.baseUrl = options.baseUrl || '/api/address';
        this.loadingClass = options.loadingClass || 'loading';

        // Element references
        this.provinceSelect = document.getElementById('province_code');
        this.regencySelect = document.getElementById('regency_code');
        this.districtSelect = document.getElementById('district_code');
        this.villageSelect = document.getElementById('village_code');
        this.postalCodeInput = document.getElementById('postal_code');

        this.init();
    }

    init() {
        this.attachEventListeners();
        this.loadProvinces();
    }

    attachEventListeners() {
        this.provinceSelect.addEventListener('change', (e) => this.onProvinceChange(e));
        this.regencySelect.addEventListener('change', (e) => this.onRegencyChange(e));
        this.districtSelect.addEventListener('change', (e) => this.onDistrictChange(e));
        this.villageSelect.addEventListener('change', (e) => this.onVillageChange(e));
    }

    async loadProvinces() {
        try {
            this.setLoading(this.provinceSelect, true);
            const response = await fetch(`${this.baseUrl}/provinces`);
            const provinces = await response.json();

            this.populateSelect(this.provinceSelect, provinces, 'Pilih Provinsi');
            this.setLoading(this.provinceSelect, false);
        } catch (error) {
            this.showError('Gagal memuat data provinsi');
            this.setLoading(this.provinceSelect, false);
        }
    }

    async onProvinceChange(event) {
        const provinceCode = event.target.value;

        // Reset dependent selects
        this.resetDependentSelects(['regency_code', 'district_code', 'village_code']);
        this.postalCodeInput.value = '';

        if (!provinceCode) return;

        try {
            this.setLoading(this.regencySelect, true);
            const response = await fetch(`${this.baseUrl}/regencies?province_code=${provinceCode}`);
            const regencies = await response.json();

            this.populateSelect(this.regencySelect, regencies, 'Pilih Kabupaten/Kota');
            this.regencySelect.disabled = false;
            this.setLoading(this.regencySelect, false);
        } catch (error) {
            this.showError('Gagal memuat data kabupaten/kota');
            this.setLoading(this.regencySelect, false);
        }
    }

    async onRegencyChange(event) {
        const regencyCode = event.target.value;

        // Reset dependent selects
        this.resetDependentSelects(['district_code', 'village_code']);
        this.postalCodeInput.value = '';

        if (!regencyCode) return;

        try {
            this.setLoading(this.districtSelect, true);
            const response = await fetch(`${this.baseUrl}/districts?regency_code=${regencyCode}`);
            const districts = await response.json();

            this.populateSelect(this.districtSelect, districts, 'Pilih Kecamatan');
            this.districtSelect.disabled = false;
            this.setLoading(this.districtSelect, false);
        } catch (error) {
            this.showError('Gagal memuat data kecamatan');
            this.setLoading(this.districtSelect, false);
        }
    }

    async onDistrictChange(event) {
        const districtCode = event.target.value;

        // Reset dependent selects
        this.resetDependentSelects(['village_code']);
        this.postalCodeInput.value = '';

        if (!districtCode) return;

        try {
            this.setLoading(this.villageSelect, true);
            const response = await fetch(`${this.baseUrl}/villages?district_code=${districtCode}`);
            const villages = await response.json();

            this.populateSelect(this.villageSelect, villages, 'Pilih Kelurahan/Desa', true);
            this.villageSelect.disabled = false;
            this.setLoading(this.villageSelect, false);
        } catch (error) {
            this.showError('Gagal memuat data kelurahan/desa');
            this.setLoading(this.villageSelect, false);
        }
    }

    onVillageChange(event) {
        const selectedOption = event.target.options[event.target.selectedIndex];

        if (selectedOption && selectedOption.dataset.postalCode) {
            this.postalCodeInput.value = selectedOption.dataset.postalCode;
        } else {
            this.postalCodeInput.value = '';
        }
    }

    populateSelect(select, options, placeholder, includePostalCode = false) {
        // Clear existing options
        select.innerHTML = `<option value="">${placeholder}</option>`;

        // Add new options
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option.code;
            optionElement.textContent = option.name;

            if (includePostalCode && option.postal_code) {
                optionElement.textContent += ` (${option.postal_code})`;
                optionElement.dataset.postalCode = option.postal_code;
            }

            // Restore selected value if it exists
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

        // Clear postal code if village is reset
        if (selectNames.includes('village_code')) {
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
        // Anda dapat mengimplementasikan toast notification atau alert di sini
        alert(message);
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

// Initialize ketika DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new AddressForm();
});
```

## Implementasi Vue.js

Untuk framework frontend modern, berikut adalah komponen Vue.js:

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
      <label for="village">Kelurahan/Desa</label>
      <select
        id="village"
        v-model="form.village_code"
        @change="onVillageChange"
        :disabled="!form.district_code || loading.villages"
        required
      >
        <option value="">-- Pilih Kelurahan/Desa --</option>
        <option
          v-for="village in villages"
          :key="village.code"
          :value="village.code"
        >
          {{ village.name }} ({{ village.postal_code }})
        </option>
      </select>
    </div>

    <div class="form-group">
      <label for="postal_code">Kode Pos</label>
      <input
        id="postal_code"
        v-model="form.postal_code"
        type="text"
        readonly
        required
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

  mounted() {
    this.loadProvinces();
  },

  methods: {
    async loadProvinces() {
      this.loading.provinces = true;

      try {
        const response = await fetch('/api/address/provinces');
        this.provinces = await response.json();
      } catch (error) {
        this.$emit('error', 'Gagal memuat data provinsi');
      } finally {
        this.loading.provinces = false;
      }
    },

    async onProvinceChange() {
      // Reset dependent data
      this.regencies = [];
      this.districts = [];
      this.villages = [];
      this.form.regency_code = '';
      this.form.district_code = '';
      this.form.village_code = '';
      this.form.postal_code = '';

      if (!this.form.province_code) return;

      this.loading.regencies = true;

      try {
        const response = await fetch(`/api/address/regencies?province_code=${this.form.province_code}`);
        this.regencies = await response.json();
      } catch (error) {
        this.$emit('error', 'Gagal memuat data kabupaten/kota');
      } finally {
        this.loading.regencies = false;
      }
    },

    async onRegencyChange() {
      // Reset dependent data
      this.districts = [];
      this.villages = [];
      this.form.district_code = '';
      this.form.village_code = '';
      this.form.postal_code = '';

      if (!this.form.regency_code) return;

      this.loading.districts = true;

      try {
        const response = await fetch(`/api/address/districts?regency_code=${this.form.regency_code}`);
        this.districts = await response.json();
      } catch (error) {
        this.$emit('error', 'Gagal memuat data kecamatan');
      } finally {
        this.loading.districts = false;
      }
    },

    async onDistrictChange() {
      // Reset dependent data
      this.villages = [];
      this.form.village_code = '';
      this.form.postal_code = '';

      if (!this.form.district_code) return;

      this.loading.villages = true;

      try {
        const response = await fetch(`/api/address/villages?district_code=${this.form.district_code}`);
        this.villages = await response.json();
      } catch (error) {
        this.$emit('error', 'Gagal memuat data kelurahan/desa');
      } finally {
        this.loading.villages = false;
      }
    },

    onVillageChange() {
      const selectedVillage = this.villages.find(v => v.code === this.form.village_code);

      if (selectedVillage) {
        this.form.postal_code = selectedVillage.postal_code;
      } else {
        this.form.postal_code = '';
      }
    },

    async submitForm() {
      this.submitting = true;

      try {
        const response = await fetch('/api/address', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(this.form)
        });

        if (response.ok) {
          this.$emit('success', 'Alamat berhasil disimpan');
          this.resetForm();
        } else {
          throw new Error('Gagal menyimpan alamat');
        }
      } catch (error) {
        this.$emit('error', error.message);
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

      this.regencies = [];
      this.districts = [];
      this.villages = [];
    }
  }
}
</script>

<style scoped>
.address-form {
  max-width: 500px;
  margin: 0 auto;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

select, input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

select:disabled, input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

button {
  background-color: #007bff;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
}

button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

button:hover:not(:disabled) {
  background-color: #0056b3;
}
</style>
```

## React Implementation

Untuk aplikasi React, berikut adalah komponen yang dapat digunakan:

```jsx
import React, { useState, useEffect } from 'react';

const AddressForm = ({ onSubmit, onError }) => {
  const [form, setForm] = useState({
    province_code: '',
    regency_code: '',
    district_code: '',
    village_code: '',
    postal_code: ''
  });

  const [data, setData] = useState({
    provinces: [],
    regencies: [],
    districts: [],
    villages: []
  });

  const [loading, setLoading] = useState({
    provinces: false,
    regencies: false,
    districts: false,
    villages: false
  });

  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    loadProvinces();
  }, []);

  const loadProvinces = async () => {
    setLoading(prev => ({ ...prev, provinces: true }));

    try {
      const response = await fetch('/api/address/provinces');
      const provinces = await response.json();
      setData(prev => ({ ...prev, provinces }));
    } catch (error) {
      onError?.('Gagal memuat data provinsi');
    } finally {
      setLoading(prev => ({ ...prev, provinces: false }));
    }
  };

  const handleProvinceChange = async (provinceCode) => {
    setForm(prev => ({
      ...prev,
      province_code: provinceCode,
      regency_code: '',
      district_code: '',
      village_code: '',
      postal_code: ''
    }));

    setData(prev => ({
      ...prev,
      regencies: [],
      districts: [],
      villages: []
    }));

    if (!provinceCode) return;

    setLoading(prev => ({ ...prev, regencies: true }));

    try {
      const response = await fetch(`/api/address/regencies?province_code=${provinceCode}`);
      const regencies = await response.json();
      setData(prev => ({ ...prev, regencies }));
    } catch (error) {
      onError?.('Gagal memuat data kabupaten/kota');
    } finally {
      setLoading(prev => ({ ...prev, regencies: false }));
    }
  };

  const handleRegencyChange = async (regencyCode) => {
    setForm(prev => ({
      ...prev,
      regency_code: regencyCode,
      district_code: '',
      village_code: '',
      postal_code: ''
    }));

    setData(prev => ({
      ...prev,
      districts: [],
      villages: []
    }));

    if (!regencyCode) return;

    setLoading(prev => ({ ...prev, districts: true }));

    try {
      const response = await fetch(`/api/address/districts?regency_code=${regencyCode}`);
      const districts = await response.json();
      setData(prev => ({ ...prev, districts }));
    } catch (error) {
      onError?.('Gagal memuat data kecamatan');
    } finally {
      setLoading(prev => ({ ...prev, districts: false }));
    }
  };

  const handleDistrictChange = async (districtCode) => {
    setForm(prev => ({
      ...prev,
      district_code: districtCode,
      village_code: '',
      postal_code: ''
    }));

    setData(prev => ({ ...prev, villages: [] }));

    if (!districtCode) return;

    setLoading(prev => ({ ...prev, villages: true }));

    try {
      const response = await fetch(`/api/address/villages?district_code=${districtCode}`);
      const villages = await response.json();
      setData(prev => ({ ...prev, villages }));
    } catch (error) {
      onError?.('Gagal memuat data kelurahan/desa');
    } finally {
      setLoading(prev => ({ ...prev, villages: false }));
    }
  };

  const handleVillageChange = (villageCode) => {
    const selectedVillage = data.villages.find(v => v.code === villageCode);

    setForm(prev => ({
      ...prev,
      village_code: villageCode,
      postal_code: selectedVillage?.postal_code || ''
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);

    try {
      const response = await fetch('/api/address', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(form)
      });

      if (response.ok) {
        onSubmit?.(form);
        resetForm();
      } else {
        throw new Error('Gagal menyimpan alamat');
      }
    } catch (error) {
      onError?.(error.message);
    } finally {
      setSubmitting(false);
    }
  };

  const resetForm = () => {
    setForm({
      province_code: '',
      regency_code: '',
      district_code: '',
      village_code: '',
      postal_code: ''
    });

    setData(prev => ({
      ...prev,
      regencies: [],
      districts: [],
      villages: []
    }));
  };

  return (
    <form onSubmit={handleSubmit} className="address-form">
      <div className="form-group">
        <label htmlFor="province">Provinsi</label>
        <select
          id="province"
          value={form.province_code}
          onChange={(e) => handleProvinceChange(e.target.value)}
          disabled={loading.provinces}
          required
        >
          <option value="">-- Pilih Provinsi --</option>
          {data.provinces.map(province => (
            <option key={province.code} value={province.code}>
              {province.name}
            </option>
          ))}
        </select>
      </div>

      <div className="form-group">
        <label htmlFor="regency">Kabupaten/Kota</label>
        <select
          id="regency"
          value={form.regency_code}
          onChange={(e) => handleRegencyChange(e.target.value)}
          disabled={!form.province_code || loading.regencies}
          required
        >
          <option value="">-- Pilih Kabupaten/Kota --</option>
          {data.regencies.map(regency => (
            <option key={regency.code} value={regency.code}>
              {regency.name}
            </option>
          ))}
        </select>
      </div>

      <div className="form-group">
        <label htmlFor="district">Kecamatan</label>
        <select
          id="district"
          value={form.district_code}
          onChange={(e) => handleDistrictChange(e.target.value)}
          disabled={!form.regency_code || loading.districts}
          required
        >
          <option value="">-- Pilih Kecamatan --</option>
          {data.districts.map(district => (
            <option key={district.code} value={district.code}>
              {district.name}
            </option>
          ))}
        </select>
      </div>

      <div className="form-group">
        <label htmlFor="village">Kelurahan/Desa</label>
        <select
          id="village"
          value={form.village_code}
          onChange={(e) => handleVillageChange(e.target.value)}
          disabled={!form.district_code || loading.villages}
          required
        >
          <option value="">-- Pilih Kelurahan/Desa --</option>
          {data.villages.map(village => (
            <option key={village.code} value={village.code}>
              {village.name} ({village.postal_code})
            </option>
          ))}
        </select>
      </div>

      <div className="form-group">
        <label htmlFor="postal_code">Kode Pos</label>
        <input
          id="postal_code"
          type="text"
          value={form.postal_code}
          readOnly
          required
        />
      </div>

      <button type="submit" disabled={submitting}>
        {submitting ? 'Menyimpan...' : 'Simpan Alamat'}
      </button>
    </form>
  );
};

export default AddressForm;
```

## CSS Styling

Berikut adalah CSS untuk styling form alamat:

```css
.address-form {
  max-width: 500px;
  margin: 0 auto;
  padding: 2rem;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #333;
}

.form-group select,
.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e1e5e9;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.2s ease;
}

.form-group select:focus,
.form-group input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-group select:disabled,
.form-group input:disabled {
  background-color: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
}

.form-group select.loading {
  background-image: url('data:image/svg+xml;charset=utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="%23007bff" stroke-width="2" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="31.416"><animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416" repeatCount="indefinite"/><animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416" repeatCount="indefinite"/></circle></svg>');
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1rem;
}

.address-form button {
  width: 100%;
  padding: 0.75rem 1.5rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.address-form button:hover:not(:disabled) {
  background-color: #0056b3;
}

.address-form button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

/* Responsive design */
@media (max-width: 768px) {
  .address-form {
    margin: 1rem;
    padding: 1.5rem;
  }
}
```

Implementasi form alamat yang komprehensif ini menyediakan:

- **Dropdown bertingkat** yang memuat data secara dinamis
- **Validasi** di frontend dan backend
- **Error handling** dengan pesan yang user-friendly
- **Auto-completion** kode pos
- **Optimisasi performa** dengan loading states
- **Aksesibilitas** dengan label dan atribut ARIA yang tepat
- **Fleksibilitas framework** dengan contoh vanilla JS, Vue.js, dan React

Form ini memastikan konsistensi data dengan memvalidasi bahwa semua komponen alamat termasuk dalam hierarki yang benar dan memberikan pengalaman pengguna yang lancar dengan loading states dan error handling yang tepat.
