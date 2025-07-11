# Address Forms

This guide shows how to build complete address forms with cascading dropdowns using Laravel Nusa. We'll cover both backend and frontend implementation with practical examples.

## Complete Address Form Example

### Backend Implementation

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
        
        // Validate address consistency
        $village = Village::find($validated['village_code']);
        if (!$village || 
            $village->district_code !== $validated['district_code'] ||
            $village->regency_code !== $validated['regency_code'] ||
            $village->province_code !== $validated['province_code']) {
            return back()->withErrors(['address' => 'Address components are inconsistent.']);
        }
        
        // Auto-fill postal code if not provided
        if (!$validated['postal_code']) {
            $validated['postal_code'] = $village->postal_code;
        }
        
        // Store the address (example with user relationship)
        auth()->user()->addresses()->create($validated);
        
        return redirect()->route('address.index')->with('success', 'Address saved successfully!');
    }
}
```

#### Routes

```php
// routes/web.php
use App\Http\Controllers\AddressController;

Route::middleware('auth')->group(function () {
    Route::get('/address', [AddressController::class, 'index'])->name('address.form');
    Route::post('/address', [AddressController::class, 'store'])->name('address.store');
    
    // AJAX endpoints for cascading dropdowns
    Route::get('/api/regencies', [AddressController::class, 'getRegencies']);
    Route::get('/api/districts', [AddressController::class, 'getDistricts']);
    Route::get('/api/villages', [AddressController::class, 'getVillages']);
});
```

### Frontend Implementation

#### Blade Template

```blade
{{-- resources/views/address/form.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Add New Address</div>
                
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
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
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
                                    <label for="province" class="form-label">Province</label>
                                    <select class="form-select @error('province_code') is-invalid @enderror" 
                                            id="province" name="province_code" required>
                                        <option value="">-- Select Province --</option>
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
                                    <label for="regency" class="form-label">Regency/City</label>
                                    <select class="form-select @error('regency_code') is-invalid @enderror" 
                                            id="regency" name="regency_code" required disabled>
                                        <option value="">-- Select Regency --</option>
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
                                    <label for="district" class="form-label">District</label>
                                    <select class="form-select @error('district_code') is-invalid @enderror" 
                                            id="district" name="district_code" required disabled>
                                        <option value="">-- Select District --</option>
                                    </select>
                                    @error('district_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="village" class="form-label">Village</label>
                                    <select class="form-select @error('village_code') is-invalid @enderror" 
                                            id="village" name="village_code" required disabled>
                                        <option value="">-- Select Village --</option>
                                    </select>
                                    @error('village_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address_line" class="form-label">Address Line</label>
                            <textarea class="form-control @error('address_line') is-invalid @enderror" 
                                      id="address_line" name="address_line" rows="3" required 
                                      placeholder="Street address, building number, etc.">{{ old('address_line') }}</textarea>
                            @error('address_line')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}" 
                                           maxlength="5" placeholder="Auto-filled from village">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Will be auto-filled when you select a village</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Address</button>
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

#### JavaScript Implementation

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
        
        // If there are old values (validation errors), restore the form state
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
            if (!response.ok) throw new Error('Failed to load regencies');
            
            const regencies = await response.json();
            this.populateSelect(this.regencySelect, regencies, 'Select Regency');
            this.regencySelect.disabled = false;
            
            if (callback) callback();
        } catch (error) {
            console.error('Error loading regencies:', error);
            this.showError('Failed to load regencies. Please try again.');
        } finally {
            this.setLoading(this.regencySelect, false);
        }
    }
    
    async loadDistricts(regencyCode, callback = null) {
        try {
            this.setLoading(this.districtSelect, true);
            
            const response = await fetch(`/api/districts?regency_code=${regencyCode}`);
            if (!response.ok) throw new Error('Failed to load districts');
            
            const districts = await response.json();
            this.populateSelect(this.districtSelect, districts, 'Select District');
            this.districtSelect.disabled = false;
            
            if (callback) callback();
        } catch (error) {
            console.error('Error loading districts:', error);
            this.showError('Failed to load districts. Please try again.');
        } finally {
            this.setLoading(this.districtSelect, false);
        }
    }
    
    async loadVillages(districtCode, callback = null) {
        try {
            this.setLoading(this.villageSelect, true);
            
            const response = await fetch(`/api/villages?district_code=${districtCode}`);
            if (!response.ok) throw new Error('Failed to load villages');
            
            const villages = await response.json();
            this.populateSelect(this.villageSelect, villages, 'Select Village', true);
            this.villageSelect.disabled = false;
            
            if (callback) callback();
        } catch (error) {
            console.error('Error loading villages:', error);
            this.showError('Failed to load villages. Please try again.');
        } finally {
            this.setLoading(this.villageSelect, false);
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
            select.innerHTML = `<option value="">-- Select ${this.capitalize(name)} --</option>`;
            select.disabled = true;
        });
        
        // Clear postal code if village is reset
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
        // You can implement a toast notification or alert here
        alert(message);
    }
    
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new AddressForm();
});
```

## Vue.js Implementation

For modern frontend frameworks, here's a Vue.js component:

```vue
<template>
  <form @submit.prevent="submitForm" class="address-form">
    <div class="form-group">
      <label for="province">Province</label>
      <select 
        id="province" 
        v-model="form.province_code" 
        @change="onProvinceChange"
        :disabled="loading.provinces"
        required
      >
        <option value="">-- Select Province --</option>
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
      <label for="regency">Regency/City</label>
      <select 
        id="regency" 
        v-model="form.regency_code" 
        @change="onRegencyChange"
        :disabled="!form.province_code || loading.regencies"
        required
      >
        <option value="">-- Select Regency --</option>
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
      <label for="district">District</label>
      <select 
        id="district" 
        v-model="form.district_code" 
        @change="onDistrictChange"
        :disabled="!form.regency_code || loading.districts"
        required
      >
        <option value="">-- Select District --</option>
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
      <label for="village">Village</label>
      <select 
        id="village" 
        v-model="form.village_code" 
        @change="onVillageChange"
        :disabled="!form.district_code || loading.villages"
        required
      >
        <option value="">-- Select Village --</option>
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
      <label for="postal_code">Postal Code</label>
      <input 
        id="postal_code" 
        v-model="form.postal_code" 
        type="text" 
        maxlength="5"
        placeholder="Auto-filled from village"
      >
    </div>
    
    <button type="submit" :disabled="submitting">
      {{ submitting ? 'Saving...' : 'Save Address' }}
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
          throw new Error('Failed to save address');
        }
      } catch (error) {
        console.error('Error saving address:', error);
        alert('Failed to save address. Please try again.');
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

This comprehensive address form implementation provides:

- **Cascading dropdowns** that load data dynamically
- **Validation** on both frontend and backend
- **Error handling** with user-friendly messages
- **Auto-completion** of postal codes
- **Performance optimization** with loading states
- **Accessibility** with proper labels and ARIA attributes
- **Framework flexibility** with both vanilla JS and Vue.js examples

The form ensures data consistency by validating that all address components belong to the correct hierarchy and provides a smooth user experience with proper loading states and error handling.
