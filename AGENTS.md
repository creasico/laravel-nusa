# Laravel Nusa - Agent Guide

Laravel package (not an app) that ships Indonesian administrative data (provinces, regencies, districts, villages) in a prebuilt SQLite database. `workbench/` is the Testbench harness for development, imports, and tests; it is not part of the package.

## Setup
- Clone with submodules: `git clone --recurse-submodules ...` or `git submodule update --init --recursive`. Upstream SQL lives in `workbench/submodules/`.
- `composer install` (copies `workbench/.env.example` to `workbench/.env`), then `pnpm install` for docs and git hooks.
- Fresh data import requires Docker: `composer upstream:up`. Tests do **not** require Docker when `database/nusa.<branch>-full.sqlite` already exists.

## Branch-scoped SQLite (easy to get wrong)
- Workbench resolves the DB from the current git branch: tests use `database/nusa.<branch>-full.sqlite` (with coordinates), distribution uses `database/nusa.<branch>.sqlite`. `<branch>` is `$GIT_BRANCH` or `git rev-parse --abbrev-ref HEAD` with `/` replaced by `_`.
- On a branch without its own DB file, Workbench renames the `main` file to the branch name. To reuse `database/nusa.main-full.sqlite` while on a feature branch, prefix commands with `GIT_BRANCH=main`.
- Only `database/nusa.sqlite` is committed; `database/nusa.*.sqlite` is gitignored. Never hand-edit SQLite files or `resources/static`; regenerate them with the commands below.

## Commands
```bash
composer test                                                    # Full Pest suite
vendor/bin/testbench package:test tests/Unit/ProvinceTest.php    # Single file
vendor/bin/testbench package:test --filter=searches_provinces
composer fix                                                     # Pint (also runs on commit)
vendor/bin/pint --test                                           # Style check only
composer check                                                   # PHPStan level 7
vendor/bin/phpstan analyse                                       # PHPStan directly

composer upstream:up                                   # Docker MySQL + nusa:import --fresh
composer upstream:down                                 # Stop Docker + purge workbench
composer testbench nusa:import -- --fresh --dist       # Import + build distribution DB
composer testbench nusa:dist -- --force                # Strip coordinates into nusa.<branch>.sqlite
composer testbench nusa:stat                           # Diff distribution vs development DB
composer testbench nusa:generate-static                # CSV/JSON into resources/static (gitignored)
composer serve                                         # Build assets + dev server; preview at /blade

pnpm docs:dev | pnpm docs:build | pnpm docs:preview
```
`composer upstream [args]` forwards to `docker compose --env-file workbench/.env`. With `composer testbench ...`, command flags must come after `--`; `vendor/bin/testbench ...` takes them directly.

## Architecture
- `src/Contracts/` holds interfaces; `src/ServiceProvider` binds them to the default `src/Models/`. The address model is swappable via `config('creasi.nusa.addressable')`, so application code should type-hint contracts, not models.
- All admin models extend `src/Models/Model.php`: string `code` primary key, non-incrementing, no timestamps, connection from `creasi.nusa.connection`.
- Config is merged under `creasi.nusa.*` (publish tag `creasi-config` / `creasi-nusa-config`), so use `config('creasi.nusa.connection')`, not `config('nusa.connection')`.
- `database/migrations/0000_00_00_000000_create_nusa_tables.php` is for development/import and is export-ignored. Consumers only publish the address-table stub (`create_addresses_tables.php.stub`, tag `creasi-migrations`).
- Routes in `routes/nusa.php` use prefix `nusa` (overridable via `routes_prefix` / `routes_enable`) with two families: paginated `/nusa/provinces/...` endpoints and legacy nested `/nusa/{province}/{regency}/{district}/{village}` endpoints that negotiate JSON, CSV, or GeoJSON.
- `with=` on list endpoints only loads relations that exist (`method_exists`); `Model::scopeSearch` is driver-specific (pgsql `ilike`, mysql `lower()`, sqlite `COLLATE NOCASE`).
- `nusa:dist` removes the `coordinates` column (privacy); the shipped DB is the distribution DB.

## Testing
- Pest runs through the same `testbench package:test` command surface. `tests/Unit/` covers Eloquent behavior, `tests/Feature/` hits the HTTP endpoints, and `tests/Workbench/` covers the import/dist/stat/static commands and support classes. Every test is independent: no `depends()` chains, each test resolves its own fixtures.
- Shared fixtures live in `tests/Fixture/`. Address tests use `RefreshDatabase`; no seeding is needed for region data.
- `composer check` runs PHPStan at level 7 using `phpstan.neon`; keep it green alongside the test suite.
- Model hierarchy: `Province` → `Regency` → `District` → `Village`, with each level also exposing `districts`/`villages` directly.

## Conventions
- PHP ≥ 8.3 with `declare(strict_types=1)` in every file (Pint `declare_strict_types`).
- Hooks (installed by `pnpm install`): pre-commit runs Pint via lint-staged; commit-msg runs commitlint (`@commitlint/config-conventional`).
- Docs are bilingual: edit `resources/docs/en/` first, then mirror to `resources/docs/id/`.
- CI (`.github/workflows/`) tests PHP 8.4 against Laravel 12/13 and sqlite/mysql/pgsql. Pin any new action to a full commit SHA with a trailing version comment.

## Operational Mandates
1. **Metadata Management**: ALL AI-generated metadata (plans, specs, design docs) MUST be stored exclusively in `.agents/` (e.g. `.agents/plans/`, `.agents/specs/`). Do not use any other directory for persistent agent artifacts.
