<?php

declare(strict_types=1);

namespace Creasi\Nusa\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * The address form: one line field, a postal code, and the four region levels.
 *
 * The initial state is rendered server-side, so a prefilled form makes no requests
 * at all; Alpine takes over only when the user changes something.
 */
final class AddressForm extends Component
{
    /**
     * Contract field => column. Override with `fields` when the Address model
     * names its columns differently.
     */
    private const FIELDS = [
        'line' => 'line',
        'postal_code' => 'postal_code',
        'province_code' => 'province_code',
        'regency_code' => 'regency_code',
        'district_code' => 'district_code',
        'village_code' => 'village_code',
    ];

    public string $id;

    /** @var array<string, string> */
    public array $fields;

    public ?string $line = null;

    public ?string $postal = null;

    /** @var array<string, ?string> */
    public array $codes = [];

    /**
     * @param  array<string, string>  $fields
     */
    public function __construct(
        mixed $address = null,
        array $fields = [],
        ?string $id = null,
    ) {
        $this->id = $id ?? 'nusa-address';
        $this->fields = array_merge(self::FIELDS, $fields);

        $this->line = $this->valueOf($address, 'line');
        $this->postal = $this->valueOf($address, 'postal_code');

        foreach (['province', 'regency', 'district', 'village'] as $level) {
            $this->codes[$level] = $this->valueOf($address, "{$level}_code");
        }
    }

    public function render(): View
    {
        return view('nusa::components.address-form');
    }

    private function valueOf(mixed $address, string $field): ?string
    {
        $value = data_get($address, $this->fields[$field] ?? $field);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
