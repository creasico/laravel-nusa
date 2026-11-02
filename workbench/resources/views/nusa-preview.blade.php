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
        The form follows the application locale (the workbench runs in <code>id</code>).
        Alpine is not part of the package; the CDN tag at the bottom of this page is
        what makes the cascade interactive here.
    </p>

    <x-nusa::address-form :address="['province_code' => '33', 'regency_code' => '33.75']" />

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
    <script defer src="https://unpkg.com/alpinejs@3/dist/cdn.min.js"></script>
</body>
</html>
