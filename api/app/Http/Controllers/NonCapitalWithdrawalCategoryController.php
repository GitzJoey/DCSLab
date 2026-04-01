<?php

namespace App\Http\Controllers;

use App\Actions\NonCapitalWithdrawalCategory\NonCapitalWithdrawalCategoryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\NonCapitalWithdrawalCategory\NonCapitalWithdrawalCategoryStoreRequest;
use App\Http\Requests\NonCapitalWithdrawalCategory\NonCapitalWithdrawalCategoryUpdateRequest;
use App\Http\Resources\NonCapitalWithdrawalCategoryResource;
use App\Models\NonCapitalWithdrawalCategory;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NonCapitalWithdrawalCategoryController extends BaseController
{
    private $nonCapitalWithdrawalCategoryActions;

    public function __construct(NonCapitalWithdrawalCategoryActions $nonCapitalWithdrawalCategoryActions)
    {
        parent::__construct();

        $this->nonCapitalWithdrawalCategoryActions = $nonCapitalWithdrawalCategoryActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', NonCapitalWithdrawalCategory::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'include_id' => ['nullable', 'integer', 'exists:non_capital_withdrawal_categories,id'],
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
            $result = $this->nonCapitalWithdrawalCategoryActions->readAny(
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

        return NonCapitalWithdrawalCategoryResource::collection($result);
    }

    public function read(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $nonCapitalWithdrawalCategory);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->nonCapitalWithdrawalCategoryActions->read($nonCapitalWithdrawalCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new NonCapitalWithdrawalCategoryResource($result);
    }

    public function store(NonCapitalWithdrawalCategoryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->nonCapitalWithdrawalCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->nonCapitalWithdrawalCategoryActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->nonCapitalWithdrawalCategoryActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory, NonCapitalWithdrawalCategoryUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->nonCapitalWithdrawalCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $nonCapitalWithdrawalCategory->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->nonCapitalWithdrawalCategoryActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                $nonCapitalWithdrawalCategory->id
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->nonCapitalWithdrawalCategoryActions->update(
                $nonCapitalWithdrawalCategory,
                $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $nonCapitalWithdrawalCategory);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->nonCapitalWithdrawalCategoryActions->delete($nonCapitalWithdrawalCategory);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
