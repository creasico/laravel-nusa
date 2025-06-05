<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;

final class DistrictController
{
    public function __construct(
        private District $model
    ) {
        // .
    }

    public function index(NusaRequest $request)
    {
        $request->relations($this->model);

        return NusaResource::collection($request->apply($this->model));
    }

    public function show(NusaRequest $request, string $district)
    {
        $district = $this->model->findOrFail($district);

        $request->relations($district);

        return new NusaResource($district);
    }

    public function villages(NusaRequest $request, string $district)
    {
        $district = $this->model->findOrFail($district);

        return NusaResource::collection(
            $request->apply($district->villages())
        );
    }
}
