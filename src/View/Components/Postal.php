<?php

declare(strict_types=1);

namespace Creasi\Nusa\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

/**
 * A postal code field that fills the region levels around it.
 *
 * It looks for the container holding those selects — `for` when given, otherwise the
 * nearest ancestor marked `data-nusa-address` — so it can be placed anywhere inside it.
 */
final class Postal extends Component
{
    public string $id;

    public string $name;

    public ?string $value;

    public ?string $for;

    public string $base;

    public function __construct(
        ?string $id = null,
        ?string $name = null,
        ?string $value = null,
        ?string $for = null,
        ?string $base = null,
    ) {
        $this->id = $id ?? 'nusa-postal-'.Str::lower(Str::random(8));
        $this->name = $name ?? 'postal_code';
        $this->value = $value;
        $this->for = $for;
        $this->base = $base ?? url()->to(config('creasi.nusa.routes_prefix', 'nusa'));
    }

    public function render(): View
    {
        return view('nusa::components.postal');
    }
}
