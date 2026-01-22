<?php

namespace App\Http\Controllers;

use App\Actions\SaleOrderProductUnit\SaleOrderProductUnitActions;
use App\Http\Requests\SaleOrderProductUnitRequest;
use App\Http\Resources\SaleOrderProductUnitResource;
use App\Models\SaleOrderProductUnit;
use Exception;

class SaleOrderProductUnitController extends BaseController
{
    private $saleOrderProductUnitActions;

    public function __construct(SaleOrderProductUnitActions $saleOrderProductUnitActions)
    {
        parent::__construct();

        $this->saleOrderProductUnitActions = $saleOrderProductUnitActions;
    }

    public function store(SaleOrderProductUnitRequest $saleOrderProductUnitRequest)
    {
        $request = $saleOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderProductUnitActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SaleOrderProductUnitRequest $saleOrderProductUnitRequest)
    {
        $request = $saleOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderProductUnitActions->readAny(
                useCache: $request['refresh'],
                withTrashed: $request['with_trashed'],

                search: $request['search'],
                companyId: $request['company_id'],

                paginate: $request['paginate'],
                page: $request['page'],
                perPage: $request['per_page'],
                limit: $request['limit'],
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = SaleOrderProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(SaleOrderProductUnit $saleOrderProductUnit, SaleOrderProductUnitRequest $saleOrderProductUnitRequest)
    {
        $request = $saleOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderProductUnitActions->read($saleOrderProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SaleOrderProductUnitResource($result);

            return $response;
        }
    }

    public function update(SaleOrderProductUnit $saleOrderProductUnit, SaleOrderProductUnitRequest $saleOrderProductUnitRequest)
    {
        $request = $saleOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderProductUnitActions->update(
                saleOrderProductUnit: $saleOrderProductUnit,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleOrderProductUnit $saleOrderProductUnit, SaleOrderProductUnitRequest $saleOrderProductUnitRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->saleOrderProductUnitActions->delete($saleOrderProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
