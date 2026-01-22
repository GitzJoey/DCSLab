<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseProductUnit\PurchaseProductUnitActions;
use App\Http\Requests\PurchaseProductUnitRequest;
use App\Http\Resources\PurchaseProductUnitResource;
use App\Models\PurchaseProductUnit;
use Exception;

class PurchaseProductUnitController extends BaseController
{
    private $purchaseProductUnitActions;

    public function __construct(PurchaseProductUnitActions $purchaseProductUnitActions)
    {
        parent::__construct();

        $this->purchaseProductUnitActions = $purchaseProductUnitActions;
    }

    public function store(PurchaseProductUnitRequest $purchaseProductUnitRequest)
    {
        $request = $purchaseProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseProductUnitRequest $purchaseProductUnitRequest)
    {
        $request = $purchaseProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitActions->readAny(
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
            $response = PurchaseProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseProductUnit $purchaseProductUnit, PurchaseProductUnitRequest $purchaseProductUnitRequest)
    {
        $request = $purchaseProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitActions->read($purchaseProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseProductUnitResource($result);

            return $response;
        }
    }

    public function update(PurchaseProductUnit $purchaseProductUnit, PurchaseProductUnitRequest $purchaseProductUnitRequest)
    {
        $request = $purchaseProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitActions->update(
                purchaseProductUnit: $purchaseProductUnit,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseProductUnit $purchaseProductUnit, PurchaseProductUnitRequest $purchaseProductUnitRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitActions->delete($purchaseProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
