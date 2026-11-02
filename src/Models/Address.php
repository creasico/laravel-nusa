<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Address as AddressContract;
use Creasi\Nusa\RuntimeException;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @implements AddressContract<static>
 *
 * @mixin Builder
 */
final class Address extends EloquentModel implements AddressContract
{
    /** @use Concerns\WithDistrict<static> */
    use Concerns\WithDistrict;

    /** @use Concerns\WithProvince<static> */
    use Concerns\WithProvince;

    /** @use Concerns\WithRegency<static> */
    use Concerns\WithRegency;

    /** @use Concerns\WithVillage<static> */
    use Concerns\WithVillage;

    /**
     * @return array<string, mixed>
     */
    public function getCasts(): array
    {
        return array_merge(parent::getCasts(), [
            'postal_code' => 'int',
        ]);
    }

    public function getFillable()
    {
        return array_merge(parent::getFillable(), ['line', 'postal_code']);
    }

    /**
     * Associate the address with a village and persist it.
     *
     * This convenience method is specific to the default Address model and is
     * not part of the Address contract.
     *
     * @throws RuntimeException when the address cannot be saved.
     */
    public function associateWith(
        Village $village,
        ?District $district = null,
        ?Regency $regency = null,
        ?Province $province = null,
    ): self {
        $this->village()->associate($village);

        if (! $district) {
            $district = $village->district;
        }

        $this->district()->associate($district);

        if (! $regency) {
            $regency = $village->regency;
        }

        $this->regency()->associate($regency);

        if (! $province) {
            $province = $village->province;
        }

        $this->province()->associate($province);

        if (! $this->save()) {
            throw new RuntimeException(
                'The address could not be saved; a saving listener may have cancelled it.'
            );
        }

        return $this->refresh();
    }

    /**
     * @see AddressContract::addressable()
     *
     * @return MorphTo<EloquentModel, $this>
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo('addressable');
    }
}
