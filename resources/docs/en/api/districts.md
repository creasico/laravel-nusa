# Districts API

The Districts API provides access to all 7,266 Indonesian districts (kecamatan) with their administrative relationships.

## Endpoints

### List Districts

```http
GET /nusa/districts
```

Returns a paginated list of all districts.

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 15, max: 100) |
| `search` | string | Search by name or code |
| `codes[]` | array | Filter by specific district codes |

#### Example Request

```bash
curl "https://your-app.com/nusa/districts?search=pekalongan&per_page=10"
```

### Get District

```http
GET /nusa/districts/{code}
```

Returns a specific district by its code.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `code` | string | District code in xx.xx.xx format |

#### Example Request

```bash
curl "https://your-app.com/nusa/districts/33.75.01"
```

### Get District Villages

```http
GET /nusa/districts/{code}/villages
```

Returns all villages within a specific district.

#### Example Request

```bash
curl "https://your-app.com/nusa/districts/33.75.01/villages"
```

## Data Attributes

### District Object

| Attribute | Type | Description |
|-----------|------|-------------|
| `code` | string | District code in xx.xx.xx format |
| `regency_code` | string | Parent regency code |
| `province_code` | string | Parent province code |
| `name` | string | District name in Indonesian |
| `postal_codes` | array | All postal codes within the district |
