<?php

namespace Creasi\Nusa\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NusaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
        ];
    }
}
