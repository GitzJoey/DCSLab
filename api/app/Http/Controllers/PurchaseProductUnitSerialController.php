<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseProductUnitSerial\PurchaseProductUnitSerialActions;
use App\Http\Requests\PurchaseProductUnitSerialRequest;
use App\Http\Resources\PurchaseProductUnitSerialResource;
use App\Models\PurchaseProductUnitSerial;
use Exception;

class PurchaseProductUnitSerialController extends BaseController
{
    private $purchaseProductUnitSerialActions;

    public function __construct(PurchaseProductUnitSerialActions $purchaseProductUnitSerialActions)
    {
        parent::__construct();

        $this->purchaseProductUnitSerialActions = $purchaseProductUnitSerialActions;
    }

    public function store(PurchaseProductUnitSerialRequest $purchaseProductUnitSerialRequest)
    {
        $request = $purchaseProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitSerialActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseProductUnitSerialRequest $purchaseProductUnitSerialRequest)
    {
        $request = $purchaseProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitSerialActions->readAny(
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
            $response = PurchaseProductUnitSerialResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseProductUnitSerial $purchaseProductUnitSerial, PurchaseProductUnitSerialRequest $purchaseProductUnitSerialRequest)
    {
        $request = $purchaseProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitSerialActions->read($purchaseProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseProductUnitSerialResource($result);

            return $response;
        }
    }

    public function update(PurchaseProductUnitSerial $purchaseProductUnitSerial, PurchaseProductUnitSerialRequest $purchaseProductUnitSerialRequest)
    {
        $request = $purchaseProductUnitSerialRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitSerialActions->update(
                purchaseProductUnitSerial: $purchaseProductUnitSerial,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseProductUnitSerial $purchaseProductUnitSerial, PurchaseProductUnitSerialRequest $purchaseProductUnitSerialRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitSerialActions->delete($purchaseProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
