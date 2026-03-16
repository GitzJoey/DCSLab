<?php

namespace App\Http\Controllers;

use App\Actions\StockAdjustmentInProductSerial\StockAdjustmentInProductSerialActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\StockAdjustmentInProductSerialCreateDTO;
use App\DTOs\StockAdjustmentInProductSerialUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockAdjustmentInProductSerial\StockAdjustmentInProductSerialStoreRequest;
use App\Http\Requests\StockAdjustmentInProductSerial\StockAdjustmentInProductSerialUpdateRequest;
use App\Http\Resources\StockAdjustmentInProductSerialResource;
use App\Models\StockAdjustmentInProductSerial;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentInProductSerialController extends BaseController
{
    private $stockAdjustmentInProductSerialActions;

    public function __construct(StockAdjustmentInProductSerialActions $stockAdjustmentInProductSerialActions)
    {
        parent::__construct();
        $this->stockAdjustmentInProductSerialActions = $stockAdjustmentInProductSerialActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', StockAdjustmentInProductSerial::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'stock_adjustment_id' => $request->filled('stock_adjustment_id') ? HashidsHelper::decodeId($request->stock_adjustment_id) : null,
            'product_id' => $request->filled('product_id') ? HashidsHelper::decodeId($request->product_id) : null,
        ]);

        $validated = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],
            'stock_adjustment_id' => ['nullable', 'integer', new ExistsForCompany('stock_adjustments', $request->company_id)],
            'product_id' => ['nullable', 'integer', new ExistsForCompany('products', $request->company_id)],
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
            $result = $this->stockAdjustmentInProductSerialActions->readAny(
                withTrashed: $validated['with_trashed'],
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'] ?? null,
                search: $validated['search'] ?? null,
                stockAdjustmentId: $validated['stock_adjustment_id'] ?? null,
                productId: $validated['product_id'] ?? null,
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

        return StockAdjustmentInProductSerialResource::collection($result);
    }

    public function read(StockAdjustmentInProductSerial $stockAdjustmentInProductSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);

        $this->authorize('view', $stockAdjustmentInProductSerial);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentInProductSerialActions->read($stockAdjustmentInProductSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new StockAdjustmentInProductSerialResource($result);
    }

    public function store(StockAdjustmentInProductSerialStoreRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $data = new StockAdjustmentInProductSerialCreateDTO(
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'],
                stockAdjustmentId: $validated['stock_adjustment_id'],
                stockAdjustmentInProductId: $validated['stock_adjustment_in_product_id'],
                serial: $validated['serial'],
            );

            $result = $this->stockAdjustmentInProductSerialActions->create($data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(StockAdjustmentInProductSerial $stockAdjustmentInProductSerial, StockAdjustmentInProductSerialUpdateRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $data = StockAdjustmentInProductSerialUpdateDTO::fromStockAdjustmentInProductSerial(
                $stockAdjustmentInProductSerial,
                $validated['serial']
            );

            $result = $this->stockAdjustmentInProductSerialActions->update(
                $stockAdjustmentInProductSerial,
                $data
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockAdjustmentInProductSerial $stockAdjustmentInProductSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);

        $this->authorize('delete', $stockAdjustmentInProductSerial);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->stockAdjustmentInProductSerialActions->delete($stockAdjustmentInProductSerial);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
