<?php

declare(strict_types=1);

namespace Creasi\Nusa\View;

final class Assets
{
    /**
     * The `@nusaAssets` output.
     *
     * Always a file, never inline: an inline script would need `script-src 'unsafe-inline'`
     * or a nonce, and a policy that blocks it fails silently — the components render and
     * simply never initialise.
     *
     * The URL goes through `asset()`, so `ASSET_URL` and any custom base URL apply. When
     * that points at another origin, the host needs its own `script-src` entry.
     */
    public static function script(): string
    {
        return '<script src="'.e(asset('vendor/nusa/nusa.js')).'"></script>';
    }
}
