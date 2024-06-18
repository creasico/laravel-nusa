<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\Coordinate;
use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;

final class CoordinateController
{
    public function __construct(
        private Coordinate $model
    ) {
        // .
    }

    public function index(NusaRequest $request)
    {
        $request->relations($this->model);

        return NusaResource::collection($request->apply($this->model));
    }

    public function show(NusaRequest $request, int $latitudeLongitude)
    {
        $latitudeLongitude = $this->model->findOrFail($latitudeLongitude);

        $request->relations($latitudeLongitude);

        return new NusaResource($latitudeLongitude);
    }

    public function districts(NusaRequest $request, int $latitudeLongitude)
    {
        $latitudeLongitude = $this->model->findOrFail($latitudeLongitude);

        return NusaResource::collection(
            $request->apply($latitudeLongitude->districts())
        );
    }

    public function villages(NusaRequest $request, int $latitudeLongitude)
    {
        $latitudeLongitude = $this->model->findOrFail($latitudeLongitude);

        return NusaResource::collection(
            $request->apply($latitudeLongitude->villages())
        );
    }

}
