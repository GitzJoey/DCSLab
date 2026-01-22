<?php

namespace App\Http\Controllers;

use App\Actions\SaleOrderDownPayment\SaleOrderDownPaymentActions;
use App\Http\Requests\SaleOrderDownPaymentRequest;
use App\Http\Resources\SaleOrderDownPaymentResource;
use App\Models\SaleOrderDownPayment;
use Exception;
use Illuminate\Support\Facades\DB;

class SaleOrderDownPaymentController extends BaseController
{
    private $saleOrderDownPaymentActions;

    public function __construct(SaleOrderDownPaymentActions $saleOrderDownPaymentActions)
    {
        parent::__construct();

        $this->saleOrderDownPaymentActions = $saleOrderDownPaymentActions;
    }

    public function store(SaleOrderDownPaymentRequest $saleOrderDownPaymentRequest)
    {
        $request = $saleOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SaleOrderDownPaymentRequest $saleOrderDownPaymentRequest)
    {
        $request = $saleOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentActions->readAny(
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
            $response = SaleOrderDownPaymentResource::collection($result);

            return $response;
        }
    }

    public function read(SaleOrderDownPayment $saleOrderDownPayment, SaleOrderDownPaymentRequest $saleOrderDownPaymentRequest)
    {
        $request = $saleOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentActions->read($saleOrderDownPayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SaleOrderDownPaymentResource($result);

            return $response;
        }
    }

    public function update(SaleOrderDownPayment $saleOrderDownPayment, SaleOrderDownPaymentRequest $saleOrderDownPaymentRequest)
    {
        $request = $saleOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentActions->update(
                saleOrderDownPayment: $saleOrderDownPayment,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleOrderDownPayment $saleOrderDownPayment, SaleOrderDownPaymentRequest $saleOrderDownPaymentRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->saleOrderDownPaymentActions->delete($saleOrderDownPayment);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
