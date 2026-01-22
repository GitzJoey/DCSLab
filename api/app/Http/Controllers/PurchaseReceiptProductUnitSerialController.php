<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReceiptProductUnitSerial\PurchaseReceiptProductUnitSerialActions;
use App\Http\Requests\PurchaseReceiptProductUnitSerialRequest;
use App\Http\Resources\PurchaseReceiptProductUnitSerialResource;
use App\Models\PurchaseReceiptProductUnitSerial;
use Exception;

class PurchaseReceiptProductUnitSerialController extends BaseController
{
    private $purchaseReceiptProductUnitSerialActions;

    public function __construct(PurchaseReceiptProductUnitSerialActions $purchaseReceiptProductUnitSerialActions)
    {
        parent::__construct();

        $this->purchaseReceiptProductUnitSerialActions = $purchaseReceiptProductUnitSerialActions;
    }

    public function store(PurchaseReceiptProductUnitSerialRequest $purchaseReceiptProductUnitSerialRequest)
    {
        $request = $purchaseReceiptProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitSerialActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseReceiptProductUnitSerialRequest $purchaseReceiptProductUnitSerialRequest)
    {
        $request = $purchaseReceiptProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitSerialActions->readAny(
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
            $response = PurchaseReceiptProductUnitSerialResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial, PurchaseReceiptProductUnitSerialRequest $purchaseReceiptProductUnitSerialRequest)
    {
        $request = $purchaseReceiptProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitSerialActions->read($purchaseReceiptProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseReceiptProductUnitSerialResource($result);

            return $response;
        }
    }

    public function update(PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial, PurchaseReceiptProductUnitSerialRequest $purchaseReceiptProductUnitSerialRequest)
    {
        $request = $purchaseReceiptProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitSerialActions->update(
                purchaseReceiptProductUnitSerial: $purchaseReceiptProductUnitSerial,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial, PurchaseReceiptProductUnitSerialRequest $purchaseReceiptProductUnitSerialRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitSerialActions->delete($purchaseReceiptProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
