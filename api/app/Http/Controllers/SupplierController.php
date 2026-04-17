<?php

namespace App\Http\Controllers;

use App\Actions\Supplier\SupplierActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Supplier\SupplierStoreRequest;
use App\Http\Requests\Supplier\SupplierUpdateRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends BaseController
{
    private $supplierActions;

    public function __construct(SupplierActions $supplierActions)
    {
        parent::__construct();

        $this->supplierActions = $supplierActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', Supplier::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
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
            $result = $this->supplierActions->readAny(
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
        } else {
            return SupplierResource::collection($result);
        }
    }

    public function read(Supplier $supplier)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $supplier);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->supplierActions->read($supplier);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new SupplierResource($result);
    }

    public function store(SupplierStoreRequest $request)
    {
        $validatedRequest = $request->validated();
        $validatedRequest['user_id'] = Auth::id();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->supplierActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->supplierActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                null
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->supplierActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Supplier $supplier, SupplierUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->supplierActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $supplier->id
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->supplierActions->isUniqueName(
                $validatedRequest['company_id'],
                $validatedRequest['name'],
                $supplier->id
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            DB::beginTransaction();

            $result = $this->supplierActions->update(
                supplier: $supplier,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Supplier $supplier)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $supplier);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->supplierActions->delete($supplier);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
