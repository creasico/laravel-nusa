<?php

namespace Creasi\Nusa\Http\Controllers;

use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Http\Requests\NusaRequest;
use Creasi\Nusa\Http\Resources\NusaResource;

final class ProvinceController
{
    public function __construct(
        private Province $model
    ) {
        // .
    }

    public function index(NusaRequest $request)
    {
        $request->relations($this->model);

        return NusaResource::collection($request->apply($this->model));
    }

    public function show(NusaRequest $request, string $province)
    {
        $province = $this->model->findOrFail($province);

        $request->relations($province);

        return new NusaResource($province);
    }

    public function regencies(NusaRequest $request, string $province)
    {
        $province = $this->model->findOrFail($province);

        return NusaResource::collection(
            $request->apply($province->regencies())
        );
    }

    public function districts(NusaRequest $request, string $province)
    {
        $province = $this->model->findOrFail($province);

        return NusaResource::collection(
            $request->apply($province->districts())
        );
    }

    public function villages(NusaRequest $request, string $province)
    {
        $province = $this->model->findOrFail($province);

        return NusaResource::collection(
            $request->apply($province->villages())
        );
    }
}
