<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseAdditionalCostCategory\PurchaseAdditionalCostCategoryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseAdditionalCostCategory\PurchaseAdditionalCostCategoryStoreRequest;
use App\Http\Requests\PurchaseAdditionalCostCategory\PurchaseAdditionalCostCategoryUpdateRequest;
use App\Http\Resources\PurchaseAdditionalCostCategoryResource;
use App\Models\PurchaseAdditionalCostCategory;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseAdditionalCostCategoryController extends BaseController
{
    public function __construct(
        private readonly PurchaseAdditionalCostCategoryActions $purchaseAdditionalCostCategoryActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseAdditionalCostCategory::class);

        $request->merge([
            'company_id' => HashidsHelper::decodeId($request->company_id),
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],

            'include_id' => ['nullable', 'integer'],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseAdditionalCostCategoryActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,

                includeId: $validatedRequest['include_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate'])
                        ? new ExecutePaginationDTO(
                            page: $validatedRequest['paginate']['page'],
                            perPage: $validatedRequest['paginate']['per_page'],
                        )
                        : null,
                    get: isset($validatedRequest['get'])
                        ? new ExecuteGetDTO(limit: $validatedRequest['get']['limit'])
                        : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : PurchaseAdditionalCostCategoryResource::collection($result);
    }

    public function read(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseAdditionalCostCategory);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseAdditionalCostCategoryActions->read($purchaseAdditionalCostCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new PurchaseAdditionalCostCategoryResource($result);
    }

    public function store(PurchaseAdditionalCostCategoryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->purchaseAdditionalCostCategoryActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            $isUniqueName = $this->purchaseAdditionalCostCategoryActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->purchaseAdditionalCostCategoryActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(
        PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory,
        PurchaseAdditionalCostCategoryUpdateRequest $request
    ) {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->purchaseAdditionalCostCategoryActions->isUniqueCode(
                $purchaseAdditionalCostCategory->company_id,
                $validatedRequest['code'],
                $purchaseAdditionalCostCategory->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            $isUniqueName = $this->purchaseAdditionalCostCategoryActions->isUniqueName(
                $purchaseAdditionalCostCategory->company_id,
                $validatedRequest['name'],
                $purchaseAdditionalCostCategory->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->purchaseAdditionalCostCategoryActions->update(
                purchaseAdditionalCostCategory: $purchaseAdditionalCostCategory,
                data: $validatedRequest,
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseAdditionalCostCategory);

        $result = null;
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
