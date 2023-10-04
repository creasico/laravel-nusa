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
            if (\in_array($relation, ['postal_codes', 'coordinates'], true)) {
                continue;
            }

            $arr[$relation] = $this->whenLoaded($relation, function () use ($relation, $with) {
                $relate = $this->resource->getRelation($relation);

                if ($relate instanceof Model) {
                    return $this->normalize($relate, $with);
                }

                return $relate->map(fn (Model $model) => $this->normalize($model, $with));
            });
        }

        return $arr;
    }

    /**
     * @param  string[]  $with
     * @return array<string, mixed>
     */
    private function normalize(Model $resource, array $with = []): array
    {
        $arr = \array_filter([
            $resource->getKeyName() => $resource->getKey(),
            'name' => $resource->name,
            'district_code' => $resource->district_code,
            'regency_code' => $resource->regency_code,
            'province_code' => $resource->province_code,
            'postal_code' => $this->isVillage($resource) ? $resource->postal_code : null,
            'latitude' => $resource->latitude,
            'longitude' => $resource->longitude,
        ]);

        if (! $this->isVillage($resource)) {
            if (\in_array('postal_codes', $with)) {
                $arr['postal_codes'] = $resource->postal_codes->toArray();
            }

            if (\in_array('coordinates', $with)) {
                $arr['coordinates'] = $resource->coordinates;
            }
        }

        return $arr;
    }

    private function isVillage(Model $resource): bool
    {
        return $resource instanceof Village;
    }
}
