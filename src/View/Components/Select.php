<?php

declare(strict_types=1);

namespace Creasi\Nusa\View\Components;

use Creasi\Nusa\Contracts;
use Creasi\Nusa\Models\Model;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

/**
 * A single level of the administrative cascade.
 *
 * Options are resolved server-side: no level ever exceeds 108 rows, so each render
 * costs one query and the browser needs no request until the user changes a parent.
 */
final class Select extends Component
{
    public string $name;

    public string $id;

    public ?string $parent;

    public bool $disabled;

    /** @var array<int, array{code: string, name: string, postal?: string}> */
    public array $options = [];

    public string $label;

    public string $placeholder;

    public string $autocomplete;

    public ?string $hint = null;

    public function __construct(
        public string $level,
        public ?string $value = null,
        ?string $parent = null,
        ?string $name = null,
        ?string $id = null,
    ) {
        $this->name = $name ?? "{$level}_code";
        $this->id = $id ?? "nusa-{$level}";
        $this->parent = $parent ?? $this->parentFromValue();
        $this->disabled = $level !== 'province' && blank($this->parent);
        $this->label = $this->trans("{$level}_code.label");
        $this->placeholder = $this->trans("{$level}_code.placeholder");
        $this->autocomplete = match ($level) {
            'province' => 'address-level1',
            'regency' => 'address-level2',
            'district' => 'address-level3',
            'village' => 'address-level4',
            default => throw new InvalidArgumentException("Unknown level [{$level}]."),
        };

        if ($level !== 'province') {
            $this->hint = $this->trans("{$level}_code.hint");
        }

        if (! $this->disabled) {
            $this->options = $this->resolveOptions();
        }
    }

    public function render(): View
    {
        return view('nusa::components.select');
    }

    /**
     * Codes carry their ancestry in their prefix: `11` → `11.01` → `11.01.01` → `11.01.01.2001`.
     */
    private function parentFromValue(): ?string
    {
        if (blank($this->value)) {
            return null;
        }

        return match ($this->level) {
            'regency' => substr($this->value, 0, 2),
            'district' => substr($this->value, 0, 5),
            'village' => substr($this->value, 0, 8),
            default => null,
        };
    }

    /** @return array<int, array{code: string, name: string, postal?: string}> */
    private function resolveOptions(): array
    {
        $query = $this->model()->newQuery()->orderBy('name');
        $columns = ['code', 'name'];

        if ($column = $this->parentColumn()) {
            $query->where($column, $this->parent);
        }

        if ($this->level === 'village') {
            $columns[] = 'postal_code';
        }

        return $query->get($columns)
            ->map(fn ($row): array => array_filter([
                'code' => (string) $row->code,
                'name' => (string) $row->name,
                'postal' => isset($row->postal_code) ? (string) $row->postal_code : null,
            ], fn ($value) => $value !== null))
            ->values()
            ->all();
    }

    /**
     * `__()` is typed as `array|string`, but a string key always resolves to a string.
     */
    private function trans(string $key): string
    {
        $value = __("creasico::nusa.{$key}");

        return \is_string($value) ? $value : $key;
    }

    private function parentColumn(): ?string
    {
        return match ($this->level) {
            'regency' => 'province_code',
            'district' => 'regency_code',
            'village' => 'district_code',
            default => null,
        };
    }

    private function model(): Model
    {
        /** @var Model $model */
        $model = app(match ($this->level) {
            'province' => Contracts\Province::class,
            'regency' => Contracts\Regency::class,
            'district' => Contracts\District::class,
            'village' => Contracts\Village::class,
            default => throw new InvalidArgumentException("Unknown level [{$this->level}]."),
        });

        return $model;
    }
}
