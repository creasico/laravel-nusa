<?php

namespace Creasi\Nusa\Http\Requests;

use Creasi\Nusa\Contracts\Village;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Http\FormRequest;

final class NusaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'with' => ['nullable', 'array'],
            'with.*' => ['string'],
            'codes' => ['nullable', 'array'],
            'codes.*' => ['string'],
            'search' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'numeric', 'digits:5'],
            'page' => ['nullable', 'numeric'],
            'per-page' => ['nullable', 'numeric'],
        ];
    }

    /**
     * @param  \Creasi\Nusa\Models\Model  $model
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function apply($model)
    {
        $query = $model instanceof HasMany
            ? $model
            : $model->load($this->relations($model))->query();

        $result = $query
            ->when($this->filled('search'), function (Builder $query) {
                $query->search($this->query('search'));
            })
            ->when($this->filled('codes'), function (Builder $query) {
                $query->whereIn($query->getModel()->getKeyName(), $this->query('codes', []));
            })
            ->when($this->filled('postal_code') && $model instanceof Village, function (Builder $query) {
                $query->where('postal_code', $this->query('postal_code'));
            });

        return $result->paginate($this->query('per-page'));
    }

    /**
     * @param  \Creasi\Nusa\Models\Model  $model
     * @return string[]
     */
    public function relations($model): array
    {
        $relations = \array_filter(
            (array) $this->query('with', []),
            fn (string $relate) => \method_exists($model, $relate)
        );

        $model->load($relations);

        return $relations;
    }
}
