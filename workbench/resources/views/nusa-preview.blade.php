<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nusa — Blade preview</title>
</head>
<body>
    <h1>Nusa — Blade preview</h1>

    <p>
        Add <code>&lt;x-nusa::address-form /&gt;</code> below once the composite exists.
        The block above the fold is only here to prove the harness and the data are wired.
    </p>

    <fieldset>
        <legend>Alamat</legend>

        <x-nusa::select level="province" value="33" />
        <x-nusa::select level="regency" value="33.75" />
        <x-nusa::select level="district" value="33.75.03" />
        <x-nusa::select level="village" />
    </fieldset>

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
    @endif
</body>
</html>
