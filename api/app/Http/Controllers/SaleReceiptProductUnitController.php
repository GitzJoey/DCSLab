<?php

namespace App\Http\Controllers;

use App\Actions\SaleReceiptProductUnit\SaleReceiptProductUnitActions;
use App\Http\Requests\SaleReceiptProductUnitRequest;
use App\Http\Resources\SaleReceiptProductUnitResource;
use App\Models\SaleReceiptProductUnit;
use Exception;

class SaleReceiptProductUnitController extends BaseController
{
    private $saleReceiptProductUnitActions;

    public function __construct(SaleReceiptProductUnitActions $saleReceiptProductUnitActions)
    {
        parent::__construct();

        $this->saleReceiptProductUnitActions = $saleReceiptProductUnitActions;
    }

    public function store(SaleReceiptProductUnitRequest $saleReceiptProductUnitRequest)
    {
        $request = $saleReceiptProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SaleReceiptProductUnitRequest $saleReceiptProductUnitRequest)
    {
        $request = $saleReceiptProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitActions->readAny(
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
            $response = SaleReceiptProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(SaleReceiptProductUnit $saleReceiptProductUnit, SaleReceiptProductUnitRequest $saleReceiptProductUnitRequest)
    {
        $request = $saleReceiptProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitActions->read($saleReceiptProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SaleReceiptProductUnitResource($result);

            return $response;
        }
    }

    public function update(SaleReceiptProductUnit $saleReceiptProductUnit, SaleReceiptProductUnitRequest $saleReceiptProductUnitRequest)
    {
        $request = $saleReceiptProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitActions->update(
                saleReceiptProductUnit: $saleReceiptProductUnit,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleReceiptProductUnit $saleReceiptProductUnit, SaleReceiptProductUnitRequest $saleReceiptProductUnitRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitActions->delete($saleReceiptProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
