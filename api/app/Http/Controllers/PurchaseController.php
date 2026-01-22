<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\PurchaseActions;
use App\Http\Requests\PurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseController extends BaseController
{
    private $purchaseActions;

    public function __construct(PurchaseActions $purchaseActions)
    {
        parent::__construct();

        $this->purchaseActions = $purchaseActions;
    }

    public function store(PurchaseRequest $purchaseRequest)
    {
        $request = $purchaseRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseRequest $purchaseRequest)
    {
        $request = $purchaseRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseActions->readAny(
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
            $response = PurchaseResource::collection($result);

            return $response;
        }
    }

    public function read(Purchase $purchase, PurchaseRequest $purchaseRequest)
    {
        $request = $purchaseRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseActions->read($purchase);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseResource($result);

            return $response;
        }
    }

    public function update(Purchase $purchase, PurchaseRequest $purchaseRequest)
    {
        $request = $purchaseRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $purchase->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseActions->update(
                purchase: $purchase,
                data: $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Purchase $purchase, PurchaseRequest $purchaseRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseActions->delete($purchase);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
