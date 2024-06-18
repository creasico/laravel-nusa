<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;


interface Coordinate
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Province
     */
    public function province();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function coordinateable();
}


?>
