<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReturnAdditionalCostCategory\PurchaseReturnAdditionalCostCategoryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseReturnAdditionalCostCategory\PurchaseReturnAdditionalCostCategoryStoreRequest;
use App\Http\Requests\PurchaseReturnAdditionalCostCategory\PurchaseReturnAdditionalCostCategoryUpdateRequest;
use App\Http\Resources\PurchaseReturnAdditionalCostCategoryResource;
use App\Models\PurchaseReturnAdditionalCostCategory;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturnAdditionalCostCategoryController extends BaseController
{
    private $purchaseReturnAdditionalCostCategoryActions;

    public function __construct(PurchaseReturnAdditionalCostCategoryActions $purchaseReturnAdditionalCostCategoryActions)
    {
        parent::__construct();

        $this->purchaseReturnAdditionalCostCategoryActions = $purchaseReturnAdditionalCostCategoryActions;
    }

    public function store(PurchaseReturnAdditionalCostCategoryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseReturnAdditionalCostCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseReturnAdditionalCostCategoryActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseReturnAdditionalCostCategory::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'include_id' => ['nullable', 'integer', 'exists:purchase_return_additional_cost_categories,id'],
            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnAdditionalCostCategoryActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,
                includeId: $validatedRequest['include_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate']) ? new ExecutePaginationDTO(
                        page: $validatedRequest['paginate']['page'],
                        perPage: $validatedRequest['paginate']['per_page'],
                    ) : null,
                    get: isset($validatedRequest['get']) ? new ExecuteGetDTO(
                        limit: $validatedRequest['get']['limit'],
                    ) : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return PurchaseReturnAdditionalCostCategoryResource::collection($result);
    }

    public function read(PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseReturnAdditionalCostCategory);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnAdditionalCostCategoryActions->read($purchaseReturnAdditionalCostCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseReturnAdditionalCostCategoryResource($result);
    }

    public function update(PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory, PurchaseReturnAdditionalCostCategoryUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseReturnAdditionalCostCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $purchaseReturnAdditionalCostCategory->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $isUniqueName = $this->purchaseReturnAdditionalCostCategoryActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                $purchaseReturnAdditionalCostCategory->id
            );
            if (! $isUniqueName) {
                return response()->error(['name' => [trans('rules.unique_name')]], 422);
            }

            $result = $this->purchaseReturnAdditionalCostCategoryActions->update(
                purchaseReturnAdditionalCostCategory: $purchaseReturnAdditionalCostCategory,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseReturnAdditionalCostCategory);

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
