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

    public function show(NusaRequest $request, int $province)
    {
        $province = $this->model->find($province);

        $request->relations($province);

        return new NusaResource($province);
    }

    public function regencies(int $province)
    {
        $province = $this->model->findOrFail($province);

        return NusaResource::collection($province->regencies()->paginate());
    }

    public function districts(int $province)
    {
        $province = $this->model->findOrFail($province);

        return NusaResource::collection($province->districts()->paginate());
    }

    public function villages(int $province)
    {
        $province = $this->model->findOrFail($province);

        return NusaResource::collection($province->villages()->paginate());
    }
}
