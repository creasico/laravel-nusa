# HTTP Endpoints

Laravel Nusa provides a comprehensive **Static Data API** that allows you to access administrative region data directly via HTTP requests. This data is pre-generated and stored as static files, making it extremely fast and suitable for client-side consumption or caching.

## Base URL

The static API is accessible via the `/static` path on your application domain. For the demo environment:

```
https://nusa.creasi.dev/static/
```

## Data Formats

The endpoints support multiple formats to suit different use cases. You can select the format by changing the file extension in the URL.

| Extension | Format | Description |
| :--- | :--- | :--- |
| `.json` | JSON | Standard structured data for web applications. |
| `.csv` | CSV | Tabular data, useful for imports or spreadsheet analysis. |
| `.geojson` | GeoJSON | Geographic data including boundaries (Polygon/MultiPolygon), used for mapping. |

## Endpoint Structure

The URL structure follows the hierarchical administrative levels of Indonesia: **Province** > **Regency** > **District** > **Village**.

Each endpoint follows the same pattern which can be accessed with or without file extension and by default will response as `json`. Additional supported file extension are `csv` to download the data as `.csv` and `geojson` to retrieve the geographic data.

### 1. Provinces (Provinsi)

You can retrieve the master list of all provinces or details for a specific province.

**List of all Provinces**
```http
GET /static
GET /static/index.json
GET /static/index.csv
```

**Specific Province Details**
Returns the province data and a list of all regencies within it.
```http
GET /static/{province_code}
GET /static/{province_code}.json
GET /static/{province_code}.csv
GET /static/{province_code}.geojson
```

**Example:**
- `https://nusa.creasi.dev/static/11` (Aceh)

### 2. Regencies (Kabupaten/Kota)

Retrieve details for a specific regency, including a list of all districts within it.

**URL Pattern**
```http
GET /static/{province_code}/{regency_code}
GET /static/{province_code}/{regency_code}.json
GET /static/{province_code}/{regency_code}.csv
GET /static/{province_code}/{regency_code}.geojson
```

**Example:**
- `https://nusa.creasi.dev/static/11/01` (Kab. Aceh Selatan)

### 3. Districts (Kecamatan)

Retrieve details for a specific district, including a list of all villages within it.

**URL Pattern**
```http
GET /static/{province_code}/{regency_code}/{district_code}
GET /static/{province_code}/{regency_code}/{district_code}.json
GET /static/{province_code}/{regency_code}/{district_code}.csv
GET /static/{province_code}/{regency_code}/{district_code}.geojson
```

**Example:**
- `https://nusa.creasi.dev/static/11/01/01` (Kec. Bakongan)

### 4. Villages (Kelurahan/Desa)

Retrieve details for a specific village.

**URL Pattern**
```http
GET /static/{province_code}/{regency_code}/{district_code}/{village_code}
GET /static/{province_code}/{regency_code}/{district_code}/{village_code}.json
GET /static/{province_code}/{regency_code}/{district_code}/{village_code}.csv
GET /static/{province_code}/{regency_code}/{district_code}/{village_code}.geojson
```

**Example:**
- `https://nusa.creasi.dev/static/11/01/01/2001` (Gampong Keude Bakongan)

## Response Examples

### JSON Response (Province)
Request: `GET /static/11.json`

```json
{
    "code": "11",
    "name": "Aceh",
    "latitude": 4.2257285830382,
    "longitude": 96.9118740861,
    "regencies": [
        {
            "code": "11.01",
            "province_code": "11",
            "name": "Kabupaten Aceh Selatan",
            "latitude": 3.1618538408941,
            "longitude": 97.436517718652
        },
        ...
    ]
}
```

### GeoJSON Response
Request: `GET /static/11.geojson`

```json
{
    "type": "Feature",
    "properties": {
        "code": "11",
        "name": "Aceh"
    },
    "geometry": {
        "type": "MultiPolygon",
        "coordinates": [ ... ]
    }
}
```

## Client-Side Usage

Since these are static files, you can fetch them directly using `fetch` or `axios` in your frontend application.

```javascript
// Example: Fetching districts for a selected regency
fetch(`https://nusa.creasi.dev/static/11/01`)
    .then(response => response.json())
    .then(data => {
        console.log("Regency:", data.name);
        console.log("Districts:", data.districts);
    });
```

## Next Steps

Explore the detailed API documentation for each endpoint:

- **[Boundaries Demo](/en/examples/boundaries)** - Example implementation of GeoJSON
- **[Provinces API](/en/api/provinces)** - Province endpoints and examples
- **[Regencies API](/en/api/regencies)** - Regency endpoints and examples
- **[Districts API](/en/api/districts)** - District endpoints and examples
- **[Villages API](/en/api/villages)** - Village endpoints and examples
