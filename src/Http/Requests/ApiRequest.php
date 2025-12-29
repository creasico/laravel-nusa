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

    /**
     * Retrieve the acceptable format.
     *
     * @return 'application/json'|'application/geo+json'|'text/csv'|string
     */
    public function getAcceptable(): string
    {
        $accepts = $this->getAcceptableContentTypes();

        if (count($accepts) === 0) {
            // @codeCoverageIgnoreStart
            return 'application/json';
            // @codeCoverageIgnoreEnd
        }

        [$accept] = explode(';', $accepts[0]);

        $accept = trim($accept);

        if ($accept === '*/*' || $accept === '*') {
            return 'application/json';
        }

        return $accept;
    }

    /**
     * Retrieve the model key from route parameter.
     */
    public function code(string $separator = '.'): string
    {
        return implode($separator, array_filter([
            $this->route('province'),
            $this->route('regency'),
            $this->route('district'),
            $this->route('village'),
        ]));
    }
}
