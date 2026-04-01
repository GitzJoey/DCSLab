<?php

namespace App\Http\Controllers;

use App\Actions\NonCapitalAdditionCategory\NonCapitalAdditionCategoryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\NonCapitalAdditionCategory\NonCapitalAdditionCategoryStoreRequest;
use App\Http\Requests\NonCapitalAdditionCategory\NonCapitalAdditionCategoryUpdateRequest;
use App\Http\Resources\NonCapitalAdditionCategoryResource;
use App\Models\NonCapitalAdditionCategory;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NonCapitalAdditionCategoryController extends BaseController
{
    private $nonCapitalAdditionCategoryActions;

    public function __construct(NonCapitalAdditionCategoryActions $nonCapitalAdditionCategoryActions)
    {
        parent::__construct();

        $this->nonCapitalAdditionCategoryActions = $nonCapitalAdditionCategoryActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', NonCapitalAdditionCategory::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'include_id' => ['nullable', 'integer', 'exists:non_capital_addition_categories,id'],
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
            $result = $this->nonCapitalAdditionCategoryActions->readAny(
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

        return NonCapitalAdditionCategoryResource::collection($result);
    }

    public function read(NonCapitalAdditionCategory $nonCapitalAdditionCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $nonCapitalAdditionCategory);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->nonCapitalAdditionCategoryActions->read($nonCapitalAdditionCategory);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new NonCapitalAdditionCategoryResource($result);
    }

    public function store(NonCapitalAdditionCategoryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->nonCapitalAdditionCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->nonCapitalAdditionCategoryActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->nonCapitalAdditionCategoryActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(NonCapitalAdditionCategory $nonCapitalAdditionCategory, NonCapitalAdditionCategoryUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->nonCapitalAdditionCategoryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $nonCapitalAdditionCategory->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->nonCapitalAdditionCategoryActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                $nonCapitalAdditionCategory->id
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->nonCapitalAdditionCategoryActions->update(
                $nonCapitalAdditionCategory,
                $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(NonCapitalAdditionCategory $nonCapitalAdditionCategory)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $nonCapitalAdditionCategory);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->nonCapitalAdditionCategoryActions->delete($nonCapitalAdditionCategory);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
