<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseAdditionalCost\PurchaseAdditionalCostActions;
use App\Http\Requests\PurchaseAdditionalCostRequest;
use App\Http\Resources\PurchaseAdditionalCostResource;
use App\Models\PurchaseAdditionalCost;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseAdditionalCostController extends BaseController
{
    private $purchaseAdditionalCostActions;

    public function __construct(PurchaseAdditionalCostActions $purchaseAdditionalCostActions)
    {
        parent::__construct();

        $this->purchaseAdditionalCostActions = $purchaseAdditionalCostActions;
    }

    public function store(PurchaseAdditionalCostRequest $purchaseAdditionalCostRequest)
    {
        $request = $purchaseAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseAdditionalCostActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseAdditionalCostActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseAdditionalCostRequest $purchaseAdditionalCostRequest)
    {
        $request = $purchaseAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseAdditionalCostActions->readAny(
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
            $response = PurchaseAdditionalCostResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseAdditionalCost $purchaseAdditionalCost, PurchaseAdditionalCostRequest $purchaseAdditionalCostRequest)
    {
        $request = $purchaseAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseAdditionalCostActions->read($purchaseAdditionalCost);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseAdditionalCostResource($result);

            return $response;
        }
    }

    public function update(PurchaseAdditionalCost $purchaseAdditionalCost, PurchaseAdditionalCostRequest $purchaseAdditionalCostRequest)
    {
        $request = $purchaseAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseAdditionalCostActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $purchaseAdditionalCost->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseAdditionalCostActions->update(
                purchaseAdditionalCost: $purchaseAdditionalCost,
                data: $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseAdditionalCost $purchaseAdditionalCost, PurchaseAdditionalCostRequest $purchaseAdditionalCostRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseAdditionalCostActions->delete($purchaseAdditionalCost);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
