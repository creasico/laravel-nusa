[![Version](https://img.shields.io/packagist/v/creasi/laravel-nusa?style=flat-square)](https://packagist.org/packages/creasi/laravel-nusa)
[![License](https://img.shields.io/github/license/creasico/laravel-nusa?style=flat-square)](https://github.com/creasico/laravel-nusa/blob/main/LICENSE)
[![Actions Status](https://img.shields.io/github/actions/workflow/status/creasico/laravel-nusa/test.yml?branch=main&style=flat-square)](https://github.com/creasico/laravel-nusa/actions)

# Creasi Nusa

Simple library aims to provide Indonesia Administrative Region Data based on [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) that easily integrated with our laravel project.

## Requirements

- PHP `>=v8.1` with `php-sqlite3` extension
- Laravel `>=10.0`

## Why?

Why don't just use existsing [laravolt/indonesia](https://github.com/laravolt/indonesia), you may ask? That packages have been around for quite sometimes and already 've been used by hundreds of people, indeed. But, we need a package that ready-to-use once its installed.

I've been using [edwardsamuel/Wilayah-Administratif-Indonesia](https://github.com/edwardsamuel/Wilayah-Administratif-Indonesia) for a while and put some contributions there, but it seems no longer maintained since 2018. More over its built for python not PHP.

That's why we choose [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) it has robust and strong database in terms of legality, but its not actually a package that can be installed as dependency. By that said, it has some work to-do.

Why PHP `>=8.1` and Laravel `>=10.0`, you may ask? Because, why not?

## Installation

```sh
composer require creasi/laravel-nusa
```

That's all

## Roadmaps

- [x] Basic Models
   - [x] Provinces
   - [x] Regencies
   - [x] Districts
   - [x] Vilages
   - [ ] Postal Codes
- [ ] Routing

## Usage

**SOON**

The database structure docucmentation please consult to [`database/README.md`](https://github.com/creasico/laravel-nusa/blob/main/database/README.md).

## Customization

By default, `nusa` will add another `database.connections` config to your project and use it as main database for all `nusa`'s models, and you can customize it anyway.

1. Publish `nusa`'s config by running the following commands

   ```sh
   ./artisan vendor:publish --tag nusa-config
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

### Notes
As of now, only `connection` name and `table` names are available to customize, also we only test it using `sqlite` driver. Let us know if you had any issue using another database drivers.

## Contributing

First thing you need to do is clone repo [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) else where on your local machine, configure and import its [`db/wilayah.sql`](https://github.com/cahyadsn/wilayah/blob/0f09b8bdd0dba880064e68a79984e13b8b2b974a/db/wilayah.sql).

By default, we use the following configurations :
- `dbname` : `cahyadsn_wilayah`
- `dbhost` : `127.0.0.1` 
- `dbuser` : `root`
- `dbpass` : `secret`

If you were using different configuration you can edit [this file](https://github.com/creasico/laravel-nusa/blob/94cd261d7726b9a5cb46cdef4aa9914522a33b4a/tests/NusaTest.php#L16-L19) but please don't commit your changes.

Once you've done with your meaningful contributions, run the following command to ensure everythings is works as expected.

```sh
composer test
```

### Notes
- **Commit Convention**: This project follows [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) using [@commitlint/config-conventional](https://github.com/conventional-changelog/commitlint/tree/master/@commitlint/config-conventional) as standart, so make sure you install its npm dependencies.
- **Code Style**: This project uses [`Laravel Pint`](https://laravel.com/docs/pint) with `laravel` preset as coding standard, so make sure you follow the rules.

## Credits
- [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah)
- [edwardsamuel/Wilayah-Administratif-Indonesia](https://github.com/edwardsamuel/Wilayah-Administratif-Indonesia)
- [laravolt/indonesia](https://github.com/laravolt/indonesia)

## License

This library is open-sourced software licensed under [MIT license](LICENSE).
