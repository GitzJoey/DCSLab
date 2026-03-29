<?php

namespace App\Http\Controllers;

use App\Actions\Sale\SaleActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Sale\SaleStoreRequest;
use App\Http\Requests\Sale\SaleUpdateRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends BaseController
{
    private $saleActions;

    public function __construct(SaleActions $saleActions)
    {
        parent::__construct();

        $this->saleActions = $saleActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', Sale::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer'],
            'search' => ['nullable', 'string'],
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
            $result = $this->saleActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,
                warehouseId: $validatedRequest['warehouse_id'] ?? null,
                customerId: $validatedRequest['customer_id'] ?? null,
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

        return SaleResource::collection($result);
    }

    public function read(Sale $sale)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $sale);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleActions->read($sale);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new SaleResource($result);
    }

    public function store(SaleStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->saleActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->saleActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Sale $sale, SaleUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->saleActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $sale->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->saleActions->update(
                sale: $sale,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Sale $sale)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $sale);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->saleActions->delete($sale);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
