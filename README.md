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

We also found that [w3appdev/kodepos](https://github.com/w3appdev/kodepos) provides better database structures that can easily mapped with databases from [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) in single query.

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
- [ ] Routing

## Usage

Thankfully Laravel provides us convenience way to have some sort of "relations" regardless of the database engine. So we can have this administrative data shipped in `sqlite` data and once we install it, then all we need is use it from our project with convenience of eloquent models.

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
