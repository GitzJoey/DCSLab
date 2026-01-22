<?php

namespace App\Http\Controllers;

use App\Actions\StockAdjustmentCategory\StockAdjustmentCategoryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockAdjustmentCategory\StockAdjustmentCategoryStoreRequest;
use App\Http\Requests\StockAdjustmentCategory\StockAdjustmentCategoryUpdateRequest;
use App\Http\Resources\StockAdjustmentCategoryResource;
use App\Models\StockAdjustmentCategory;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentCategoryController extends BaseController
{
    private $stockAdjustmentCategoryActions;

    public function __construct(StockAdjustmentCategoryActions $stockAdjustmentCategoryActions)
    {
        parent::__construct();

        $this->stockAdjustmentCategoryActions = $stockAdjustmentCategoryActions;
    }

    public function store(StockAdjustmentCategoryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->stockAdjustmentCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->stockAdjustmentCategoryActions->create([
                'company_id' => $validatedRequest['company_id'],
                'code' => $validatedRequest['code'],
                'name' => $validatedRequest['name'],
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('viewAny', StockAdjustmentCategory::class);

        if ($request->filled('company_id')) $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        if ($request->filled('include_id')) $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'search' => ['nullable', 'string'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'include_id' => ['nullable', 'integer', 'exists:stock_adjustment_categories,id'],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:10'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentCategoryActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                search: $validatedRequest['search'] ?? null,
                companyId: $validatedRequest['company_id'],
                includeId: $validatedRequest['include_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: $validatedRequest['refresh'],
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
                    })()
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return StockAdjustmentCategoryResource::collection($result);
        }
    }

    public function read(StockAdjustmentCategory $stockAdjustmentCategory)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $stockAdjustmentCategory);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentCategoryActions->read($stockAdjustmentCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new StockAdjustmentCategoryResource($result);
        }
    }

    public function update(StockAdjustmentCategory $stockAdjustmentCategory, StockAdjustmentCategoryUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->stockAdjustmentCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $stockAdjustmentCategory->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->stockAdjustmentCategoryActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                $stockAdjustmentCategory->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->stockAdjustmentCategoryActions->update(
                stockAdjustmentCategory: $stockAdjustmentCategory,
                data: [
                    'code' => $validatedRequest['code'],
                    'name' => $validatedRequest['name'],
                ]
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockAdjustmentCategory $stockAdjustmentCategory)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $stockAdjustmentCategory);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->stockAdjustmentCategoryActions->delete($stockAdjustmentCategory);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
