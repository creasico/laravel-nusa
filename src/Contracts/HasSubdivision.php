<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TChild of Model
 *
 * @mixin TChild
 */
interface HasSubdivision
{
    /**
     * @return Collection<int, TChild>
     */
    public function subdivisions(): Collection;
}
