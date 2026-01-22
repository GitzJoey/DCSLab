<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrderProductUnit\PurchaseOrderProductUnitActions;
use App\Http\Requests\PurchaseOrderProductUnitRequest;
use App\Http\Resources\PurchaseOrderProductUnitResource;
use App\Models\PurchaseOrderProductUnit;
use Exception;

class PurchaseOrderProductUnitController extends BaseController
{
    private $purchaseOrderProductUnitActions;

    public function __construct(PurchaseOrderProductUnitActions $purchaseOrderProductUnitActions)
    {
        parent::__construct();

        $this->purchaseOrderProductUnitActions = $purchaseOrderProductUnitActions;
    }

    public function store(PurchaseOrderProductUnitRequest $purchaseOrderProductUnitRequest)
    {
        $request = $purchaseOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderProductUnitActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseOrderProductUnitRequest $purchaseOrderProductUnitRequest)
    {
        $request = $purchaseOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderProductUnitActions->readAny(
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
            $response = PurchaseOrderProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseOrderProductUnit $purchaseOrderProductUnit, PurchaseOrderProductUnitRequest $purchaseOrderProductUnitRequest)
    {
        $request = $purchaseOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderProductUnitActions->read($purchaseOrderProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseOrderProductUnitResource($result);

            return $response;
        }
    }

    public function update(PurchaseOrderProductUnit $purchaseOrderProductUnit, PurchaseOrderProductUnitRequest $purchaseOrderProductUnitRequest)
    {
        $request = $purchaseOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderProductUnitActions->update(
                purchaseOrderProductUnit: $purchaseOrderProductUnit,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseOrderProductUnit $purchaseOrderProductUnit, PurchaseOrderProductUnitRequest $purchaseOrderProductUnitRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderProductUnitActions->delete($purchaseOrderProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
