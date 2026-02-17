<?php

namespace App\Http\Controllers;

use App\Actions\StockAdjustmentOutProduct\StockAdjustmentOutProductActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\StockAdjustmentOutProductCreateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockAdjustmentOutProduct\StockAdjustmentOutProductStoreRequest;
use App\Http\Requests\StockAdjustmentOutProduct\StockAdjustmentOutProductUpdateRequest;
use App\Http\Resources\StockAdjustmentOutProductResource;
use App\Models\StockAdjustmentOutProduct;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentOutProductController extends BaseController
{
    private $stockAdjustmentOutProductActions;

    public function __construct(StockAdjustmentOutProductActions $stockAdjustmentOutProductActions)
    {
        parent::__construct();
        $this->stockAdjustmentOutProductActions = $stockAdjustmentOutProductActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', StockAdjustmentOutProduct::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'stock_adjustment_id' => $request->filled('stock_adjustment_id') ? HashidsHelper::decodeId($request->stock_adjustment_id) : null,
        ]);

        $validated = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],
            'stock_adjustment_id' => ['nullable', 'integer', new ExistsForCompany('stock_adjustments', $request->company_id)],
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
            $result = $this->stockAdjustmentOutProductActions->readAny(
                withTrashed: $validated['with_trashed'],
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'] ?? null,
                search: $validated['search'] ?? null,
                stockAdjustmentId: $validated['stock_adjustment_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validated['refresh'],
                    pagination: (function () use ($validated) {
                        $pagination = null;
                        if (isset($validated['paginate'])) {
                            $pagination = new ExecutePaginationDTO(
                                page: $validated['paginate']['page'],
                                perPage: $validated['paginate']['per_page'],
                            );
                        }

                        return $pagination;
                    })(),
                    get: (function () use ($validated) {
                        $get = null;
                        if (isset($validated['get'])) {
                            $get = new ExecuteGetDTO(
                                limit: $validated['get']['limit'],
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

        return StockAdjustmentOutProductResource::collection($result);
    }

    public function read(StockAdjustmentOutProduct $stockAdjustmentOutProduct)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $stockAdjustmentOutProduct);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentOutProductActions->read($stockAdjustmentOutProduct);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new StockAdjustmentOutProductResource($result);
    }

    public function store(StockAdjustmentOutProductStoreRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $data = new StockAdjustmentOutProductCreateDTO(
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'],
                stockAdjustmentId: $validated['stock_adjustment_id'],
                qty: $validated['qty'],
                productUnitId: $validated['product_unit_id'],
                productUnitConversionValue: $validated['product_unit_conversion_value'],
                remarks: $validated['remarks'] ?? null,
            );

            $result = $this->stockAdjustmentOutProductActions->create($data);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(StockAdjustmentOutProduct $stockAdjustmentOutProduct, StockAdjustmentOutProductUpdateRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentOutProductActions->update(
                stockAdjustmentOutProduct: $stockAdjustmentOutProduct,
                data: $validated
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockAdjustmentOutProduct $stockAdjustmentOutProduct)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $stockAdjustmentOutProduct);

        $result = false;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentOutProductActions->delete($stockAdjustmentOutProduct);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
