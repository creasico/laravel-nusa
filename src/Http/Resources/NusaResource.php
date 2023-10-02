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
        $with = $request->query('with', []);
        $arr = $this->normalize($this->resource, $with);

        foreach ($with as $relation) {
            $arr[$relation] = $this->whenLoaded($relation, function () use ($relation, $with) {
                $relate = $this->resource->$relation;

                if ($relate instanceof Model) {
                    return $this->normalize($relate, $with);
                }

                return $relate->map(fn (Model $model) => $this->normalize($model, $with));
            });
        }

        return \array_filter($arr);
    }

    private function normalize(Model $resource, array $with = []): array
    {
        $arr = [
            $resource->getKeyName() => $resource->getKey(),
            'name' => $resource->name,
            'district_code' => $resource->district_code,
            'regency_code' => $resource->regency_code,
            'province_code' => $resource->province_code,
            'postal_code' => $this->when(
                $this->isVillage($resource),
                fn () => $resource->postal_code
            ),
        ];

        if (\in_array('postal_codes', $with, true)) {
            $arr['postal_codes'] = $this->when(
                ! $this->isVillage($resource),
                fn () => $resource->postal_codes
            );
        }

        if (\in_array('coordinates', $with, true)) {
            $arr['latitude'] = $resource->latitude;
            $arr['longitude'] = $resource->longitude;
            $arr['coordinates'] = $resource->coordinates;
        }

        return \array_filter($arr);
    }

    private function isVillage(Model $resource): bool
    {
        return $resource instanceof Village;
    }
}
