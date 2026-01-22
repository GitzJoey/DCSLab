<?php

namespace App\Http\Controllers;

use App\Actions\SaleOrderDownPaymentApply\SaleOrderDownPaymentApplyActions;
use App\Http\Requests\SaleOrderDownPaymentApplyRequest;
use App\Http\Resources\SaleOrderDownPaymentApplyResource;
use App\Models\SaleOrderDownPaymentApply;
use Exception;

class SaleOrderDownPaymentApplyController extends BaseController
{
    private $saleOrderDownPaymentApplyActions;

    public function __construct(SaleOrderDownPaymentApplyActions $saleOrderDownPaymentApplyActions)
    {
        parent::__construct();

        $this->saleOrderDownPaymentApplyActions = $saleOrderDownPaymentApplyActions;
    }

    public function store(SaleOrderDownPaymentApplyRequest $saleOrderDownPaymentApplyRequest)
    {
        $request = $saleOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentApplyActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SaleOrderDownPaymentApplyRequest $saleOrderDownPaymentApplyRequest)
    {
        $request = $saleOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentApplyActions->readAny(
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
            $response = SaleOrderDownPaymentApplyResource::collection($result);

            return $response;
        }
    }

    public function read(SaleOrderDownPaymentApply $saleOrderDownPaymentApply, SaleOrderDownPaymentApplyRequest $saleOrderDownPaymentApplyRequest)
    {
        $request = $saleOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentApplyActions->read($saleOrderDownPaymentApply);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SaleOrderDownPaymentApplyResource($result);

            return $response;
        }
    }

    public function update(SaleOrderDownPaymentApply $saleOrderDownPaymentApply, SaleOrderDownPaymentApplyRequest $saleOrderDownPaymentApplyRequest)
    {
        $request = $saleOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentApplyActions->update(
                saleOrderDownPaymentApply: $saleOrderDownPaymentApply,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleOrderDownPaymentApply $saleOrderDownPaymentApply, SaleOrderDownPaymentApplyRequest $saleOrderDownPaymentApplyRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentApplyActions->delete($saleOrderDownPaymentApply);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
