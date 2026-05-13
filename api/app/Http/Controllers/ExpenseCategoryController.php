<?php

namespace App\Http\Controllers;

use App\Actions\ExpenseCategory\ExpenseCategoryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\ExpenseCategoryCreateDTO;
use App\DTOs\ExpenseCategoryUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\ExpenseCategory\ExpenseCategoryStoreRequest;
use App\Http\Requests\ExpenseCategory\ExpenseCategoryUpdateRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryController extends BaseController
{
    public function __construct(
        private readonly ExpenseCategoryActions $expenseCategoryActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', ExpenseCategory::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'parent_id' => $request->filled('parent_id') ? HashidsHelper::decodeId($request->parent_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],

            'parent_id' => ['nullable', 'integer', new ExistsForCompany('expense_categories', $request->company_id)],
            'has_parent' => ['nullable', 'boolean'],
            'has_children' => ['nullable', 'boolean'],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('expense_categories', $request->company_id)],

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
            $result = $this->expenseCategoryActions->readAny(
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

        return is_null($result) ? response()->error($errorMsg) : ExpenseCategoryResource::collection($result);
    }

    public function read(ExpenseCategory $expenseCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $expenseCategory);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->expenseCategoryActions->read($expenseCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new ExpenseCategoryResource($result);
    }

    public function store(ExpenseCategoryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->expenseCategoryActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            $isUniqueName = $this->expenseCategoryActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $dto = new ExpenseCategoryCreateDTO(
                companyId: $validatedRequest['company_id'],
                parentId: $validatedRequest['parent_id'],
                categoryType: $validatedRequest['category_type'],
                code: $validatedRequest['code'],
                name: $validatedRequest['name'],
                sequence: $validatedRequest['sequence'],
            );
            $result = $this->expenseCategoryActions->create($dto);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(ExpenseCategory $expenseCategory, ExpenseCategoryUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->expenseCategoryActions->isUniqueCode(
                $expenseCategory->company_id,
                $validatedRequest['code'],
                $expenseCategory->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            $isUniqueName = $this->expenseCategoryActions->isUniqueName(
                $expenseCategory->company_id,
                $validatedRequest['name'],
                $expenseCategory->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->expenseCategoryActions->update(
                expenseCategory: $expenseCategory,
                data: new ExpenseCategoryUpdateDTO(
                    categoryType: $validatedRequest['category_type'],
                    code: $validatedRequest['code'],
                    name: $validatedRequest['name'],
                    sequence: $validatedRequest['sequence'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(ExpenseCategory $expenseCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $expenseCategory);

        if ($this->expenseCategoryActions->hasChildren($expenseCategory)) {
            return response()->error([
                'errors' => [
                    'expense_category' => [trans('rules.expense_category.cannot_delete_with_children')],
                ],
                'message' => trans('rules.expense_category.cannot_delete_with_children'),
            ], 422);
        }

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->expenseCategoryActions->delete($expenseCategory);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
