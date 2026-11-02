<?php

declare(strict_types=1);

namespace Creasi\Nusa\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ApiRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
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

        if (\count($accepts) === 0) {
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
        $codes = array_filter(array_map(
            fn ($route) => \is_string($route) ? $route : null,
            [
                $this->route('province'),
                $this->route('regency'),
                $this->route('district'),
                $this->route('village'),
            ]
        ));

        return implode($separator, $codes);
    }
}
