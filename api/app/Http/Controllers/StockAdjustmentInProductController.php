<?php

namespace App\Http\Controllers;

use App\Actions\StockAdjustmentInProduct\StockAdjustmentInProductActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\StockAdjustmentInProductCreateDTO;
use App\DTOs\StockAdjustmentInProductUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockAdjustmentInProduct\StockAdjustmentInProductStoreRequest;
use App\Http\Requests\StockAdjustmentInProduct\StockAdjustmentInProductUpdateRequest;
use App\Http\Resources\StockAdjustmentInProductResource;
use App\Models\StockAdjustmentInProduct;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentInProductController extends BaseController
{
    private $stockAdjustmentInProductActions;

    public function __construct(StockAdjustmentInProductActions $stockAdjustmentInProductActions)
    {
        parent::__construct();
        $this->stockAdjustmentInProductActions = $stockAdjustmentInProductActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', StockAdjustmentInProduct::class);

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
            $result = $this->stockAdjustmentInProductActions->readAny(
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

        return StockAdjustmentInProductResource::collection($result);
    }

    public function read(StockAdjustmentInProduct $stockAdjustmentInProduct)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $stockAdjustmentInProduct);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentInProductActions->read($stockAdjustmentInProduct);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new StockAdjustmentInProductResource($result);
    }

    public function store(StockAdjustmentInProductStoreRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $data = new StockAdjustmentInProductCreateDTO(
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'],
                stockAdjustmentId: $validated['stock_adjustment_id'],
                qty: $validated['qty'],
                productUnitId: $validated['product_unit_id'],
                productUnitConversionValue: $validated['product_unit_conversion_value'],
                productUnitCogs: $validated['product_unit_cogs'],
                remarks: $validated['remarks'] ?? null,
            );

            $result = $this->stockAdjustmentInProductActions->create($data);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(StockAdjustmentInProduct $stockAdjustmentInProduct, StockAdjustmentInProductUpdateRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $data = new StockAdjustmentInProductUpdateDTO(
                qty: $validated['qty'],
                productUnitId: $validated['product_unit_id'],
                productUnitConversionValue: $validated['product_unit_conversion_value'],
                productUnitCogs: $validated['product_unit_cogs'],
                remarks: $validated['remarks'] ?? null,
            );

            $result = $this->stockAdjustmentInProductActions->update(
                stockAdjustmentInProduct: $stockAdjustmentInProduct,
                data: $data
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockAdjustmentInProduct $stockAdjustmentInProduct)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $stockAdjustmentInProduct);

        $result = false;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentInProductActions->delete($stockAdjustmentInProduct);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
