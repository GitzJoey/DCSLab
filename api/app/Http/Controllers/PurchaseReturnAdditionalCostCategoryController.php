<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReturnAdditionalCostCategory\PurchaseReturnAdditionalCostCategoryActions;
use App\Http\Requests\PurchaseReturnAdditionalCostCategoryRequest;
use App\Http\Resources\PurchaseReturnAdditionalCostCategoryResource;
use App\Models\PurchaseReturnAdditionalCostCategory;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseReturnAdditionalCostCategoryController extends BaseController
{
    private $purchaseReturnAdditionalCostCategoryActions;

    public function __construct(PurchaseReturnAdditionalCostCategoryActions $purchaseReturnAdditionalCostCategoryActions)
    {
        parent::__construct();

        $this->purchaseReturnAdditionalCostCategoryActions = $purchaseReturnAdditionalCostCategoryActions;
    }

    public function store(PurchaseReturnAdditionalCostCategoryRequest $purchaseReturnAdditionalCostCategoryRequest)
    {
        $request = $purchaseReturnAdditionalCostCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseReturnAdditionalCostCategoryActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseReturnAdditionalCostCategoryActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseReturnAdditionalCostCategoryRequest $purchaseReturnAdditionalCostCategoryRequest)
    {
        $request = $purchaseReturnAdditionalCostCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnAdditionalCostCategoryActions->readAny(
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
            $response = PurchaseReturnAdditionalCostCategoryResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory, PurchaseReturnAdditionalCostCategoryRequest $purchaseReturnAdditionalCostCategoryRequest)
    {
        $request = $purchaseReturnAdditionalCostCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnAdditionalCostCategoryActions->read($purchaseReturnAdditionalCostCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseReturnAdditionalCostCategoryResource($result);

            return $response;
        }
    }

    public function update(PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory, PurchaseReturnAdditionalCostCategoryRequest $purchaseReturnAdditionalCostCategoryRequest)
    {
        $request = $purchaseReturnAdditionalCostCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseReturnAdditionalCostCategoryActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $purchaseReturnAdditionalCostCategory->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUniqueName = $this->purchaseReturnAdditionalCostCategoryActions->isUniqueName(
                $request['company_id'],
                $request['name'],
                $purchaseReturnAdditionalCostCategory->id
            );
            if (! $isUniqueName) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $result = $this->purchaseReturnAdditionalCostCategoryActions->update(
                purchaseReturnAdditionalCostCategory: $purchaseReturnAdditionalCostCategory,
                data: $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory, PurchaseReturnAdditionalCostCategoryRequest $purchaseReturnAdditionalCostCategoryRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseReturnAdditionalCostCategoryActions->delete($purchaseReturnAdditionalCostCategory);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
