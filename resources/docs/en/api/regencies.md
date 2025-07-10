# Regencies API

The Regencies API provides access to all 514 Indonesian regencies and cities (kabupaten/kota) with their geographic data and administrative relationships.

## Endpoints

### List Regencies

```http
GET /nusa/regencies
```

Returns a paginated list of all regencies.

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 15, max: 100) |
| `search` | string | Search by name or code |
| `codes[]` | array | Filter by specific regency codes |

#### Example Request

```bash
curl "https://your-app.com/nusa/regencies?search=jakarta&per_page=10"
```

#### Example Response

```json
{
  "data": [
    {
      "code": "31.71",
      "province_code": "31",
      "name": "Kota Jakarta Selatan",
      "latitude": -6.2615,
      "longitude": 106.8106,
      "coordinates": [...],
      "postal_codes": ["12110", "12120", "..."]
    }
  ],
  "links": {
    "first": "https://your-app.com/nusa/regencies?page=1",
    "last": "https://your-app.com/nusa/regencies?page=35",
    "prev": null,
    "next": "https://your-app.com/nusa/regencies?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 35,
    "per_page": 15,
    "to": 15,
    "total": 514
  }
}
```

### Get Regency

```http
GET /nusa/regencies/{code}
```

Returns a specific regency by its code.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `code` | string | Regency code in xx.xx format |

#### Example Request

```bash
curl "https://your-app.com/nusa/regencies/33.75"
```

### Get Regency Districts

```http
GET /nusa/regencies/{code}/districts
```

Returns all districts within a specific regency.

#### Example Request

```bash
curl "https://your-app.com/nusa/regencies/33.75/districts"
```

### Get Regency Villages

```http
GET /nusa/regencies/{code}/villages
```

Returns all villages within a specific regency.

#### Example Request

```bash
curl "https://your-app.com/nusa/regencies/33.75/villages?per_page=50"
```

## Data Attributes

### Regency Object

| Attribute | Type | Description |
|-----------|------|-------------|
| `code` | string | Regency code in xx.xx format |
| `province_code` | string | Parent province code |
| `name` | string | Regency/City name in Indonesian |
| `latitude` | number | Geographic center latitude |
| `longitude` | number | Geographic center longitude |
| `coordinates` | array | Boundary polygon coordinates |
| `postal_codes` | array | All postal codes within the regency |
