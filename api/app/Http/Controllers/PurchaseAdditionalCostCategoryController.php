<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseAdditionalCostCategory\PurchaseAdditionalCostCategoryActions;
use App\Http\Requests\PurchaseAdditionalCostCategoryRequest;
use App\Http\Resources\PurchaseAdditionalCostCategoryResource;
use App\Models\PurchaseAdditionalCostCategory;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseAdditionalCostCategoryController extends BaseController
{
    private $purchaseAdditionalCostCategoryActions;

    public function __construct(PurchaseAdditionalCostCategoryActions $purchaseAdditionalCostCategoryActions)
    {
        parent::__construct();

        $this->purchaseAdditionalCostCategoryActions = $purchaseAdditionalCostCategoryActions;
    }

    public function store(PurchaseAdditionalCostCategoryRequest $purchaseAdditionalCostCategoryRequest)
    {
        $request = $purchaseAdditionalCostCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseAdditionalCostCategoryActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseAdditionalCostCategoryActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseAdditionalCostCategoryRequest $purchaseAdditionalCostCategoryRequest)
    {
        $request = $purchaseAdditionalCostCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseAdditionalCostCategoryActions->readAny(
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
            $response = PurchaseAdditionalCostCategoryResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory, PurchaseAdditionalCostCategoryRequest $purchaseAdditionalCostCategoryRequest)
    {
        $request = $purchaseAdditionalCostCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseAdditionalCostCategoryActions->read($purchaseAdditionalCostCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseAdditionalCostCategoryResource($result);

            return $response;
        }
    }

    public function update(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory, PurchaseAdditionalCostCategoryRequest $purchaseAdditionalCostCategoryRequest)
    {
        $request = $purchaseAdditionalCostCategoryRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseAdditionalCostCategoryActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $purchaseAdditionalCostCategory->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUniqueName = $this->purchaseAdditionalCostCategoryActions->isUniqueName(
                $request['company_id'],
                $request['name'],
                $purchaseAdditionalCostCategory->id
            );
            if (! $isUniqueName) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $result = $this->purchaseAdditionalCostCategoryActions->update(
                purchaseAdditionalCostCategory: $purchaseAdditionalCostCategory,
                data: $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory, PurchaseAdditionalCostCategoryRequest $purchaseAdditionalCostCategoryRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseAdditionalCostCategoryActions->delete($purchaseAdditionalCostCategory);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
