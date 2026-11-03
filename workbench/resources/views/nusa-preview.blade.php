<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nusa — Blade preview</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <div class="container h-full mx-auto flex flex-col gap-4">
        <h1 class="text-xl font-bold py-4">Nusa — Blade preview</h1>

        <div class="flex flex-row gap-4">
            <div class="basis-2/3 flex flex-col">
                <div>
                    <span class="font-semibold">Connection</span>
                    <code>{{ $nusa_db }}</code>
                </div>
                <div>
                    <span class="font-semibold">Database</span>
                    <code>{{ $database }}</code>
                </div>
            </div>

            <div class="basis-1/3 flex flex-col">
                <span class="font-semibold">Provinces</span>
                <span>{{ $provinces ?? '—' }}</span>
            </div>
        </div>

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

            <div class="flex flex-row gap-4">
                <fieldset class="basis-1/3">
                    <legend class="text-sm mb-4 font-semibold text-gray-700">
                        {{ __('creasico::nusa.address.label') }}
                    </legend>

                    <x-nusa::address-form :address="$address" />
                </fieldset>

                <fieldset class="basis-1/3">
                    <legend class="text-sm mb-4 font-semibold text-gray-700">Standalone</legend>

                    <div data-nusa-address class="space-y-4">
                        <p>
                            Each select works on its own. Cascading is wired by <code>parent-id</code>:
                            without it a select is just a select.
                        </p>

                        <x-nusa::postal value="51252" />
                        <x-nusa::select level="province" :value="$province_code" id="demo-province" />
                        <x-nusa::select level="regency" :value="$regency_code" id="demo-regency" parent-id="demo-province" />
                        <x-nusa::select level="district" :value="$district_code" id="demo-district" parent-id="demo-regency" />
                        <x-nusa::select level="village" :value="$village_code" id="demo-village" parent-id="demo-district" />
                    </div>
                </fieldset>

                <fieldset class="basis-1/3">
                    <legend class="text-sm mb-4 font-semibold text-gray-700">No-parent</legend>

                    <div class="space-y-4">
                        <p>A select whose <code>parent-id</code> does not exist stays inert and says so in the console:</p>

                        <x-nusa::select level="village" id="demo-orphan" parent-id="does-not-exist" />
                    </div>
                </fieldset>
            </div>

        @endif
    </div>

    @nusaAssets

    @if ($limit = max(0, (int) request('route-limit')))
        {{-- Emulates a rate-limited API so the failure path can be seen: /blade?route-limit=1 --}}
        <script>
            (() => {
                let remaining = {{ $limit }};
                const marker = @js('/'.trim(config('creasi.nusa.routes_prefix', 'nusa'), '/').'/');
                const realFetch = window.fetch;

                window.fetch = (url, options) => {
                    if (remaining > 0 && String(url).includes(marker)) {
                        remaining--;

                        return Promise.resolve(new Response(JSON.stringify({ message: 'Too Many Attempts.' }), {
                            status: 429,
                            headers: { 'Content-Type': 'application/json', 'Retry-After': '60' },
                        }));
                    }

                    return realFetch(url, options);
                };
            })();
        </script>
    @endif

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</body>
</html>
