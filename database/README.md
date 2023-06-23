# Database Structure

```mermaid
classDiagram
    provinces "1" --> "*" regencies
    provinces "1" --> "*" districts
    provinces "1" --> "*" villages
    regencies "1" --> "*" villages
    regencies "1" --> "*" districts
    districts "1" --> "*" villages
    class provinces {
        +char~2~ code
        +varchar name
    }
    class regencies {
        +char~4~ code
        +char~2~ province_code
        +varchar name
    }
    class districts {
        +char~6~ code
        +char~4~ regency_code
        +char~2~ province_code
        +varchar name
    }
    class villages {
        +char~10~ code
        +char~6~ district_code
        +char~4~ regency_code
        +char~2~ province_code
        +varchar name
    }
```

## `provinces`

| Field | Attribute | Description |
| --- | --- | --- |
| `code` | `char(2)`, `unique`, `primary` | - |
| `name` | `string` | - |

## `regencies`

| Field | Attribute | Description |
| --- | --- | --- |
| `code` | `char(4)`, `unique`, `primary` | - |
| `province_code` | `char(2)`, `index` | - |
| `name` | `string` | - |

**Relation Properties**
- `province_code` : reference `provinces`

## `districts`

| Field | Attribute | Description |
| --- | --- | --- |
| `code` | `char(6)`, `unique`, `primary` | - |
| `regency_code` | `char(4)`, `index` | - |
| `province_code` | `char(2)`, `index` | - |
| `name` | `string` | - |

**Relation Properties**
- `regency_code` : reference `regencies`
- `province_code` : reference `provinces`

## `villages`

| Field | Attribute | Description |
| --- | --- | --- |
| `code` | `char(10)`, `unique`, `primary` | - |
| `district_code` | `char(6)`, `index` | - |
| `regency_code` | `char(4)`, `index` | - |
| `province_code` | `char(2)`, `index` | - |
| `name` | `string` | - |

**Relation Properties**
- `district_code` : reference `districts`
- `regency_code` : reference `regencies`
- `province_code` : reference `provinces`
