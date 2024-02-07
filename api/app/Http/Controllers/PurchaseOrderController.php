<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\Http\Requests\PurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\Company;
use App\Models\PurchaseOrder;
use Exception;

class PurchaseOrderController extends BaseController
{
    private $purchaseOrderActions;

    public function __construct(PurchaseOrderActions $purchaseOrderActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->purchaseOrderActions = $purchaseOrderActions;
    }

    public function store(PurchaseOrderRequest $purchaseOrderRequest)
    {
        $request = $purchaseOrderRequest->validated();

        $company_id = $request['company_id'];

        $branch_id = $request['branch_id'];
        if ($branch_id) {
            if (! Company::find($company_id)->branches()->where('id', '=', $branch_id)->exists()) {
                return response()->error([
                    'branch_id' => [trans('rules.valid_branch')],
                ], 422);
            }
        }

        $code = $request['invoice_code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->purchaseOrderActions->generateUniqueCode();
            } while (! $this->purchaseOrderActions->isUniqueCode($code, $company_id));
        } else {
            if (! $this->purchaseOrderActions->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $globalDiscountArr = [];
        if (array_key_exists('arr_global_discount_discount_type', $request)) {
            foreach ($request['arr_global_discount_discount_type'] as $order => $product_unit_global_discount_discount_type) {
                $globalDiscount = [
                    'order' => $request['arr_global_discount_order'][$order],
                    'discount_type' => $product_unit_global_discount_discount_type,
                    'amount' => $request['arr_global_discount_amount'][$order],
                ];
                array_push($globalDiscountArr, $globalDiscount);
            }
        }

        $purchaseOrderArr = [
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'invoice_code' => $code,
            'invoice_date' => $request['invoice_date'],
            'supplier_id' => $request['supplier_id'],
            'shipping_date' => $request['shipping_date'],
            'shipping_address' => $request['shipping_address'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
            'global_discount' => $globalDiscountArr,
        ];

        $productUnitArr = [];
        $productUnitCount = count($request['arr_product_unit_id']);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $perUnitDiscountArr = [];
            if (array_key_exists('arr_product_unit_per_unit_discount_discount_type', $request)) {
                if (array_key_exists($i, $request['arr_product_unit_per_unit_discount_discount_type'])) {
                    for ($j = 0; $j < count($request['arr_product_unit_per_unit_discount_discount_type'][$i]); $j++) {
                        $perUnitDiscount = [
                            'order' => $request['arr_product_unit_per_unit_discount_order'][$i][$j],
                            'discount_type' => $request['arr_product_unit_per_unit_discount_discount_type'][$i][$j],
                            'amount' => $request['arr_product_unit_per_unit_discount_amount'][$i][$j],
                        ];
                        array_push($perUnitDiscountArr, $perUnitDiscount);
                    }
                }
            }

            $perUnitSubTotalDiscountArr = [];
            if (array_key_exists('arr_product_unit_per_unit_sub_total_discount_discount_type', $request)) {
                if (array_key_exists($i, $request['arr_product_unit_per_unit_sub_total_discount_discount_type'])) {
                    for ($j = 0; $j < count($request['arr_product_unit_per_unit_sub_total_discount_discount_type'][$i]); $j++) {
                        $perUnitSubTotalDiscount = [
                            'order' => $request['arr_product_unit_per_unit_sub_total_discount_order'][$i][$j],
                            'discount_type' => $request['arr_product_unit_per_unit_sub_total_discount_discount_type'][$i][$j],
                            'amount' => $request['arr_product_unit_per_unit_sub_total_discount_amount'][$i][$j],
                        ];
                        array_push($perUnitSubTotalDiscountArr, $perUnitSubTotalDiscount);
                    }
                }
            }

            array_push($productUnitArr, [
                'id' => $request['arr_product_unit_id'][$i],
                'product_id' => $request['arr_product_unit_product_id'][$i],
                'product_unit_id' => $request['arr_product_unit_product_unit_id'][$i],
                'qty' => $request['arr_product_unit_qty'][$i],
                'product_unit_amount_per_unit' => $request['arr_product_unit_amount_per_unit'][$i],
                'product_unit_initial_price' => $request['arr_product_unit_initial_price'][$i],
                'per_unit_discounts' => $perUnitDiscountArr,
                'per_unit_sub_total_discounts' => $perUnitSubTotalDiscountArr,
                'vat_status' => $request['arr_product_unit_vat_status'][$i],
                'vat_rate' => $request['arr_product_unit_vat_rate'][$i],
                'remarks' => $request['arr_product_unit_remarks'][$i],
            ]);
        }

        $calculatedProductUnitArr = $this->calculateResult($productUnitArr, $globalDiscountArr);
        foreach ($calculatedProductUnitArr as $productUnit) {
            if ($productUnit['priceAfterDiscount'] < 0) {
                return response()->error([
                    'per_unit_discounts' => [trans('rules.purchase_order.per_unit_discounts')],
                ], 422);
            }

            if ($productUnit['total'] < 0) {
                return response()->error([
                    'per_unit_subtotal_discounts' => [trans('rules.purchase_order.per_unit_subtotal_discounts')],
                ], 422);
            }

            if ($productUnit['grandTotal'] < 0) {
                return response()->error([
                    'global_discounts' => [trans('rules.purchase_order.global_discounts')],
                ], 422);
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderActions->create(
                $purchaseOrderArr,
                $productUnitArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(PurchaseOrderRequest $request)
    {
        $request = $request->validated();

        $company_id = $request['company_id'];

        $branch_id = $request['branch_id'];
        if ($branch_id) {
            if (! Company::find($company_id)->branches()->where('id', '=', $branch_id)->exists()) {
                return response()->error([
                    'branch_id' => [trans('rules.valid_branch')],
                ], 422);
            }
        }

        $search = $request['search'] ?? '';
        $paginate = $request['paginate'] ?? false;
        $page = abs($request['page'] ?? 1);
        $perPage = abs($request['per_page'] ?? 10);
        $useCache = boolval($request['use_cache'] ?? true);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderActions->readAny(
                companyId: $company_id,
                branchId: $branch_id,
                search: $search,
                paginate: $paginate,
                page: $page,
                perPage: $perPage,
                useCache: $useCache
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = PurchaseOrderResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseOrder $purchaseOrder, PurchaseOrderRequest $purchaseOrderRequest)
    {
        $request = $purchaseOrderRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderActions->read($purchaseOrder);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseOrderResource($result);

            return $response;
        }
    }

    public function update(PurchaseOrder $purchaseOrder, PurchaseOrderRequest $purchaseOrderRequest)
    {
        $request = $purchaseOrderRequest->validated();

        $company_id = $request['company_id'];

        $branch_id = $request['branch_id'];
        if ($branch_id) {
            if (! Company::find($company_id)->branches()->where('id', '=', $branch_id)->exists()) {
                return response()->error([
                    'branch_id' => [trans('rules.valid_branch')],
                ], 422);
            }
        }

        if ($branch_id !== $purchaseOrder->branch_id) {
            return response()->error([
                'branch_id' => [trans('rules.valid_branch')],
            ], 422);
        }

        $invoice_code = $request['invoice_code'];
        if (! $this->purchaseOrderActions->isUniqueCode($invoice_code, $company_id, $purchaseOrder->id)) {
            return response()->error([
                'invoice_code' => [trans('rules.purchase_order.unique_code')],
            ], 422);
        }

        $globalDiscountArr = [];
        if (array_key_exists('arr_global_discount_discount_type', $request)) {
            for ($i = 0; $i < count($request['arr_global_discount_discount_type']); $i++) {
                $globalDiscount = [
                    'id' => $request['arr_global_discount_id'][$i],
                    'ulid' => $request['arr_global_discount_ulid'][$i],
                    'order' => $request['arr_global_discount_order'][$i],
                    'discount_type' => $request['arr_global_discount_discount_type'][$i],
                    'amount' => $request['arr_global_discount_amount'][$i],
                ];
                array_push($globalDiscountArr, $globalDiscount);
            }
        }

        $purchaseOrderArr = [
            'invoice_code' => $invoice_code,
            'invoice_date' => $request['invoice_date'],
            'supplier_id' => $request['supplier_id'],
            'shipping_date' => $request['shipping_date'],
            'shipping_address' => $request['shipping_address'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
            'global_discount' => $globalDiscountArr,
        ];

        $productUnitArr = [];
        $productUnitCount = count($request['arr_product_unit_id']);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $product_unit_id = $request['arr_product_unit_id'][$i] ? $request['arr_product_unit_id'][$i] : '';

            $product_unit_ulid = $request['arr_product_unit_ulid'][$i];

            $perUnitDiscountArr = [];
            if (array_key_exists('arr_product_unit_per_unit_discount_discount_type', $request)) {
                if (array_key_exists($i, $request['arr_product_unit_per_unit_discount_discount_type'])) {
                    for ($j = 0; $j < count($request['arr_product_unit_per_unit_discount_discount_type'][$i]); $j++) {
                        $perUnitDiscount = [
                            'id' => $request['arr_product_unit_per_unit_discount_id'][$i][$j],
                            'ulid' => $request['arr_product_unit_per_unit_discount_ulid'][$i][$j],
                            'order' => $request['arr_product_unit_per_unit_discount_order'][$i][$j],
                            'discount_type' => $request['arr_product_unit_per_unit_discount_discount_type'][$i][$j],
                            'amount' => $request['arr_product_unit_per_unit_discount_amount'][$i][$j],
                        ];
                        array_push($perUnitDiscountArr, $perUnitDiscount);
                    }
                }
            }

            $perUnitSubTotalDiscountArr = [];
            if (array_key_exists('arr_product_unit_per_unit_sub_total_discount_discount_type', $request)) {
                if (array_key_exists($i, $request['arr_product_unit_per_unit_sub_total_discount_discount_type'])) {
                    for ($j = 0; $j < count($request['arr_product_unit_per_unit_sub_total_discount_discount_type'][$i]); $j++) {
                        $perUnitSubTotalDiscount = [
                            'id' => $request['arr_product_unit_per_unit_sub_total_discount_id'][$i][$j],
                            'ulid' => $request['arr_product_unit_per_unit_sub_total_discount_ulid'][$i][$j],
                            'order' => $request['arr_product_unit_per_unit_sub_total_discount_order'][$i][$j],
                            'discount_type' => $request['arr_product_unit_per_unit_sub_total_discount_discount_type'][$i][$j],
                            'amount' => $request['arr_product_unit_per_unit_sub_total_discount_amount'][$i][$j],
                        ];
                        array_push($perUnitSubTotalDiscountArr, $perUnitSubTotalDiscount);
                    }
                }
            }

            array_push($productUnitArr, [
                'id' => $product_unit_id,
                'ulid' => $product_unit_ulid,
                'product_id' => $request['arr_product_unit_product_id'][$i],
                'product_unit_id' => $request['arr_product_unit_product_unit_id'][$i],
                'qty' => $request['arr_product_unit_qty'][$i],
                'product_unit_amount_per_unit' => $request['arr_product_unit_amount_per_unit'][$i],
                'product_unit_initial_price' => $request['arr_product_unit_initial_price'][$i],
                'per_unit_discounts' => $perUnitDiscountArr,
                'per_unit_sub_total_discounts' => $perUnitSubTotalDiscountArr,
                'vat_status' => $request['arr_product_unit_vat_status'][$i],
                'vat_rate' => $request['arr_product_unit_vat_rate'][$i],
                'remarks' => $request['arr_product_unit_remarks'][$i],
            ]);
        }

        $calculatedProductUnitArr = $this->calculateResult($productUnitArr, $globalDiscountArr);
        foreach ($calculatedProductUnitArr as $productUnit) {
            if ($productUnit['priceAfterDiscount'] < 0) {
                return response()->error([
                    'per_unit_discounts' => [trans('rules.purchase_order.per_unit_discounts')],
                ], 422);
            }

            if ($productUnit['total'] < 0) {
                return response()->error([
                    'per_unit_subtotal_discounts' => [trans('rules.purchase_order.per_unit_subtotal_discounts')],
                ], 422);
            }

            if ($productUnit['grandTotal'] < 0) {
                return response()->error([
                    'global_discounts' => [trans('rules.purchase_order.global_discounts')],
                ], 422);
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderActions->update($purchaseOrder, $purchaseOrderArr, $productUnitArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseOrder $purchaseOrder, PurchaseOrderRequest $purchaseOrderRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderActions->delete($purchaseOrder);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }

    public function calculateResult(array $productUnitArr, array $globalDiscountArr): array
    {
        $productUnitNew = [];
        foreach ($productUnitArr as $productUnit) {
            $qty = $productUnit['qty'];

            $priceBeforeDiscount = $productUnit['product_unit_initial_price'];

            $perUnitDiscount = $this->purchaseOrderActions->calculatePerUnitDiscountFromNestedArray(
                $priceBeforeDiscount,
                $productUnit['per_unit_discounts']
            );

            $priceAfterDiscount = $priceBeforeDiscount - $perUnitDiscount;

            $subTotal = $qty * $priceAfterDiscount;

            $perUnitSubTotalDiscount = $this->purchaseOrderActions->calculatePerUnitSubTotalDiscountFromNestedArray(
                $subTotal,
                $productUnit['per_unit_sub_total_discounts']
            );

            $total = $subTotal - $perUnitSubTotalDiscount;

            $arrProductUnit = [
                'qty' => $qty,
                'priceBeforeDiscount' => $priceBeforeDiscount,
                'perUnitDiscount' => $perUnitDiscount,
                'priceAfterDiscount' => $priceAfterDiscount,
                'subTotal' => $subTotal,
                'perUnitSubTotalDiscount' => $perUnitSubTotalDiscount,
                'total' => $total,
                'globalDiscount' => 0,
                'grandTotal' => 0,
                'finalPrice' => 0,
            ];

            array_push($productUnitNew, $arrProductUnit);
        }

        $grandTotal = 0;
        foreach ($productUnitNew as $productUnit) {
            $grandTotal = $grandTotal + $productUnit['total'];
        }

        foreach ($productUnitNew as $order => $productUnit) {
            $globalDiscount = $this->purchaseOrderActions->calculateGlobalDiscountFromNestedArray(
                $grandTotal,
                $globalDiscountArr
            );

            $productUnitNew[$order]['globalDiscount'] = $grandTotal != 0 ? $productUnit['total'] / $grandTotal * $globalDiscount : 0;
            $productUnitNew[$order]['grandTotal'] = $productUnit['total'] - $productUnitNew[$order]['globalDiscount'];
            $productUnitNew[$order]['finalPrice'] = $productUnitNew[$order]['grandTotal'] / $productUnit['qty'];
        }

        return $productUnitNew;
    }
}
