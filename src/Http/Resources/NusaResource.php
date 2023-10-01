<?php

namespace Creasi\Nusa\Http\Resources;

use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Model $resource
 */
final class NusaResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->additional([
            'meta' => [],
        ]);
    }

    public function toArray(Request $request): array
    {
        $arr = [
            $this->resource->getKeyName() => $this->resource->getKey(),
            'name' => $this->resource->name,
            'district_code' => $this->resource->district_code,
            'regency_code' => $this->resource->regency_code,
            'province_code' => $this->resource->province_code,
            'postal_code' => $this->when(
                $this->isVillage(),
                fn () => $this->resource->postal_code
            ),
        ];

        $with = (array) $request->query('with', []);

        if (\in_array('postal_codes', $with, true)) {
            $arr['postal_codes'] = $this->when(
                ! $this->isVillage(),
                fn () => $this->resource->postal_codes
            );
        }

        if (\in_array('coordinates', $with, true)) {
            $arr['latitude'] = $this->resource->latitude;
            $arr['longitude'] = $this->resource->longitude;
            $arr['coordinates'] = $this->resource->coordinates;
        }

        foreach ($with as $relation) {
            $arr[$relation] = $this->whenLoaded($relation);
        }

        return \array_filter($arr);
    }

    private function isVillage(): bool
    {
        return $this->resource instanceof Village;
    }
}
