<?php

namespace App\Http\Controllers;

use App\Actions\SalePayment\SalePaymentActions;
use App\Http\Requests\SalePaymentRequest;
use App\Http\Resources\SalePaymentResource;
use App\Models\SalePayment;
use Exception;
use Illuminate\Support\Facades\DB;

class SalePaymentController extends BaseController
{
    private $salePaymentActions;

    public function __construct(SalePaymentActions $salePaymentActions)
    {
        parent::__construct();

        $this->salePaymentActions = $salePaymentActions;
    }

    public function store(SalePaymentRequest $salePaymentRequest)
    {
        $request = $salePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salePaymentActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SalePaymentRequest $salePaymentRequest)
    {
        $request = $salePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salePaymentActions->readAny(
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
            $response = SalePaymentResource::collection($result);

            return $response;
        }
    }

    public function read(SalePayment $salePayment, SalePaymentRequest $salePaymentRequest)
    {
        $request = $salePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salePaymentActions->read($salePayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SalePaymentResource($result);

            return $response;
        }
    }

    public function update(SalePayment $salePayment, SalePaymentRequest $salePaymentRequest)
    {
        $request = $salePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salePaymentActions->update(
                salePayment: $salePayment,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SalePayment $salePayment, SalePaymentRequest $salePaymentRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->salePaymentActions->delete($salePayment);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
