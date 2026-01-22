<?php

namespace App\Http\Controllers;

use App\Actions\StockTransferProductUnit\StockTransferProductUnitActions;
use App\Http\Requests\StockTransferProductUnitRequest;
use App\Http\Resources\StockTransferProductUnitResource;
use App\Models\StockTransferProductUnit;
use Exception;

class StockTransferProductUnitController extends BaseController
{
    private $stockTransferProductUnitActions;

    public function __construct(StockTransferProductUnitActions $stockTransferProductUnitActions)
    {
        parent::__construct();

        $this->stockTransferProductUnitActions = $stockTransferProductUnitActions;
    }

    public function store(StockTransferProductUnitRequest $stockTransferProductUnitRequest)
    {
        $request = $stockTransferProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(StockTransferProductUnitRequest $stockTransferProductUnitRequest)
    {
        $request = $stockTransferProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitActions->readAny(
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
            $response = StockTransferProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(StockTransferProductUnit $stockTransferProductUnit, StockTransferProductUnitRequest $stockTransferProductUnitRequest)
    {
        $request = $stockTransferProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitActions->read($stockTransferProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new StockTransferProductUnitResource($result);

            return $response;
        }
    }

    public function update(StockTransferProductUnit $stockTransferProductUnit, StockTransferProductUnitRequest $stockTransferProductUnitRequest)
    {
        $request = $stockTransferProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitActions->update(
                stockTransferProductUnit: $stockTransferProductUnit,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockTransferProductUnit $stockTransferProductUnit, StockTransferProductUnitRequest $stockTransferProductUnitRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitActions->delete($stockTransferProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
