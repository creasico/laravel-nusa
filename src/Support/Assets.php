<?php

declare(strict_types=1);

namespace Creasi\Nusa\Support;

final class Assets
{
    /**
     * The `@nusaAssets` output.
     *
     * Resolved at render time on purpose: the file is read per request when it is not
     * published, so editing it never leaves a stale copy in the compiled views.
     */
    public static function script(): string
    {
        if (is_file($published = public_path('vendor/nusa/nusa.js'))) {
            return '<script src="'.e(asset('vendor/nusa/nusa.js')).'" defer></script>';
        }

        return '<script>'.file_get_contents(self::source()).'</script>';
    }

    public static function source(): string
    {
        return \dirname(__DIR__, 2).'/resources/js/nusa.js';
    }
}
