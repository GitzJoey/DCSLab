<?php

namespace App\Http\Controllers;

use App\Actions\SaleReceipt\SaleReceiptActions;
use App\Http\Requests\SaleReceiptRequest;
use App\Http\Resources\SaleReceiptResource;
use App\Models\SaleReceipt;
use Exception;
use Illuminate\Support\Facades\DB;

class SaleReceiptController extends BaseController
{
    private $saleReceiptActions;

    public function __construct(SaleReceiptActions $saleReceiptActions)
    {
        parent::__construct();

        $this->saleReceiptActions = $saleReceiptActions;
    }

    public function store(SaleReceiptRequest $saleReceiptRequest)
    {
        $request = $saleReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(SaleReceiptRequest $saleReceiptRequest)
    {
        $request = $saleReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptActions->readAny(
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
            $response = SaleReceiptResource::collection($result);

            return $response;
        }
    }

    public function read(SaleReceipt $saleReceipt, SaleReceiptRequest $saleReceiptRequest)
    {
        $request = $saleReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptActions->read($saleReceipt);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new SaleReceiptResource($result);

            return $response;
        }
    }

    public function update(SaleReceipt $saleReceipt, SaleReceiptRequest $saleReceiptRequest)
    {
        $request = $saleReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleReceiptActions->update(
                saleReceipt: $saleReceipt,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleReceipt $saleReceipt, SaleReceiptRequest $saleReceiptRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->saleReceiptActions->delete($saleReceipt);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
