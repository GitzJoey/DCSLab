<?php

namespace App\Http\Controllers;

use App\Actions\Brand\BrandActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Brand\BrandStoreRequest;
use App\Http\Requests\Brand\BrandUpdateRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BrandController extends BaseController
{
    private $brandActions;

    public function __construct(BrandActions $brandActions)
    {
        parent::__construct();

        $this->brandActions = $brandActions;
    }

    public function store(BrandStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->brandActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->brandActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->brandActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('viewAny', Brand::class);

        if ($request->filled('company_id')) $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        if ($request->filled('include_id')) $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'include_id' => ['nullable', 'integer', 'exists:brands,id'],

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
            $result = $this->brandActions->readAny(
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
                    })()
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return BrandResource::collection($result);
        }
    }

    public function read(Brand $brand)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $brand);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->brandActions->read($brand);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new BrandResource($result);

            return $response;
        }
    }

    public function update(Brand $brand, BrandUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->brandActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], $brand->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->brandActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], $brand->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->brandActions->update($brand, $validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Brand $brand)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $brand);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->brandActions->delete($brand);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
