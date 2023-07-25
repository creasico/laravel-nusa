# Database Structure

```mermaid
classDiagram
    Province "1" --> "*" Regency
    Province "1" --> "*" District
    Province "1" --> "*" Village
    Regency "1" --> "*" Village
    Regency "1" --> "*" District
    District "1" --> "*" Village

    class Province {
        +int code
        +string name
        +double latitude
        +double longitude
        +array coordinates
        +regencies() Regency[]
        +districts() District[]
        +villages() Village[]
    }
    class Regency {
        +int code
        +int province_code
        +string name
        +double latitude
        +double longitude
        +array coordinates
        +province() Province
        +districts() District[]
        +villages() Village[]
    }
    class District {
        +int code
        +int regency_code
        +int province_code
        +string name
        +province() Province
        +regency() Regency
        +villages() Village[]
    }
    class Village {
        +int code
        +int district_code
        +int regency_code
        +int province_code
        +string name
        +int postal_code
        +province() Province
        +regency() Regency
        +district() District
    }
```

## `provinces`

| Field | Attribute | Key | Description |
| --- | --- | --- | --- |
| `code` | `char(2)` | `primary` | - |
| `name` | `varchar` | - | - |
| `latitude` | `double`, `nullable` | - | - |
| `longitude` | `double`, `nullable` | - | - |
| `coordinates` | `array`, `nullable` | - | - |

## `regencies`

| Field | Attribute | Key | Description |
| --- | --- | --- | --- |
| `code` | `char(4)` | `primary` | - |
| `province_code` | `char(2)` | `foreign` | - |
| `name` | `varchar` | - | - |
| `latitude` | `double`, `nullable` | - | - |
| `longitude` | `double`, `nullable` | - | - |
| `coordinates` | `array`, `nullable` | - | - |

**Relation Properties**
- `province_code` : reference `provinces`

## `districts`

| Field | Attribute | Key | Description |
| --- | --- | --- | --- |
| `code` | `char(6)` | `primary` | - |
| `regency_code` | `char(4)` | `foreign` | - |
| `province_code` | `char(2)` | `foreign` | - |
| `name` | `varchar` | - | - |

**Relation Properties**
- `regency_code` : reference `regencies`
- `province_code` : reference `provinces`

## `villages`

| Field | Attribute | Key | Description |
| --- | --- | --- | --- |
| `code` | `char(10)` | `primary` | - |
| `district_code` | `char(6)` | `foreign` | - |
| `regency_code` | `char(4)` | `foreign` | - |
| `province_code` | `char(2)` | `foreign` | - |
| `name` | `varchar` | - | - |
| `postal_code` | `char(5)`, `nullable` | - | - |

**Relation Properties**
- `district_code` : reference `districts`
- `regency_code` : reference `regencies`
- `province_code` : reference `provinces`
