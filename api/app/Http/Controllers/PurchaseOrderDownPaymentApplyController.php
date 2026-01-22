<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrderDownPaymentApply\PurchaseOrderDownPaymentApplyActions;
use App\Http\Requests\PurchaseOrderDownPaymentApplyRequest;
use App\Http\Resources\PurchaseOrderDownPaymentApplyResource;
use App\Models\PurchaseOrderDownPaymentApply;
use Exception;

class PurchaseOrderDownPaymentApplyController extends BaseController
{
    private $purchaseOrderDownPaymentApplyActions;

    public function __construct(PurchaseOrderDownPaymentApplyActions $purchaseOrderDownPaymentApplyActions)
    {
        parent::__construct();

        $this->purchaseOrderDownPaymentApplyActions = $purchaseOrderDownPaymentApplyActions;
    }

    public function store(PurchaseOrderDownPaymentApplyRequest $purchaseOrderDownPaymentApplyRequest)
    {
        $request = $purchaseOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentApplyActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseOrderDownPaymentApplyRequest $purchaseOrderDownPaymentApplyRequest)
    {
        $request = $purchaseOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentApplyActions->readAny(
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
            $response = PurchaseOrderDownPaymentApplyResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply, PurchaseOrderDownPaymentApplyRequest $purchaseOrderDownPaymentApplyRequest)
    {
        $request = $purchaseOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentApplyActions->read($purchaseOrderDownPaymentApply);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseOrderDownPaymentApplyResource($result);

            return $response;
        }
    }

    public function update(PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply, PurchaseOrderDownPaymentApplyRequest $purchaseOrderDownPaymentApplyRequest)
    {
        $request = $purchaseOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentApplyActions->update(
                purchaseOrderDownPaymentApply: $purchaseOrderDownPaymentApply,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply, PurchaseOrderDownPaymentApplyRequest $purchaseOrderDownPaymentApplyRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentApplyActions->delete($purchaseOrderDownPaymentApply);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
