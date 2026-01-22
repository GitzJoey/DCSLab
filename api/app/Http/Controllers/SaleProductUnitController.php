<?php

namespace App\Http\Controllers;

use App\Actions\SaleProductUnit\SaleProductUnitActions;
use App\Http\Requests\SaleProductUnitRequest;
use App\Http\Resources\SaleProductUnitResource;
use App\Models\SaleProductUnit;
use Exception;

class SaleProductUnitController extends BaseController
{
    private $saleProductUnitActions;

    public function __construct(SaleProductUnitActions $saleProductUnitActions)
    {
        parent::__construct();

        $this->saleProductUnitActions = $saleProductUnitActions;
    }

    public function store(SaleProductUnitRequest $saleProductUnitRequest)
    {
        $request = $saleProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SaleProductUnitRequest $saleProductUnitRequest)
    {
        $request = $saleProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitActions->readAny(
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
            $response = SaleProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(SaleProductUnit $saleProductUnit, SaleProductUnitRequest $saleProductUnitRequest)
    {
        $request = $saleProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitActions->read($saleProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SaleProductUnitResource($result);

            return $response;
        }
    }

    public function update(SaleProductUnit $saleProductUnit, SaleProductUnitRequest $saleProductUnitRequest)
    {
        $request = $saleProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitActions->update(
                saleProductUnit: $saleProductUnit,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleProductUnit $saleProductUnit, SaleProductUnitRequest $saleProductUnitRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->saleProductUnitActions->delete($saleProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
