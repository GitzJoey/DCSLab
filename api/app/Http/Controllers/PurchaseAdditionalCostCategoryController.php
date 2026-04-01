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
    private $purchaseAdditionalCostCategoryActions;

    public function __construct(PurchaseAdditionalCostCategoryActions $purchaseAdditionalCostCategoryActions)
    {
        parent::__construct();

        $this->purchaseAdditionalCostCategoryActions = $purchaseAdditionalCostCategoryActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseAdditionalCostCategory::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],

            'include_id' => ['nullable', 'integer', 'exists:purchase_additional_cost_categories,id'],

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
                    pagination: (function () use ($validatedRequest) {
                        $pagination = null;
                        if (isset($validatedRequest['paginate'])) {
                            $pagination = new ExecutePaginationDTO(
                                page: $validatedRequest['paginate']['page'],
                                perPage: $validatedRequest['paginate']['per_page'],
                            );
                        }

                        return $pagination;
                    })(),
                    get: (function () use ($validatedRequest) {
                        $get = null;
                        if (isset($validatedRequest['get'])) {
                            $get = new ExecuteGetDTO(
                                limit: $validatedRequest['get']['limit'],
                            );
                        }

                        return $get;
                    })(),
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return PurchaseAdditionalCostCategoryResource::collection($result);
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

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseAdditionalCostCategoryResource($result);
    }

    public function store(PurchaseAdditionalCostCategoryStoreRequest $purchaseAdditionalCostCategoryRequest)
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
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->purchaseAdditionalCostCategoryActions->isUniqueName(
                $request['company_id'],
                $request['name']
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->purchaseAdditionalCostCategoryActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory, PurchaseAdditionalCostCategoryUpdateRequest $purchaseAdditionalCostCategoryRequest)
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
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->purchaseAdditionalCostCategoryActions->isUniqueName(
                $request['company_id'],
                $request['name'],
                $purchaseAdditionalCostCategory->id
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

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

    public function delete(PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseAdditionalCostCategory);

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
