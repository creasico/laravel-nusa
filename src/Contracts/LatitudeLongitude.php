<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;


interface LatitudeLongitude
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function latitudeLongitudeable();
}


?>
