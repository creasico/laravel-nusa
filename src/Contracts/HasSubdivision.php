<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface HasSubdivision
{
    /**
     * @return Collection<int, \Creasi\Nusa\Models\Model>
     */
    public function subdivisions(): Collection;
}
