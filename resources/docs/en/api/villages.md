# Villages API

The Villages API provides access to all 83,762 Indonesian villages (kelurahan/desa) with their postal codes and administrative relationships.

## Endpoints

### List Villages

```http
GET /nusa/villages
```

Returns a paginated list of all villages.

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 15, max: 100) |
| `search` | string | Search by name or code |
| `codes[]` | array | Filter by specific village codes |

#### Example Request

```bash
curl "https://your-app.com/nusa/villages?search=medono&per_page=10"
```

### Get Village

```http
GET /nusa/villages/{code}
```

Returns a specific village by its code.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `code` | string | 10-digit village code |

#### Example Request

```bash
curl "https://your-app.com/nusa/villages/3375011002"
```

## Data Attributes

### Village Object

| Attribute | Type | Description |
|-----------|------|-------------|
| `code` | string | 10-digit village code |
| `district_code` | string | Parent district code |
| `regency_code` | string | Parent regency code |
| `province_code` | string | Parent province code |
| `name` | string | Village name in Indonesian |
| `postal_code` | string | 5-digit postal code |
