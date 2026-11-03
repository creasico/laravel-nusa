<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nusa — Blade preview</title>
</head>
<body>
    <h1>Nusa — Blade preview</h1>

    <dl>
        <dt>Connection</dt>
        <dd><code>{{ config('creasi.nusa.connection') }}</code></dd>

        <dt>Database</dt>
        <dd><code>{{ $database }}</code></dd>

        <dt>Provinces</dt>
        <dd>{{ $provinces ?? '—' }}</dd>
    </dl>

    @if ($provinces === null)
        <p>
            <strong>This branch has no imported database.</strong> Run
            <code>composer upstream:up</code> (needs Docker), or serve against a branch
            that already has one: <code>GIT_BRANCH=&lt;branch&gt; composer testbench serve</code>.
        </p>
    @elseif ($provinces === 0)
        <p><strong>The database is empty.</strong> Import it before previewing.</p>
    @else
        <p>
            The form follows the application locale (the workbench runs in <code>id</code>).
            Alpine is not part of the package; the CDN tag at the bottom of this page is
            what makes the cascade interactive here.
        </p>

        <fieldset class="space-y-4">
            <legend class="block text-sm font-medium text-gray-700">
                {{ __('creasico::nusa.address.label') }}
            </legend>

            <x-nusa::address-form :address="$address" />
        </fieldset>

        <h2>Standalone</h2>

        <p>
            Each select works on its own. Cascading is wired by <code>parent-id</code>:
            without it a select is just a select.
        </p>

        <div data-nusa-address>
            <x-nusa::postal value="51252" />
            <x-nusa::select level="province" :value="$province_code" id="demo-province" />
            <x-nusa::select level="regency" :value="$regency_code" id="demo-regency" parent-id="demo-province" />
            <x-nusa::select level="district" :value="$district_code" id="demo-district" parent-id="demo-regency" />
            <x-nusa::select level="village" :value="$village_code" id="demo-village" parent-id="demo-district" />
        </div>

        <p>A select whose <code>parent-id</code> does not exist stays inert and says so in the console:</p>

        <x-nusa::select level="village" id="demo-orphan" parent-id="does-not-exist" />
    @endif

    @nusaAssets
    <script defer src="https://unpkg.com/alpinejs@3/dist/cdn.min.js"></script>
</body>
</html>
