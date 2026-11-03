# Upgrading

This guide covers upgrading Laravel Nusa between releases. For the full list of changes in
each release, see the [changelog](https://github.com/creasico/laravel-nusa/blob/main/CHANGELOG.md).

## Upgrading to 0.2 from 0.1

`0.2.0` is the first breaking release. It adds the Blade/Alpine UI components, publishes
database and static assets with every release, and raises the platform requirements.

### Requirements

0.2 supports PHP 8.3+ and Laravel 12 or 13:

| Requirement | 0.1 | 0.2 |
|---|---|---|
| PHP | `^8.2` | `^8.3` |
| Laravel | `^9.0 \| ^10.0 \| ^11.0 \| ^12.0 \| ^13.0` | `^12.0 \| ^13.0` |

Upgrade PHP and Laravel first, then require the new line. A `^0.1` constraint will not
install 0.2 on its own:

```bash
composer require creasi/laravel-nusa:^0.2
```

If your application has to stay on PHP 8.2 or Laravel 9–11, stay on the 0.1 line instead:

```bash
composer require creasi/laravel-nusa:^0.1
```

### Page size validation

`per_page` is the documented parameter. In 0.1 only `per-page` was read. It still works as
an alias and there is no plan to remove it. Both are validated as integers between 1 and
200:

| Request | 0.1 | 0.2 |
|---|---|---|
| `?per-page=50` | works | works (alias) |
| `?per_page=50` | ignored, default page size | works |
| `?per-page=0` | default page size | `422` |
| `?per-page=-1` | returned every row | `422` |
| `?per-page=1.5` | silently became `1` | `422` |
| `?per-page=201` | accepted | `422` |

Replace `0` with an explicit page size, or paginate/chunk the query. If a client cannot
handle a `422`, clamp the value before sending it.

### UI components

New in 0.2: `<x-nusa::address-form>`, `<x-nusa::select>` and `<x-nusa::postal>`. They need
the JavaScript published once:

```bash
php artisan vendor:publish --tag=nusa-assets
```

Then load it before Alpine in each layout:

```blade
@nusaAssets

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
```

There is no inline fallback: an unpublished asset renders the components as plain selects
that never cascade. See [Installation](/en/guide/installation#ui-components-optional) for CSP
and asset URL notes.

Optional overrides:

- `--tag=nusa-views` publishes the component views for editing.
- `--tag=creasi-lang` publishes `lang/vendor/creasico/{en,id}/nusa.php` for labels and messages.

Nothing else changed at runtime. Existing models, routes and address tables keep working.
