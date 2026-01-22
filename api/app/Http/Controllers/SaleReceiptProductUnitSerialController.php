<?php

namespace App\Http\Controllers;

use App\Actions\SaleReceiptProductUnitSerial\SaleReceiptProductUnitSerialActions;
use App\Http\Requests\SaleReceiptProductUnitSerialRequest;
use App\Http\Resources\SaleReceiptProductUnitSerialResource;
use App\Models\SaleReceiptProductUnitSerial;
use Exception;

class SaleReceiptProductUnitSerialController extends BaseController
{
    private $saleReceiptProductUnitSerialActions;

    public function __construct(SaleReceiptProductUnitSerialActions $saleReceiptProductUnitSerialActions)
    {
        parent::__construct();

        $this->saleReceiptProductUnitSerialActions = $saleReceiptProductUnitSerialActions;
    }

    public function store(SaleReceiptProductUnitSerialRequest $saleReceiptProductUnitSerialRequest)
    {
        $request = $saleReceiptProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitSerialActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SaleReceiptProductUnitSerialRequest $saleReceiptProductUnitSerialRequest)
    {
        $request = $saleReceiptProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitSerialActions->readAny(
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
            $response = SaleReceiptProductUnitSerialResource::collection($result);

            return $response;
        }
    }

    public function read(SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial, SaleReceiptProductUnitSerialRequest $saleReceiptProductUnitSerialRequest)
    {
        $request = $saleReceiptProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitSerialActions->read($saleReceiptProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SaleReceiptProductUnitSerialResource($result);

            return $response;
        }
    }

    public function update(SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial, SaleReceiptProductUnitSerialRequest $saleReceiptProductUnitSerialRequest)
    {
        $request = $saleReceiptProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitSerialActions->update(
                saleReceiptProductUnitSerial: $saleReceiptProductUnitSerial,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial, SaleReceiptProductUnitSerialRequest $saleReceiptProductUnitSerialRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptProductUnitSerialActions->delete($saleReceiptProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
