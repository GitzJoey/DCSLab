<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReturnProductUnit\PurchaseReturnProductUnitActions;
use App\Http\Requests\PurchaseReturnProductUnitRequest;
use App\Http\Resources\PurchaseReturnProductUnitResource;
use App\Models\PurchaseReturnProductUnit;
use Exception;

class PurchaseReturnProductUnitController extends BaseController
{
    private $purchaseReturnProductUnitActions;

    public function __construct(PurchaseReturnProductUnitActions $purchaseReturnProductUnitActions)
    {
        parent::__construct();

        $this->purchaseReturnProductUnitActions = $purchaseReturnProductUnitActions;
    }

    public function store(PurchaseReturnProductUnitRequest $purchaseReturnProductUnitRequest)
    {
        $request = $purchaseReturnProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseReturnProductUnitRequest $purchaseReturnProductUnitRequest)
    {
        $request = $purchaseReturnProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitActions->readAny(
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
            $response = PurchaseReturnProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseReturnProductUnit $purchaseReturnProductUnit, PurchaseReturnProductUnitRequest $purchaseReturnProductUnitRequest)
    {
        $request = $purchaseReturnProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitActions->read($purchaseReturnProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseReturnProductUnitResource($result);

            return $response;
        }
    }

    public function update(PurchaseReturnProductUnit $purchaseReturnProductUnit, PurchaseReturnProductUnitRequest $purchaseReturnProductUnitRequest)
    {
        $request = $purchaseReturnProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitActions->update(
                purchaseReturnProductUnit: $purchaseReturnProductUnit,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReturnProductUnit $purchaseReturnProductUnit, PurchaseReturnProductUnitRequest $purchaseReturnProductUnitRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitActions->delete($purchaseReturnProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
