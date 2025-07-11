# Contoh Integrasi API

Halaman ini menyediakan contoh praktis integrasi dengan API Laravel Nusa dari berbagai platform dan bahasa pemrograman.

## Integrasi JavaScript/Frontend

### JavaScript Murni

```js
class NusaAPI {
  constructor(baseUrl = '/nusa') {
    this.baseUrl = baseUrl;
  }
  
  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    const response = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...options.headers
      },
      ...options
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return response.json();
  }
  
  // Metode Provinsi
  async getProvinces(params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/provinces?${query}`);
  }
  
  async getProvince(code) {
    return this.request(`/provinces/${code}`);
  }
  
  // Metode Kabupaten/Kota
  async getRegencies(params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/regencies?${query}`);
  }
  
  async getRegenciesByProvince(provinceCode, params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/provinces/${provinceCode}/regencies?${query}`);
  }
  
  // Metode Kecamatan
  async getDistrictsByRegency(regencyCode, params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/regencies/${regencyCode}/districts?${query}`);
  }
  
  // Metode Desa/Kelurahan
  async getVillagesByDistrict(districtCode, params = {}) {
    const query = new URLSearchParams(params);
    return this.request(`/districts/${districtCode}/villages?${query}`);
  }
  
  // Metode Pencarian
  async searchProvinces(query) {
    return this.getProvinces({ search: query });
  }
  
  async searchRegencies(query) {
    return this.getRegencies({ search: query });
  }
}

// Penggunaan
const nusa = new NusaAPI();

// Dapatkan semua provinsi
const provinces = await nusa.getProvinces();
console.log(provinces.data);

// Cari provinsi Jawa
const javaProvinces = await nusa.searchProvinces('jawa');
console.log(javaProvinces.data);

// Dapatkan kabupaten/kota di Jawa Tengah
const regencies = await nusa.getRegenciesByProvince('33');
console.log(regencies.data);
```

### React Hook

```jsx
import { useState, useEffect } from 'react';

// Hook kustom untuk Nusa API
function useNusaAPI() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  
  const request = async (endpoint, options = {}) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await fetch(`/nusa${endpoint}`, {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          ...options.headers
        },
        ...options
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      return data;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };
  
  return { request, loading, error };
}

// Komponen formulir alamat
function AddressForm() {
  const { request, loading, error } = useNusaAPI();
  const [provinces, setProvinces] = useState([]);
  const [regencies, setRegencies] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [villages, setVillages] = useState([]);
  
  const [selectedProvince, setSelectedProvince] = useState('');
  const [selectedRegency, setSelectedRegency] = useState('');
  const [selectedDistrict, setSelectedDistrict] = useState('');
  const [selectedVillage, setSelectedVillage] = useState('');
  
  // Muat provinsi saat komponen dimuat
  useEffect(() => {
    const loadProvinces = async () => {
      try {
        const response = await request('/provinces');
        setProvinces(response.data);
      } catch (err) {
        console.error('Gagal memuat provinsi:', err);
      }
    };
    
    loadProvinces();
  }, []);
  
  // Muat kabupaten/kota saat provinsi berubah
  useEffect(() => {
    if (selectedProvince) {
      const loadRegencies = async () => {
        try {
          const response = await request(`/provinces/${selectedProvince}/regencies`);
          setRegencies(response.data);
          setDistricts([]);
          setVillages([]);
          setSelectedRegency('');
          setSelectedDistrict('');
          setSelectedVillage('');
        } catch (err) {
          console.error('Gagal memuat kabupaten/kota:', err);
        }
      };
      
      loadRegencies();
    }
  }, [selectedProvince]);
  
  // Muat kecamatan saat kabupaten/kota berubah
  useEffect(() => {
    if (selectedRegency) {
      const loadDistricts = async () => {
        try {
          const response = await request(`/regencies/${selectedRegency}/districts`);
          setDistricts(response.data);
          setVillages([]);
          setSelectedDistrict('');
          setSelectedVillage('');
        } catch (err) {
          console.error('Gagal memuat kecamatan:', err);
        }
      };
      
      loadDistricts();
    }
  }, [selectedRegency]);
  
  // Muat desa/kelurahan saat kecamatan berubah
  useEffect(() => {
    if (selectedDistrict) {
      const loadVillages = async () => {
        try {
          const response = await request(`/districts/${selectedDistrict}/villages`);
          setVillages(response.data);
          setSelectedVillage('');
        } catch (err) {
          console.error('Gagal memuat desa/kelurahan:', err);
        }
      };
      
      loadVillages();
    }
  }, [selectedDistrict]);
  
  return (
    <form>
      {error && <div className="error">Error: {error}</div>}
      
      <div>
        <label>Provinsi:</label>
        <select 
          value={selectedProvince} 
          onChange={(e) => setSelectedProvince(e.target.value)}
          disabled={loading}
        >
          <option value="">Pilih Provinsi</option>
          {provinces.map(province => (
            <option key={province.code} value={province.code}>
              {province.name}
            </option>
          ))}
        </select>
      </div>
      
      <div>
        <label>Kabupaten/Kota:</label>
        <select 
          value={selectedRegency} 
          onChange={(e) => setSelectedRegency(e.target.value)}
          disabled={loading || !selectedProvince}
        >
          <option value="">Pilih Kabupaten/Kota</option>
          {regencies.map(regency => (
            <option key={regency.code} value={regency.code}>
              {regency.name}
            </option>
          ))}
        </select>
      </div>
      
      <div>
        <label>Kecamatan:</label>
        <select 
          value={selectedDistrict} 
          onChange={(e) => setSelectedDistrict(e.target.value)}
          disabled={loading || !selectedRegency}
        >
          <option value="">Pilih Kecamatan</option>
          {districts.map(district => (
            <option key={district.code} value={district.code}>
              {district.name}
            </option>
          ))}
        </select>
      </div>
      
      <div>
        <label>Desa/Kelurahan:</label>
        <select 
          value={selectedVillage} 
          onChange={(e) => setSelectedVillage(e.target.value)}
          disabled={loading || !selectedDistrict}
        >
          <option value="">Pilih Desa/Kelurahan</option>
          {villages.map(village => (
            <option key={village.code} value={village.code}>
              {village.name} {village.postal_code && `(${village.postal_code})`}
            </option>
          ))}
        </select>
      </div>
      
      {loading && <div>Memuat...</div>}
    </form>
  );
}

export default AddressForm;
```

## Integrasi PHP

### Laravel HTTP Client

```php
namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class NusaAPIService
{
    private string $baseUrl;
    private int $timeout;
    private int $cacheTimeout;
    
    public function __construct()
    {
        $this->baseUrl = config('app.url') . '/nusa';
        $this->timeout = 30;
        $this->cacheTimeout = 3600; // 1 jam
    }
    
    private function request(string $endpoint, array $params = []): array
    {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . $endpoint, $params);
            
        if ($response->failed()) {
            throw new \Exception("Permintaan API gagal: {$response->status()}");
        }
        
        return $response->json();
    }
    
    public function getProvinces(array $params = []): array
    {
        $cacheKey = 'nusa.provinces.' . md5(serialize($params));
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($params) {
            return $this->request('/provinces', $params);
        });
    }
    
    public function getProvince(string $code): array
    {
        $cacheKey = "nusa.province.{$code}";
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($code) {
            return $this->request("/provinces/{$code}");
        });
    }
    
    public function getRegenciesByProvince(string $provinceCode, array $params = []): array
    {
        $cacheKey = "nusa.regencies.{$provinceCode}." . md5(serialize($params));
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($provinceCode, $params) {
            return $this->request("/provinces/{$provinceCode}/regencies", $params);
        });
    }
    
    public function getDistrictsByRegency(string $regencyCode, array $params = []): array
    {
        $cacheKey = "nusa.districts.{$regencyCode}." . md5(serialize($params));
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($regencyCode, $params) {
            return $this->request("/regencies/{$regencyCode}/districts", $params);
        });
    }
    
    public function getVillagesByDistrict(string $districtCode, array $params = []): array
    {
        $cacheKey = "nusa.villages.{$districtCode}." . md5(serialize($params));
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($districtCode, $params) {
            return $this->request("/districts/{$districtCode}/villages", $params);
        });
    }
    
    public function searchProvinces(string $query): array
    {
        return $this->getProvinces(['search' => $query]);
    }
    
    public function searchRegencies(string $query): array
    {
        return $this->request('/regencies', ['search' => $query]);
    }
    
    public function getFullAddressHierarchy(string $villageCode): array
    {
        $village = $this->request("/villages/{$villageCode}");
        $villageData = $village['data'];
        
        $district = $this->request("/districts/{$villageData['district_code']}");
        $regency = $this->request("/regencies/{$villageData['regency_code']}");
        $province = $this->request("/provinces/{$villageData['province_code']}");
        
        return [
            'village' => $villageData,
            'district' => $district['data'],
            'regency' => $regency['data'],
            'province' => $province['data'],
            'full_address' => implode(', ', [
                $villageData['name'],
                $district['data']['name'],
                $regency['data']['name'],
                $province['data']['name'],
                $villageData['postal_code'] ?? ''
            ])
        ];
    }
}

// Penggunaan di controller
class AddressController extends Controller
{
    private NusaAPIService $nusaAPI;
    
    public function __construct(NusaAPIService $nusaAPI)
    {
        $this->nusaAPI = $nusaAPI;
    }
    
    public function getProvinces()
    {
        try {
            $provinces = $this->nusaAPI->getProvinces();
            return response()->json($provinces['data']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getRegencies(Request $request)
    {
        $request->validate([
            'province_code' => 'required|string|size:2'
        ]);
        
        try {
            $regencies = $this->nusaAPI->getRegenciesByProvince(
                $request->province_code
            );
            return response()->json($regencies['data']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

## Integrasi Python

### Menggunakan Pustaka Requests

```python
import requests
from typing import Dict, List, Optional
import time

class NusaAPI:
    def __init__(self, base_url: str = "https://your-app.com/nusa", timeout: int = 30):
        self.base_url = base_url.rstrip('/')
        self.timeout = timeout
        self.session = requests.Session()
        self.session.headers.update({
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        })
        
    def _request(self, endpoint: str, params: Optional[Dict] = None) -> Dict:
        """Make a request to the API"""
        url = f"{self.base_url}{endpoint}"
        
        try:
            response = self.session.get(url, params=params, timeout=self.timeout)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            raise Exception(f"Permintaan API gagal: {e}")
    
    def get_provinces(self, params: Optional[Dict] = None) -> Dict:
        """Get all provinces"""
        return self._request('/provinces', params)
    
    def get_province(self, code: str) -> Dict:
        """Get specific province by code"""
        return self._request(f'/provinces/{code}')
    
    def get_regencies_by_province(self, province_code: str, params: Optional[Dict] = None) -> Dict:
        """Get regencies in a province"""
        return self._request(f'/provinces/{province_code}/regencies', params)
    
    def get_districts_by_regency(self, regency_code: str, params: Optional[Dict] = None) -> Dict:
        """Get districts in a regency"""
        return self._request(f'/regencies/{regency_code}/districts', params)
    
    def get_villages_by_district(self, district_code: str, params: Optional[Dict] = None) -> Dict:
        """Get villages in a district"""
        return self._request(f'/districts/{district_code}/villages', params)
    
    def search_provinces(self, query: str) -> Dict:
        """Search provinces by name or code"""
        return self.get_provinces({'search': query})
    
    def search_regencies(self, query: str) -> Dict:
        """Search regencies by name or code"""
        return self._request('/regencies', {'search': query})
    
    def get_full_address_hierarchy(self, village_code: str) -> Dict:
        """Get complete address hierarchy for a village"""
        village = self._request(f'/villages/{village_code}')
        village_data = village['data']
        
        district = self._request(f"/districts/{village_data['district_code']}")
        regency = self._request(f"/regencies/{village_data['regency_code']}")
        province = self._request(f"/provinces/{village_data['province_code']}")
        
        return {
            'village': village_data,
            'district': district['data'],
            'regency': regency['data'],
            'province': province['data'],
            'full_address': ', '.join(filter(None, [
                village_data['name'],
                district['data']['name'],
                regency['data']['name'],
                province['data']['name'],
                village_data.get('postal_code', '')
            ]))
        }

# Contoh penggunaan
def main():
    nusa = NusaAPI()
    
    # Dapatkan semua provinsi
    provinces = nusa.get_provinces()
    print(f"Ditemukan {len(provinces['data'])} provinsi")
    
    # Cari provinsi Jawa
    java_provinces = nusa.search_provinces("jawa")
    for province in java_provinces['data']:
        print(f"- {province['name']} ({province['code']})")
    
    # Dapatkan kabupaten/kota di Jawa Tengah
    regencies = nusa.get_regencies_by_province("33")
    print(f"Jawa Tengah memiliki {len(regencies['data'])} kabupaten/kota")
    
    # Dapatkan hierarki alamat lengkap
    address = nusa.get_full_address_hierarchy("3375011002")
    print(f"Alamat lengkap: {address['full_address']}")

if __name__ == "__main__":
    main()
```

### Integrasi Django

```python
# services.py
from django.conf import settings
from django.core.cache import cache
import requests

class NusaAPIService:
    def __init__(self):
        self.base_url = getattr(settings, 'NUSA_API_URL', 'https://your-app.com/nusa')
        self.timeout = getattr(settings, 'NUSA_API_TIMEOUT', 30)
        self.cache_timeout = getattr(settings, 'NUSA_CACHE_TIMEOUT', 3600)
    
    def _get_cached_or_fetch(self, cache_key: str, url: str, params=None):
        """Get from cache or fetch from API"""
        cached_data = cache.get(cache_key)
        if cached_data:
            return cached_data
        
        response = requests.get(url, params=params, timeout=self.timeout)
        response.raise_for_status()
        data = response.json()
        
        cache.set(cache_key, data, self.cache_timeout)
        return data
    
    def get_provinces(self):
        cache_key = 'nusa_provinces'
        url = f'{self.base_url}/provinces'
        return self._get_cached_or_fetch(cache_key, url)
    
    def get_regencies_by_province(self, province_code: str):
        cache_key = f'nusa_regencies_{province_code}'
        url = f'{self.base_url}/provinces/{province_code}/regencies'
        return self._get_cached_or_fetch(cache_key, url)

# views.py
from django.http import JsonResponse
from django.views import View
from .services import NusaAPIService

class ProvinceListView(View):
    def get(self, request):
        try:
            nusa_service = NusaAPIService()
            provinces = nusa_service.get_provinces()
            return JsonResponse(provinces['data'], safe=False)
        except Exception as e:
            return JsonResponse({'error': str(e)}, status=500)

class RegencyListView(View):
    def get(self, request, province_code):
        try:
            nusa_service = NusaAPIService()
            regencies = nusa_service.get_regencies_by_province(province_code)
            return JsonResponse(regencies['data'], safe=False)
        except Exception as e:
            return JsonResponse({'error': str(e)}, status=500)
```

## Integrasi Aplikasi Seluler

### Flutter/Dart

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class NusaAPI {
  final String baseUrl;
  final Duration timeout;
  
  NusaAPI({
    this.baseUrl = 'https://your-app.com/nusa',
    this.timeout = const Duration(seconds: 30),
  });
  
  Future<Map<String, dynamic>> _request(String endpoint, [Map<String, String>? params]) async {
    final uri = Uri.parse('$baseUrl$endpoint').replace(queryParameters: params);
    
    try {
      final response = await http.get(
        uri,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ).timeout(timeout);
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      throw Exception('Permintaan API gagal: $e');
    }
  }
  
  Future<List<dynamic>> getProvinces({String? search}) async {
    final params = search != null ? {'search': search} : null;
    final response = await _request('/provinces', params);
    return response['data'];
  }
  
  Future<Map<String, dynamic>> getProvince(String code) async {
    final response = await _request('/provinces/$code');
    return response['data'];
  }
  
  Future<List<dynamic>> getRegenciesByProvince(String provinceCode) async {
    final response = await _request('/provinces/$provinceCode/regencies');
    return response['data'];
  }
  
  Future<List<dynamic>> getDistrictsByRegency(String regencyCode) async {
    final response = await _request('/regencies/$regencyCode/districts');
    return response['data'];
  }
  
  Future<List<dynamic>> getVillagesByDistrict(String districtCode) async {
    final response = await _request('/districts/$districtCode/villages');
    return response['data'];
  }
}

// Penggunaan di widget Flutter
class AddressFormWidget extends StatefulWidget {
  @override
  _AddressFormWidgetState createState() => _AddressFormWidgetState();
}

class _AddressFormWidgetState extends State<AddressFormWidget> {
  final NusaAPI _nusaAPI = NusaAPI();
  
  List<dynamic> provinces = [];
  List<dynamic> regencies = [];
  List<dynamic> districts = [];
  List<dynamic> villages = [];
  
  String? selectedProvince;
  String? selectedRegency;
  String? selectedDistrict;
  String? selectedVillage;
  
  bool isLoading = false;
  
  @override
  void initState() {
    super.initState();
    _loadProvinces();
  }
  
  Future<void> _loadProvinces() async {
    setState(() => isLoading = true);
    try {
      final data = await _nusaAPI.getProvinces();
      setState(() => provinces = data);
    } catch (e) {
      print('Gagal memuat provinsi: $e');
    } finally {
      setState(() => isLoading = false);
    }
  }
  
  Future<void> _loadRegencies(String provinceCode) async {
    setState(() => isLoading = true);
    try {
      final data = await _nusaAPI.getRegenciesByProvince(provinceCode);
      setState(() {
        regencies = data;
        districts = [];
        villages = [];
        selectedRegency = null;
        selectedDistrict = null;
        selectedVillage = null;
      });
    } catch (e) {
      print('Gagal memuat kabupaten/kota: $e');
    } finally {
      setState(() => isLoading = false);
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        DropdownButtonFormField<String>(
          value: selectedProvince,
          decoration: InputDecoration(labelText: 'Provinsi'),
          items: provinces.map<DropdownMenuItem<String>>((province) {
            return DropdownMenuItem<String>(
              value: province['code'],
              child: Text(province['name']),
            );
          }).toList(),
          onChanged: (value) {
            setState(() => selectedProvince = value);
            if (value != null) _loadRegencies(value);
          },
        ),
        // Tambahkan dropdown serupa untuk kabupaten/kota, kecamatan, desa/kelurahan
        if (isLoading) CircularProgressIndicator(),
      ],
    );
  }
}
```

Contoh-contoh ini menunjukkan bagaimana mengintegrasikan dengan API Laravel Nusa dari berbagai platform dan bahasa pemrograman, menyediakan fondasi yang kuat untuk membangun formulir alamat dan fitur berbasis lokasi di aplikasi Anda.