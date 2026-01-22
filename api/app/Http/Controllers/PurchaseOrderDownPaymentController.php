<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Http\Requests\PurchaseOrderDownPaymentRequest;
use App\Http\Resources\PurchaseOrderDownPaymentResource;
use App\Models\PurchaseOrderDownPayment;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseOrderDownPaymentController extends BaseController
{
    private $purchaseOrderDownPaymentActions;

    public function __construct(PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions)
    {
        parent::__construct();

        $this->purchaseOrderDownPaymentActions = $purchaseOrderDownPaymentActions;
    }

    public function store(PurchaseOrderDownPaymentRequest $purchaseOrderDownPaymentRequest)
    {
        $request = $purchaseOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseOrderDownPaymentRequest $purchaseOrderDownPaymentRequest)
    {
        $request = $purchaseOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentActions->readAny(
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
            $response = PurchaseOrderDownPaymentResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseOrderDownPayment $purchaseOrderDownPayment, PurchaseOrderDownPaymentRequest $purchaseOrderDownPaymentRequest)
    {
        $request = $purchaseOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentActions->read($purchaseOrderDownPayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseOrderDownPaymentResource($result);

            return $response;
        }
    }

    public function update(PurchaseOrderDownPayment $purchaseOrderDownPayment, PurchaseOrderDownPaymentRequest $purchaseOrderDownPaymentRequest)
    {
        $request = $purchaseOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentActions->update(
                purchaseOrderDownPayment: $purchaseOrderDownPayment,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseOrderDownPayment $purchaseOrderDownPayment, PurchaseOrderDownPaymentRequest $purchaseOrderDownPaymentRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseOrderDownPaymentActions->delete($purchaseOrderDownPayment);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
