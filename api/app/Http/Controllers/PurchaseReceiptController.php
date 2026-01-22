<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use App\Http\Requests\PurchaseReceiptRequest;
use App\Http\Resources\PurchaseReceiptResource;
use App\Models\PurchaseReceipt;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptController extends BaseController
{
    private $purchaseReceiptActions;

    public function __construct(PurchaseReceiptActions $purchaseReceiptActions)
    {
        parent::__construct();

        $this->purchaseReceiptActions = $purchaseReceiptActions;
    }

    public function store(PurchaseReceiptRequest $purchaseReceiptRequest)
    {
        $request = $purchaseReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseReceiptRequest $purchaseReceiptRequest)
    {
        $request = $purchaseReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptActions->readAny(
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
            $response = PurchaseReceiptResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseReceipt $purchaseReceipt, PurchaseReceiptRequest $purchaseReceiptRequest)
    {
        $request = $purchaseReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptActions->read($purchaseReceipt);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseReceiptResource($result);

            return $response;
        }
    }

    public function update(PurchaseReceipt $purchaseReceipt, PurchaseReceiptRequest $purchaseReceiptRequest)
    {
        $request = $purchaseReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptActions->update(
                purchaseReceipt: $purchaseReceipt,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReceipt $purchaseReceipt, PurchaseReceiptRequest $purchaseReceiptRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseReceiptActions->delete($purchaseReceipt);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
