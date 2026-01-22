<?php

namespace App\Http\Controllers;

use App\Actions\PurchasePayment\PurchasePaymentActions;
use App\Http\Requests\PurchasePaymentRequest;
use App\Http\Resources\PurchasePaymentResource;
use App\Models\PurchasePayment;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends BaseController
{
    private $purchasePaymentActions;

    public function __construct(PurchasePaymentActions $purchasePaymentActions)
    {
        parent::__construct();

        $this->purchasePaymentActions = $purchasePaymentActions;
    }

    public function store(PurchasePaymentRequest $purchasePaymentRequest)
    {
        $request = $purchasePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchasePaymentActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchasePaymentRequest $purchasePaymentRequest)
    {
        $request = $purchasePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchasePaymentActions->readAny(
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
            $response = PurchasePaymentResource::collection($result);

            return $response;
        }
    }

    public function read(PurchasePayment $purchasePayment, PurchasePaymentRequest $purchasePaymentRequest)
    {
        $request = $purchasePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchasePaymentActions->read($purchasePayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchasePaymentResource($result);

            return $response;
        }
    }

    public function update(PurchasePayment $purchasePayment, PurchasePaymentRequest $purchasePaymentRequest)
    {
        $request = $purchasePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchasePaymentActions->update(
                purchasePayment: $purchasePayment,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchasePayment $purchasePayment, PurchasePaymentRequest $purchasePaymentRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchasePaymentActions->delete($purchasePayment);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
