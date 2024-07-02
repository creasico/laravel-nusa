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

    public function show(NusaRequest $request, int $coordinates)
    {
        $coordinates = $this->model->findOrFail($coordinates);

        $request->relations($coordinates);

        return new NusaResource($coordinates);
    }

    public function provinces(NusaRequest $request, int $coordinates)
    {
        $coordinates = $this->model->findOrFail($coordinates);

        return NusaResource::collection(
            $request->apply($coordinates->provinces())
        );
    }

    public function districts(NusaRequest $request, int $coordinates)
    {
        $coordinates = $this->model->findOrFail($coordinates);

        return NusaResource::collection(
            $request->apply($coordinates->districts())
        );
    }

    public function villages(NusaRequest $request, int $coordinates)
    {
        $coordinates = $this->model->findOrFail($coordinates);

        return NusaResource::collection(
            $request->apply($coordinates->villages())
        );
    }

}
