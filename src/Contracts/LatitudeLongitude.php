<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;


interface LatitudeLongitude
{
    /**
     * @return float
     */
    public function latitude(): float;

    /**
     * @return float
     */
    public function longitude(): float;
}


?>
