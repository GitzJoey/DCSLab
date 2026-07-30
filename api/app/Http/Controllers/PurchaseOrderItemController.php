<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrderItem\PurchaseOrderItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Resources\PurchaseOrderItemResource;
use App\Models\PurchaseOrderItem;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderItemController extends BaseController
{
    public function __construct(
        private readonly PurchaseOrderItemActions $purchaseOrderItemActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseOrderItem::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'purchase_order_supplier_id' => $request->filled('purchase_order_supplier_id') ? HashidsHelper::decodeId($request->purchase_order_supplier_id) : null,
            'product_unit_product_category_id' => $request->filled('product_unit_product_category_id') ? HashidsHelper::decodeId($request->product_unit_product_category_id) : null,
            'product_unit_product_brand_id' => $request->filled('product_unit_product_brand_id') ? HashidsHelper::decodeId($request->product_unit_product_brand_id) : null,
        ]);

        $validated = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'purchase_order_code' => ['nullable', 'string'],
            'purchase_order_start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'purchase_order_end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s'), 'after_or_equal:purchase_order_start_date'],
            'purchase_order_supplier_id' => ['nullable', 'integer', new ExistsForCompany('suppliers', $request->company_id)],
            'product_unit_code' => ['nullable', 'string'],
            'product_unit_product_name' => ['nullable', 'string'],
            'product_unit_product_category_id' => ['nullable', 'integer', new ExistsForCompany('product_categories', $request->company_id)],
            'product_unit_product_brand_id' => ['nullable', 'integer', new ExistsForCompany('brands', $request->company_id)],

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
            $result = $this->purchaseOrderItemActions->readAny(
                withTrashed: $validated['with_trashed'],
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'] ?? null,
                search: $validated['search'] ?? null,

                purchaseOrderCode: $validated['purchase_order_code'] ?? null,
                purchaseOrderStartDate: $validated['purchase_order_start_date'] ?? null,
                purchaseOrderEndDate: $validated['purchase_order_end_date'] ?? null,
                purchaseOrderSupplierId: $validated['purchase_order_supplier_id'] ?? null,
                productUnitCode: $validated['product_unit_code'] ?? null,
                productUnitProductName: $validated['product_unit_product_name'] ?? null,
                productUnitProductCategoryId: $validated['product_unit_product_category_id'] ?? null,
                productUnitProductBrandId: $validated['product_unit_product_brand_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validated['refresh'],
                    pagination: (function () use ($validated) {
                        if (! isset($validated['paginate'])) {
                            return null;
                        }

                        return new ExecutePaginationDTO(
                            page: $validated['paginate']['page'],
                            perPage: $validated['paginate']['per_page'],
                        );
                    })(),
                    get: (function () use ($validated) {
                        if (! isset($validated['get'])) {
                            return null;
                        }

                        return new ExecuteGetDTO(
                            limit: $validated['get']['limit'],
                        );
                    })(),
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return PurchaseOrderItemResource::collection($result);
    }

    public function read(PurchaseOrderItem $purchaseOrderItem)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseOrderItem);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderItemActions->read($purchaseOrderItem);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseOrderItemResource($result);
    }
}
