# Integrasi API

Panduan lengkap untuk mengintegrasikan RESTful API Laravel Nusa ke dalam aplikasi frontend, termasuk contoh implementasi dengan JavaScript, React, Vue.js, dan framework lainnya.

## Integrasi API Dasar

### Vanilla JavaScript

```javascript
// Class API client
class NusaAPI {
    constructor(baseURL = '/nusa') {
        this.baseURL = baseURL;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }

        return response.json();
    }

    // Method provinsi
    async getProvinces(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/provinces?${query}`);
    }

    async getProvince(code, include = []) {
        const query = include.length ? `?include=${include.join(',')}` : '';
        return this.request(`/provinces/${code}${query}`);
    }

    // Method kabupaten/kota
    async getRegencies(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/regencies?${query}`);
    }

    async getRegenciesByProvince(provinceCode, params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/provinces/${provinceCode}/regencies?${query}`);
    }
}

// Penggunaan
const api = new NusaAPI();

// Load provinsi
const provinces = await api.getProvinces({ sort: 'name' });
console.log(provinces.data);

// Load kabupaten/kota berdasarkan provinsi
const regencies = await api.getRegenciesByProvince('33', { sort: 'name' });
console.log(regencies.data);
```

### Komponen Location Selector

```javascript
class LocationSelector {
    constructor(container) {
        this.container = container;
        this.api = new NusaAPI();
        this.init();
    }

    init() {
        this.container.innerHTML = `
            <div class="location-selector">
                <select id="province" class="form-select">
                    <option value="">Pilih Provinsi</option>
                </select>
                <select id="regency" class="form-select" disabled>
                    <option value="">Pilih Kabupaten/Kota</option>
                </select>
                <select id="district" class="form-select" disabled>
                    <option value="">Pilih Kecamatan</option>
                </select>
                <select id="village" class="form-select" disabled>
                    <option value="">Pilih Kelurahan/Desa</option>
                </select>
            </div>
        `;

        this.bindEvents();
        this.loadProvinces();
    }

    bindEvents() {
        const province = this.container.querySelector('#province');
        const regency = this.container.querySelector('#regency');
        const district = this.container.querySelector('#district');

        province.addEventListener('change', (e) => {
            if (e.target.value) {
                this.loadRegencies(e.target.value);
            } else {
                this.resetSelect(regency);
                this.resetSelect(district);
                this.resetSelect(this.container.querySelector('#village'));
            }
        });

        regency.addEventListener('change', (e) => {
            if (e.target.value) {
                this.loadDistricts(e.target.value);
            } else {
                this.resetSelect(district);
                this.resetSelect(this.container.querySelector('#village'));
            }
        });
        
        district.addEventListener('change', (e) => {
            if (e.target.value) {
                this.loadVillages(e.target.value);
            } else {
                this.resetSelect(this.container.querySelector('#village'));
            }
        });
    }
    
    async loadProvinces() {
        try {
            const response = await this.api.getProvinces({ sort: 'name' });
            const select = this.container.querySelector('#province');
            
            response.data.forEach(province => {
                const option = new Option(province.name, province.code);
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading provinces:', error);
        }
    }
    
    async loadRegencies(provinceCode) {
        try {
            const response = await this.api.getRegenciesByProvince(provinceCode, { sort: 'name' });
            const select = this.container.querySelector('#regency');
            
            this.resetSelect(select);
            select.disabled = false;
            
            response.data.forEach(regency => {
                const option = new Option(regency.name, regency.code);
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading regencies:', error);
        }
    }
    
    resetSelect(select) {
        select.innerHTML = `<option value="">${select.dataset.placeholder || 'Pilih...'}</option>`;
        select.disabled = true;
    }
    
    getSelectedValues() {
        return {
            province: this.container.querySelector('#province').value,
            regency: this.container.querySelector('#regency').value,
            district: this.container.querySelector('#district').value,
            village: this.container.querySelector('#village').value
        };
    }
}

// Penggunaan
const selector = new LocationSelector(document.getElementById('location-container'));
```

## Integrasi React

### Custom Hook

```jsx
// hooks/useNusaAPI.js
import { useState, useEffect } from 'react';

const API_BASE = '/nusa';

export function useNusaAPI() {
    const request = async (endpoint, options = {}) => {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            headers: {
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }
        
        return response.json();
    };
    
    return { request };
}

export function useProvinces() {
    const [provinces, setProvinces] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const { request } = useNusaAPI();
    
    useEffect(() => {
        const loadProvinces = async () => {
            try {
                setLoading(true);
                const response = await request('/provinces?sort=name');
                setProvinces(response.data);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };
        
        loadProvinces();
    }, []);
    
    return { provinces, loading, error };
}

export function useRegencies(provinceCode) {
    const [regencies, setRegencies] = useState([]);
    const [loading, setLoading] = useState(false);
    const { request } = useNusaAPI();
    
    useEffect(() => {
        if (!provinceCode) {
            setRegencies([]);
            return;
        }
        
        const loadRegencies = async () => {
            try {
                setLoading(true);
                const response = await request(`/provinces/${provinceCode}/regencies?sort=name`);
                setRegencies(response.data);
            } catch (err) {
                console.error('Error loading regencies:', err);
                setRegencies([]);
            } finally {
                setLoading(false);
            }
        };
        
        loadRegencies();
    }, [provinceCode]);
    
    return { regencies, loading };
}
```

### Location Selector Component

```jsx
// components/LocationSelector.jsx
import React, { useState } from 'react';
import { useProvinces, useRegencies } from '../hooks/useNusaAPI';

export function LocationSelector({ onSelectionChange }) {
    const [selectedProvince, setSelectedProvince] = useState('');
    const [selectedRegency, setSelectedRegency] = useState('');
    const [selectedDistrict, setSelectedDistrict] = useState('');
    const [selectedVillage, setSelectedVillage] = useState('');
    
    const { provinces, loading: provincesLoading } = useProvinces();
    const { regencies, loading: regenciesLoading } = useRegencies(selectedProvince);
    
    const handleProvinceChange = (e) => {
        const value = e.target.value;
        setSelectedProvince(value);
        setSelectedRegency('');
        setSelectedDistrict('');
        setSelectedVillage('');
        
        onSelectionChange?.({
            province: value,
            regency: '',
            district: '',
            village: ''
        });
    };
    
    const handleRegencyChange = (e) => {
        const value = e.target.value;
        setSelectedRegency(value);
        setSelectedDistrict('');
        setSelectedVillage('');
        
        onSelectionChange?.({
            province: selectedProvince,
            regency: value,
            district: '',
            village: ''
        });
    };
    
    return (
        <div className="location-selector">
            <div className="form-group">
                <label htmlFor="province">Provinsi</label>
                <select
                    id="province"
                    value={selectedProvince}
                    onChange={handleProvinceChange}
                    disabled={provincesLoading}
                    className="form-control"
                >
                    <option value="">Pilih Provinsi</option>
                    {provinces.map(province => (
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
                    value={selectedRegency}
                    onChange={handleRegencyChange}
                    disabled={!selectedProvince || regenciesLoading}
                    className="form-control"
                >
                    <option value="">Pilih Kabupaten/Kota</option>
                    {regencies.map(regency => (
                        <option key={regency.code} value={regency.code}>
                            {regency.name}
                        </option>
                    ))}
                </select>
            </div>
            
            {/* Add District and Village selectors similarly */}
        </div>
    );
}
```

## Next Steps

- **[Address Forms](/id/examples/address-forms)** - Complete address form implementation
- **[Geographic Queries](/id/examples/geographic-queries)** - Location-based queries and mapping
- **[Custom Models](/id/examples/custom-models)** - Extending Laravel Nusa models
- **[API Reference](/id/api/overview)** - Complete API documentation
