<?php

namespace App\Http\Controllers;

use App\Actions\StockTransferProductUnit\StockTransferProductUnitActions;
use App\DTOs\StockTransferProductUnitCreateDTO;
use App\DTOs\StockTransferProductUnitUpdateDTO;
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
            $data = new StockTransferProductUnitCreateDTO(
                companyId: $request['company_id'],
                branchId: $request['branch_id'],
                stockTransferId: $request['stock_transfer_id'],
                qty: $request['qty'],
                productUnitId: $request['product_unit_id'],
                productUnitConversionValue: $request['product_unit_conversion_value'],
                remarks: $request['remarks'] ?? null,
                serials: [],
            );
            $result = $this->stockTransferProductUnitActions->create($data);
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
            $data = new StockTransferProductUnitUpdateDTO(
                qty: $request['qty'],
                productUnitId: $request['product_unit_id'],
                productUnitConversionValue: $request['product_unit_conversion_value'],
                remarks: $request['remarks'] ?? null,
                deleteSerialIds: [],
                serials: [],
            );
            $result = $this->stockTransferProductUnitActions->update(
                stockTransferProductUnit: $stockTransferProductUnit,
                data: $data
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
