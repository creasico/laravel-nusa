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
            'search' => ['nullable', 'string'],
        ];
    }

    public function apply(Model $model)
    {
        return $model->newQuery()
            ->when($this->has('search'), function (Builder $query) {
                $query->search($this->query('search'));
            });
    }
}
