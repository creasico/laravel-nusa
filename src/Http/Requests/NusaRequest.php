<?php

namespace Creasi\Nusa\Http\Requests;

use Creasi\Nusa\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;

class NusaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'with' => ['nullable', 'array'],
            'with.*' => ['string'],
            'codes' => ['nullable', 'array'],
            'codes.*' => ['numeric'],
            'search' => ['nullable', 'string'],
            'page' => ['nullable', 'numeric'],
            'per-page' => ['nullable', 'numeric'],
        ];
    }

    public function apply(Model $model)
    {
        $result = $model->load($this->relations($model))->query()
            ->when($this->has('search'), function (Builder $query) {
                $query->search($this->query('search'));
            })
            ->when($this->has('codes'), function (Builder $query) use ($model) {
                $query->whereIn($model->getKeyName(), (array) $this->query('codes'));
            });

        return $result->paginate($this->query('per-page'));
    }

    /**
     * @return string[]
     */
    public function relations(Model $model): array
    {
        return \array_filter((array) $this->query('with', []), fn (string $relate) => \method_exists($model, $relate));
    }
}
