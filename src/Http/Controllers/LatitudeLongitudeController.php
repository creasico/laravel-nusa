<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\LatitudeLongitude;
use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;

final class LatitudeLongitudeController
{
    public function __construct(
        private LatitudeLongitude $model
    ) {
        // .
    }

    public function index(NusaRequest $request)
    {
        $request->relations($this->model);

        return NusaResource::collection($request->apply($this->model));
    }
}




?>
