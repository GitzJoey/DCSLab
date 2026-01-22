<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReturnAdditionalCost\PurchaseReturnAdditionalCostActions;
use App\Http\Requests\PurchaseReturnAdditionalCostRequest;
use App\Http\Resources\PurchaseReturnAdditionalCostResource;
use App\Models\PurchaseReturnAdditionalCost;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseReturnAdditionalCostController extends BaseController
{
    private $purchaseReturnAdditionalCostActions;

    public function __construct(PurchaseReturnAdditionalCostActions $purchaseReturnAdditionalCostActions)
    {
        parent::__construct();

        $this->purchaseReturnAdditionalCostActions = $purchaseReturnAdditionalCostActions;
    }

    public function store(PurchaseReturnAdditionalCostRequest $purchaseReturnAdditionalCostRequest)
    {
        $request = $purchaseReturnAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseReturnAdditionalCostActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseReturnAdditionalCostActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseReturnAdditionalCostRequest $purchaseReturnAdditionalCostRequest)
    {
        $request = $purchaseReturnAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnAdditionalCostActions->readAny(
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
            $response = PurchaseReturnAdditionalCostResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost, PurchaseReturnAdditionalCostRequest $purchaseReturnAdditionalCostRequest)
    {
        $request = $purchaseReturnAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnAdditionalCostActions->read($purchaseReturnAdditionalCost);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseReturnAdditionalCostResource($result);

            return $response;
        }
    }

    public function update(PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost, PurchaseReturnAdditionalCostRequest $purchaseReturnAdditionalCostRequest)
    {
        $request = $purchaseReturnAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseReturnAdditionalCostActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $purchaseReturnAdditionalCost->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseReturnAdditionalCostActions->update(
                purchaseReturnAdditionalCost: $purchaseReturnAdditionalCost,
                data: $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost, PurchaseReturnAdditionalCostRequest $purchaseReturnAdditionalCostRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseReturnAdditionalCostActions->delete($purchaseReturnAdditionalCost);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
