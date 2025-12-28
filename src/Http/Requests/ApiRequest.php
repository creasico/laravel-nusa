<?php

declare(strict_types=1);

namespace Creasi\Nusa\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ApiRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    public function getAcceptable(): string
    {
        $accepts = $this->getAcceptableContentTypes();

        if (count($accepts) === 0) {
            return '*';
        }

        [$accept] = explode(';', $accepts[0]);

        return $accept;
    }
}
