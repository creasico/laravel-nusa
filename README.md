[![Version](https://img.shields.io/packagist/v/creasi/laravel-nusa?style=flat-square)](https://packagist.org/packages/creasi/laravel-nusa)
[![License](https://img.shields.io/github/license/creasico/laravel-nusa?style=flat-square)](https://github.com/creasico/laravel-nusa/blob/main/LICENSE)
[![Actions Status](https://img.shields.io/github/actions/workflow/status/creasico/laravel-nusa/test.yml?branch=main&style=flat-square)](https://github.com/creasico/laravel-nusa/actions)

# Creasi Nusa

Simple library aims to provide Indonesia Administrative Region Data based including the coordinates and postal codes, that easily integrated with our laravel project.

## Requirements

- PHP `>=v8.1` with `php-sqlite3` extension
- Laravel `>=10.0`

## Why?

Why don't just use existsing [laravolt/indonesia](https://github.com/laravolt/indonesia), you may ask? That packages have been around for quite sometimes and already 've been used by hundreds of people, indeed. But, we need a package that ready-to-use once its installed.

I've been using [edwardsamuel/Wilayah-Administratif-Indonesia](https://github.com/edwardsamuel/Wilayah-Administratif-Indonesia) for a while and put some contributions there, but it seems no longer maintained since 2018. More over its built for python not PHP.

That's why we choose [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) it has robust and strong database in terms of legality, but its not actually a package that can be installed as dependency. By that said, it has some work to-do.

We also found that [w3appdev/kodepos](https://github.com/w3appdev/kodepos) provides better database structures that can easily mapped with databases from [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) in single query. Until we decided to swap it with [cahyadsn/wilayah_kodepos](https://github.com/cahyadsn/wilayah_kodepos) due to [`#41`](https://github.com/creasico/laravel-nusa/issues/41)

Our takes for the words **"easily integrated"** and **"ready-to-use once its installed"** means we shouldn't dealing with the data migration and seeding, hence Indonesia isn't a small country, right? running seeder for such amount of data can takes quite some times to proceed let alone the app seeder.

Why PHP `>=8.1` and Laravel `>=10.0`, you may ask? Because, why not!

## Installation

```sh
composer require creasi/laravel-nusa
```

That's all

## Roadmaps

- [x] Basic Models
   - [x] Provinces, includes `laltitude`, `longitude` and `coordinates`
   - [x] Regencies, includes `laltitude`, `longitude` and `coordinates`
   - [x] Districts
   - [x] Vilages, include `postal_code`
- [x] Routing

## Usage

Thankfully Laravel provides us convenience way to have some sort of "relations" regardless of the database engine. So we can have this administrative data shipped in `sqlite` data and once we install it, then all we need is use it from our project with convenience of eloquent models.

### ReSTful API

- Get all provinces

  `GET {APP_URL}/nusa/provinces`

  - **Query Params :**
  
    | Field | Type | Option | Description |
    | --- | --- | --- | --- |
    | `codes` | `numeric[]` | `optional` | Fetch only specified province code |
    | `search` | `string` | `optional` | Search province by a keyword |
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/provinces</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 33,
            "name": "Jawa Tengah",
            "latitude": -6.9934809206806,
            "longitude": 110.42024335421,
            "coordinates": [...],
            "postal_codes": [...],
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/provinces?page=1",
          "last": "http://localhost:8000/nusa/provinces?page=3",
          "prev": null,
          "next": "http://localhost:8000/nusa/provinces?page=2",
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 3,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/provinces?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": "http://localhost/nusa/provinces?page=2",
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/provinces",
          "per_page": 15,
          "to": 15,
          "total": 34
        }
      }
      ```
      </details>

- Show a province

  `GET {APP_URL}/nusa/provinces/{province}`

  - **Route Param :** `{province}` province code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/provinces/33</code></summary>

      ```jsonc
      {
        "data": {
          "code": 33,
          "name": "Jawa Tengah",
          "latitude": -6.9934809206806,
          "longitude": 110.42024335421,
          "coordinates": [...],
          "postal_codes": [...],
        },
        "meta": {}
      }
      ```
      </details>

- Get all regencies in a province

  `GET {APP_URL}/nusa/provinces/{province}/regencies`

  - **Route Param :** `{province}` province code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/provinces/33/regencies</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 3375,
            "province_code": 33,
            "name": "Kota Pekalongan",
            "latitude": -6.8969497174987,
            "longitude": 109.66208089654,
            "coordinates": [...],
            "postal_codes": [...],
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/provinces/33/regencies?page=1",
          "last": "http://localhost:8000/nusa/provinces/33/regencies?page=3",
          "prev": null,
          "next": "http://localhost:8000/nusa/provinces/33/regencies?page=2",
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 3,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/provinces/33/regencies?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": "http://localhost/nusa/provinces/33/regencies?page=2",
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/provinces/33/regencies",
          "per_page": 15,
          "to": 15,
          "total": 35
        }
      }
      ```
      </details>

- Get all districts in a province

  `GET {APP_URL}/nusa/provinces/{province}/districts`

  - **Route Param :** `{province}` province code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/provinces/33/districts</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 330101,
            "regency_code": 3301,
            "province_code": 33,
            "name": "Kedungreja",
            "postal_codes": [
              53263
            ]
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/provinces/33/districts?page=1",
          "last": "http://localhost:8000/nusa/provinces/33/districts?page=39",
          "prev": null,
          "next": "http://localhost/nusa/provinces/33/districts?page=2",
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 39,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/provinces/33/districts?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": "http://localhost/nusa/provinces/33/districts?page=2",
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/provinces/33/districts",
          "per_page": 15,
          "to": 15,
          "total": 576
        }
      }
      ```
      </details>

- Get all villages in a province

  `GET {APP_URL}/nusa/provinces/{province}/villages`

  - **Route Param :** `{province}` province code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/provinces/33/villages</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 3301012001,
            "district_code": 330101,
            "regency_code": 3301,
            "province_code": 33,
            "name": "Tambakreja",
            "postal_code": 53263,
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/provinces/33/villages?page=1",
          "last": "http://localhost:8000/nusa/provinces/33/villages?page=571",
          "prev": null,
          "next": "http://localhost:8000/nusa/provinces/33/villages?page=2",
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 571,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/provinces/33/villages?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": "http://localhost:8000/nusa/provinces/33/villages?page=2",
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/provinces/33/villages",
          "per_page": 15,
          "to": 15,
          "total": 8562
        }
      }
      ```
      </details>

- Get all regencies

  `GET {APP_URL}/nusa/regencies`

  - **Query Params :** 
  
    | Field | Type | Option | Description |
    | --- | --- | --- | --- |
    | `codes` | `numeric[]` | `optional` | Fetch only specified regency code |
    | `search` | `string` | `optional` | Search regency by a keyword |
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/regencies</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 3375,
            "province_code": 33,
            "name": "Kota Pekalongan",
            "latitude": -6.8969497174987,
            "longitude": 109.66208089654,
            "coordinates": [...],
            "postal_codes": [...],
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/regencies?page=1",
          "last": "http://localhost:8000/nusa/regencies?page=35",
          "prev": null,
          "next": "http://localhost:8000/nusa/regencies?page=2",
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 35,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/regencies?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": "http://localhost/nusa/regencies?page=2",
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/regencies",
          "per_page": 15,
          "to": 15,
          "total": 514
        }
      }
      ```
      </details>

- Show a regency

  `GET {APP_URL}/nusa/regencies/{regency}`

  - **Route Param :** `{regency}` regency code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/regencies/3375</code></summary>

      ```jsonc
      {
        "data": {
          "code": 3375,
          "province_code": 33,
          "name": "Kota Pekalongan",
          "latitude": -6.8969497174987,
          "longitude": 109.66208089654,
          "coordinates": [...],
          "postal_codes": [...],
        },
        "meta": {}
      }
      ```
      </details>

- Get all districts in a regency

  `GET {APP_URL}/nusa/regencies/{regency}/districts`

  - **Route Param :** `{regency}` regency code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/regencies/3375/districts</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 337501,
            "regency_code": 3375,
            "province_code": 33,
            "name": "Pekalongan Barat",
            "postal_codes": [
              51111
              51112
              51113
              51116
              51117
              51151
            ]
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/regencies/3375/districts?page=1",
          "last": "http://localhost:8000/nusa/regencies/3375/districts?page=1",
          "prev": null,
          "next": null,
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 1,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/regencies/3375/districts?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": null,
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/regencies/3375/districts",
          "per_page": 15,
          "to": 4,
          "total": 4
        }
      }
      ```
      </details>

- Get all villages in a regency

  `GET {APP_URL}/nusa/regencies/{regency}/villages`

  - **Route Param :** `{regency}` regency code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/regencies/3375/villages</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 3375011002,
            "district_code": 337501,
            "regency_code": 3375,
            "province_code": 33,
            "name": "Medono",
            "postal_code": 51111,
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/regencies/3375/villages?page=1",
          "last": "http://localhost:8000/nusa/regencies/3375/villages?page=2",
          "prev": null,
          "next": "http://localhost:8000/nusa/regencies/3375/villages?page=2",
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 2,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/regencies/3375/villages?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": "http://localhost:8000/nusa/regencies/3375/villages?page=2",
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/regencies/3375/villages",
          "per_page": 15,
          "to": 15,
          "total": 27
        }
      }
      ```
      </details>

- Get all districts

  `GET {APP_URL}/nusa/districts`

  - **Query Params :** 
  
    | Field | Type | Option | Description |
    | --- | --- | --- | --- |
    | `codes` | `numeric[]` | `optional` | Fetch only specified district code |
    | `search` | `string` | `optional` | Search district by a keyword |
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/districts</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 110101,
            "regency_code": 1101,
            "province_code": 11,
            "name": "Bakongan",
            "postal_codes": [
              23773
            ],
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/districts?page=1",
          "last": "http://localhost:8000/nusa/districts?page=485",
          "prev": null,
          "next": "http://localhost:8000/nusa/districts?page=2",
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 485,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/districts?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": "http://localhost:8000/nusa/districts?page=2",
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/districts",
          "per_page": 15,
          "to": 15,
          "total": 7266
        }
      }
      ```
      </details>

- Show a district

  `GET {APP_URL}/nusa/districts/{district}`

  - **Route Param :** `{district}` district code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/districts/337503</code></summary>

      ```jsonc
      {
        "data": {
          "code": 337503,
          "regency_code": 3375,
          "province_code": 33,
          "name": "Pekalongan Utara",
          "postal_codes": [
            51141
            51143
            51146
            51147
            51148
            51149
          ],
        },
        "meta": {}
      }
      ```
      </details>

- Get all villages in a district

  `GET {APP_URL}/nusa/districts/{district}/villages`

  - **Route Param :** `{district}` district code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/districts/337503/villages</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 3375031002,
            "district_code": 337503,
            "regency_code": 3375,
            "province_code": 33,
            "name": "Krapyak",
            "postal_code": 51147,
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/districts/337503/villages?page=1",
          "last": "http://localhost:8000/nusa/districts/337503/villages?page=1",
          "prev": null,
          "next": null,
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 1,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/districts/337503/villages?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": null,
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/districts/337503/villages",
          "per_page": 15,
          "to": 7,
          "total": 7
        }
      }
      ```
      </details>

- Get all villages

  `GET {APP_URL}/nusa/villages`

  - **Query Params :**
  
    | Field | Type | Option | Description |
    | --- | --- | --- | --- |
    | `codes` | `numeric[]` | `optional` | Fetch only specified village code |
    | `search` | `string` | `optional` | Search village by a keyword |
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/villages</code></summary>

      ```jsonc
      {
        "data": [
          {
            "code": 1101012001,
            "district_code": 110101,
            "regency_code": 1101,
            "province_code": 11,
            "name": "Keude Bakongan",
            "postal_code": 23773,
          },
          { ... }
        ],
        "links": {
          "first": "http://localhost:8000/nusa/villages?page=1",
          "last": "http://localhost:8000/nusa/villages?page=5565",
          "prev": null,
          "next": "http://localhost:8000/nusa/villages?page=2",
        },
        "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 5565,
          "links": [
            {
              "url": null,
              "label": "&laquo; Previous",
              "active": false,
            },
            {
              "url": "http://localhost:8000/nusa/villages?page=1",
              "label": "1",
              "active": true,
            },
            { ... },
            {
              "url": "http://localhost:8000/nusa/villages?page=2",
              "label": "Next &raquo;",
              "active": false,
            },
          ],
          "path": "http://localhost:8000/nusa/villages",
          "per_page": 15,
          "to": 15,
          "total": 83467
        }
      }
      ```
      </details>

- Get a village

  `GET {APP_URL}/nusa/villages/{village}`

  - **Route Param :** `{village}` village code
  - **Example :**
    - <details><summary><code>GET http://localhost:8000/nusa/villages/3375031006</code></summary>

      ```jsonc
      {
        "data": {
          "code": 3375031006,
          "district_code": 337503,
          "regency_code": 3375,
          "province_code": 33,
          "name": "Padukuhan Kraton",
          "postal_code": 51146,
        },
        "meta": {}
      }
      ```
      </details>

### Models

This library comes with 4 primary models as follows :

- `Creasi\Nusa\Models\Province`
- `Creasi\Nusa\Models\Regency`
- `Creasi\Nusa\Models\District`
- `Creasi\Nusa\Models\Village`

Every models comes with similar interfaces, which mean every model has `code` and `name` field in it, you can also use `search()` scope method to query model either by `code` or `name`. e.g:

```php
use Creasi\Nusa\Models\Province;

$province = Province::search(33)->first();

// or

$province = Province::search('Jawa Tengah')->first();
```

Please note that only `Province` and `Regency` that comes with `latitude`, `longitude` and `coordinates` data, while `Village` comes with `postal_code`. That's due to what [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) provide us.

In that regard we expect that `Province`, `Regency` and `District` should have access of any `postal_codes` that available within those area, and we believe that might be helpful in some cases. And there you go

```php
// Retrieve distict list of postal codes available in the province
$province->postal_codes;
```

Base on our experiences developing huge variety of products, the most use cases we need such a data is to fill up address form. But the requirement is might be vary on every single project. For that reason we also provide the bare minimun of `Address` model that use your default db connection and can easily extended to comply with project's requirement.

In that case you might wanna use our `WithAddresses` or `WithAdress` trait to your exitsting model, like so 

```php
use Creasi\Nusa\Contracts\HasAddresses;
use Creasi\Nusa\Models\Concerns\WithAddresses;

class User extends Model implements HasAddresses
{
    use WithAddresses;
}
```

To be able to use `Address` model, all you need is to publish the migration, like so

```sh
php artisan vendor:publish --tag creasi-migrations
```

Then simply run `artisan migrate` to apply the additional migrations.

### Databases

The database structure documentation please consult to [`database/README.md`](https://github.com/creasico/laravel-nusa/blob/main/database/README.md).

## Customization

By default, `nusa` will add another `database.connections` config to your project and use it as main database for all `nusa`'s models, and you can customize it anyway.

1. Publish `nusa`'s config by running the following commands

   ```sh
   ./artisan vendor:publish --tag creasi-nusa-config
   ```

2. Add new `database.connections` with key of your `creasi.nusa.connection`, say you have

   ```php
   // config/nusa.php
   return [
       'connection' => 'indonesia',
   ];
   ```
   
   So, you'll need
   ```php
   // config/database.php
   return [
       'connections' => [
           'indonesia' => [
               // ...
           ]
       ],
   ];
   ```

In term of extending `Address` model, please a look at `creasi.nusa.addressable` config if you wanna use your own implementation of `Address` model.

### Notes
As of now, only `connection` name and `table` names are available to customize, also we only test it using `sqlite` driver. Let us know if you had any issue using another database drivers.

## Contributing

1. Clone the repo and `cd` into it

   ```sh
   git clone --recurse-submodules git@github.com:creasico/laravel-nusa.git
   ```

2. Install dependencies

   ```sh
   composer install
   ```

3. Create new database, by default we use the following configuration :
   
   - `dbname` : `nusantara`
   - `dbhost` : `127.0.0.1` 
   - `dbuser` : `root`
   - `dbpass` : `secret`

   ```sh
   mysql -e 'create database nusantara;'
   ```

4. Last but not least, run `db:import` command

   ```sh
   composer db:import
   ```

If you were using different configuration you can edit [this file](https://github.com/creasico/laravel-nusa/blob/94cd261d7726b9a5cb46cdef4aa9914522a33b4a/tests/NusaTest.php#L16-L19) but please don't submit your changes.

Once you've done with your meaningful contributions, run the following command to ensure everythings is works as expected.

```sh
composer test
```

### Notes
- **Commit Convention**: This project follows [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) using [@commitlint/config-conventional](https://github.com/conventional-changelog/commitlint/tree/master/@commitlint/config-conventional) as standart, so make sure you install its npm dependencies.
- **Code Style**: This project uses [`Laravel Pint`](https://laravel.com/docs/pint) with `laravel` preset as coding standard, so make sure you follow the rules.

## Credits
- [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah)
- [w3appdev/kodepos](https://github.com/w3appdev/kodepos)
- [edwardsamuel/Wilayah-Administratif-Indonesia](https://github.com/edwardsamuel/Wilayah-Administratif-Indonesia)
- [laravolt/indonesia](https://github.com/laravolt/indonesia)

## License

This library is open-sourced software licensed under [MIT license](LICENSE).