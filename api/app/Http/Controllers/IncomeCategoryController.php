<?php

namespace App\Http\Controllers;

use App\Actions\IncomeCategory\IncomeCategoryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\IncomeCategory\IncomeCategoryStoreRequest;
use App\Http\Requests\IncomeCategory\IncomeCategoryUpdateRequest;
use App\Http\Resources\IncomeCategoryResource;
use App\Models\IncomeCategory;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncomeCategoryController extends BaseController
{
    public function __construct(
        private readonly IncomeCategoryActions $incomeCategoryActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', IncomeCategory::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'parent_id' => $request->filled('parent_id') ? HashidsHelper::decodeId($request->parent_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],

            'parent_id' => ['nullable', 'integer', new ExistsForCompany('income_categories', $request->company_id)],
            'has_parent' => ['nullable', 'boolean'],
            'has_children' => ['nullable', 'boolean'],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('income_categories', $request->company_id)],

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
            $result = $this->incomeCategoryActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,

                parentId: $validatedRequest['parent_id'] ?? null,
                hasParent: $validatedRequest['has_parent'] ?? null,
                hasChildren: $validatedRequest['has_children'] ?? null,
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

        return is_null($result) ? response()->error($errorMsg) : IncomeCategoryResource::collection($result);
    }

    public function read(IncomeCategory $incomeCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $incomeCategory);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->incomeCategoryActions->read($incomeCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new IncomeCategoryResource($result);
    }

    public function store(IncomeCategoryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->incomeCategoryActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            $isUniqueName = $this->incomeCategoryActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->incomeCategoryActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(IncomeCategory $incomeCategory, IncomeCategoryUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->incomeCategoryActions->isUniqueCode(
                $incomeCategory->company_id,
                $validatedRequest['code'],
                $incomeCategory->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            $isUniqueName = $this->incomeCategoryActions->isUniqueName(
                $incomeCategory->company_id,
                $validatedRequest['name'],
                $incomeCategory->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->incomeCategoryActions->update(
                incomeCategory: $incomeCategory,
                data: [
                    'code' => $validatedRequest['code'],
                    'name' => $validatedRequest['name'],
                    'sequence' => $validatedRequest['sequence'],
                ],
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(IncomeCategory $incomeCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $incomeCategory);

        if ($this->incomeCategoryActions->hasChildren($incomeCategory)) {
            return response()->error([
                'errors' => [
                    'income_category' => [trans('rules.income_category.cannot_delete_with_children')],
                ],
                'message' => trans('rules.income_category.cannot_delete_with_children'),
            ], 422);
        }

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->incomeCategoryActions->delete($incomeCategory);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
