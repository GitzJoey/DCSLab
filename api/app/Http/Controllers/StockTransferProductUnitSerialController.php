<?php

namespace App\Http\Controllers;

use App\Actions\StockTransferProductUnitSerial\StockTransferProductUnitSerialActions;
use App\Http\Requests\StockTransferProductUnitSerialRequest;
use App\Http\Resources\StockTransferProductUnitSerialResource;
use App\Models\StockTransferProductUnitSerial;
use Exception;

class StockTransferProductUnitSerialController extends BaseController
{
    private $stockTransferProductUnitSerialActions;

    public function __construct(StockTransferProductUnitSerialActions $stockTransferProductUnitSerialActions)
    {
        parent::__construct();

        $this->stockTransferProductUnitSerialActions = $stockTransferProductUnitSerialActions;
    }

    public function store(StockTransferProductUnitSerialRequest $stockTransferProductUnitSerialRequest)
    {
        $request = $stockTransferProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitSerialActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(StockTransferProductUnitSerialRequest $stockTransferProductUnitSerialRequest)
    {
        $request = $stockTransferProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitSerialActions->readAny(
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
            $response = StockTransferProductUnitSerialResource::collection($result);

            return $response;
        }
    }

    public function read(StockTransferProductUnitSerial $stockTransferProductUnitSerial, StockTransferProductUnitSerialRequest $stockTransferProductUnitSerialRequest)
    {
        $request = $stockTransferProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitSerialActions->read($stockTransferProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new StockTransferProductUnitSerialResource($result);

            return $response;
        }
    }

    public function update(StockTransferProductUnitSerial $stockTransferProductUnitSerial, StockTransferProductUnitSerialRequest $stockTransferProductUnitSerialRequest)
    {
        $request = $stockTransferProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitSerialActions->update(
                stockTransferProductUnitSerial: $stockTransferProductUnitSerial,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockTransferProductUnitSerial $stockTransferProductUnitSerial, StockTransferProductUnitSerialRequest $stockTransferProductUnitSerialRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitSerialActions->delete($stockTransferProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
