<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReturnProductUnitSerial\PurchaseReturnProductUnitSerialActions;
use App\Http\Requests\PurchaseReturnProductUnitSerialRequest;
use App\Http\Resources\PurchaseReturnProductUnitSerialResource;
use App\Models\PurchaseReturnProductUnitSerial;
use Exception;

class PurchaseReturnProductUnitSerialController extends BaseController
{
    private $purchaseReturnProductUnitSerialActions;

    public function __construct(PurchaseReturnProductUnitSerialActions $purchaseReturnProductUnitSerialActions)
    {
        parent::__construct();

        $this->purchaseReturnProductUnitSerialActions = $purchaseReturnProductUnitSerialActions;
    }

    public function store(PurchaseReturnProductUnitSerialRequest $purchaseReturnProductUnitSerialRequest)
    {
        $request = $purchaseReturnProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitSerialActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseReturnProductUnitSerialRequest $purchaseReturnProductUnitSerialRequest)
    {
        $request = $purchaseReturnProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitSerialActions->readAny(
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
            $response = PurchaseReturnProductUnitSerialResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial, PurchaseReturnProductUnitSerialRequest $purchaseReturnProductUnitSerialRequest)
    {
        $request = $purchaseReturnProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitSerialActions->read($purchaseReturnProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseReturnProductUnitSerialResource($result);

            return $response;
        }
    }

    public function update(PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial, PurchaseReturnProductUnitSerialRequest $purchaseReturnProductUnitSerialRequest)
    {
        $request = $purchaseReturnProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitSerialActions->update(
                purchaseReturnProductUnitSerial: $purchaseReturnProductUnitSerial,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial, PurchaseReturnProductUnitSerialRequest $purchaseReturnProductUnitSerialRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitSerialActions->delete($purchaseReturnProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
