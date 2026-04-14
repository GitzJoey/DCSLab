<?php

namespace App\Http\Controllers;

use App\Actions\ProductUnit\ProductUnitActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Resources\ProductUnitResource;
use App\Models\ProductUnit;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductUnitController extends BaseController
{
    private ProductUnitActions $productUnitActions;

    public function __construct(ProductUnitActions $productUnitActions)
    {
        parent::__construct();

        $this->productUnitActions = $productUnitActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', ProductUnit::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'product_id' => $request->filled('product_id') ? HashidsHelper::decodeId($request->product_id) : null,
            'unit_id' => $request->filled('unit_id') ? HashidsHelper::decodeId($request->unit_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'product_id' => ['nullable', 'integer', new ExistsForCompany('products', $request->company_id)],
            'unit_id' => ['nullable', 'integer', new ExistsForCompany('units', $request->company_id)],
            'is_base' => ['nullable', 'boolean'],
            'is_primary_unit' => ['nullable', 'boolean'],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('product_units', $request->company_id)],

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
            $result = $this->productUnitActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,

                productId: $validatedRequest['product_id'] ?? null,
                unitId: $validatedRequest['unit_id'] ?? null,
                isBase: $validatedRequest['is_base'] ?? null,
                isPrimaryUnit: $validatedRequest['is_primary_unit'] ?? null,
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
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return ProductUnitResource::collection($result);
        }
    }

    public function read(ProductUnit $productUnit)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $productUnit);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productUnitActions->read($productUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new ProductUnitResource($result);
        }
    }
}
