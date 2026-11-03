<?php

declare(strict_types=1);

namespace Creasi\Nusa\Http\Requests;

use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use ReflectionMethod;
use ReflectionNamedType;

final class NusaRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
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
            'per-page' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'per_page' => ['nullable', 'numeric', 'min:1', 'max:200'],
        ];
    }

    /**
     * @param  Model|HasMany<*, *>  $model
     * @return LengthAwarePaginator<int, \Illuminate\Database\Eloquent\Model>
     */
    public function apply(Model|HasMany $model)
    {
        $related = $model instanceof HasMany ? $model->getRelated() : $model;

        $query = $model instanceof HasMany
            ? $model->getQuery()
            : $model->query();

        // `per_page` is the Laravel convention; `per-page` is kept as a deprecated alias.
        $perPage = $this->filled('per_page')
            ? $this->integer('per_page')
            : ($this->filled('per-page') ? $this->integer('per-page') : null);

        $result = $query
            ->with($this->eagerLoads($related))
            ->when($this->filled('search'), function (Builder $query) {
                $search = $this->query('search');

                if (\is_string($search)) {
                    $query->getModel()->scopeSearch($query, $search);
                }
            })
            ->when($this->filled('codes'), function (Builder $query) {
                $query->whereIn($query->getModel()->getKeyName(), $this->query('codes', []));
            })
            ->when($this->filled('postal_code') && $model instanceof Village, function (Builder $query) {
                $query->where('postal_code', $this->query('postal_code'));
            });

        return $result->paginate($perPage);
    }

    /**
     * @return string[]
     */
    public function relations(Model $model): array
    {
        $relations = $this->eagerLoads($model);

        $model->load($relations);

        return $relations;
    }

    /**
     * @return string[]
     */
    private function eagerLoads(Model $model): array
    {
        $relations = $this->relationNames($model);

        if (! \in_array('postal_codes', (array) $this->query('with', []), true)) {
            return $relations;
        }

        $eagerLoads = $relations;

        if (method_exists($model, 'distinctVillagesByPostalCodes')) {
            $eagerLoads[] = 'distinctVillagesByPostalCodes';
        }

        foreach ($relations as $relation) {
            $related = $model->{$relation}()->getRelated();

            if (method_exists($related, 'distinctVillagesByPostalCodes')) {
                $eagerLoads[] = "{$relation}.distinctVillagesByPostalCodes";
            }
        }

        return array_values(array_unique($eagerLoads));
    }

    /**
     * @return string[]
     */
    private function relationNames(Model $model): array
    {
        return array_values(array_filter(
            (array) $this->query('with', []),
            fn (string $relation) => $this->isRelation($model, $relation)
        ));
    }

    /**
     * Check the method signature without invoking arbitrary model methods.
     */
    private function isRelation(Model $model, string $relation): bool
    {
        if (! method_exists($model, $relation)) {
            return false;
        }

        $method = new ReflectionMethod($model, $relation);

        if (! $method->isPublic() || $method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
            return false;
        }

        $type = $method->getReturnType();

        return $type instanceof ReflectionNamedType
            && ! $type->isBuiltin()
            && is_a($type->getName(), Relation::class, true);
    }
}
