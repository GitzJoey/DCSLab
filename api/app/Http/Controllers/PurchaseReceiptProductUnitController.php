<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReceiptProductUnit\PurchaseReceiptProductUnitActions;
use App\Http\Requests\PurchaseReceiptProductUnitRequest;
use App\Http\Resources\PurchaseReceiptProductUnitResource;
use App\Models\PurchaseReceiptProductUnit;
use Exception;

class PurchaseReceiptProductUnitController extends BaseController
{
    private $purchaseReceiptProductUnitActions;

    public function __construct(PurchaseReceiptProductUnitActions $purchaseReceiptProductUnitActions)
    {
        parent::__construct();

        $this->purchaseReceiptProductUnitActions = $purchaseReceiptProductUnitActions;
    }

    public function store(PurchaseReceiptProductUnitRequest $purchaseReceiptProductUnitRequest)
    {
        $request = $purchaseReceiptProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseReceiptProductUnitRequest $purchaseReceiptProductUnitRequest)
    {
        $request = $purchaseReceiptProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitActions->readAny(
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
            $response = PurchaseReceiptProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseReceiptProductUnit $purchaseReceiptProductUnit, PurchaseReceiptProductUnitRequest $purchaseReceiptProductUnitRequest)
    {
        $request = $purchaseReceiptProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitActions->read($purchaseReceiptProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseReceiptProductUnitResource($result);

            return $response;
        }
    }

    public function update(PurchaseReceiptProductUnit $purchaseReceiptProductUnit, PurchaseReceiptProductUnitRequest $purchaseReceiptProductUnitRequest)
    {
        $request = $purchaseReceiptProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitActions->update(
                purchaseReceiptProductUnit: $purchaseReceiptProductUnit,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReceiptProductUnit $purchaseReceiptProductUnit, PurchaseReceiptProductUnitRequest $purchaseReceiptProductUnitRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitActions->delete($purchaseReceiptProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
