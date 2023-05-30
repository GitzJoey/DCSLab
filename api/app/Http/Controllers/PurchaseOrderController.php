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

        $globalDiscounts = [];
        if (array_key_exists('arr_global_discount_discount_type', $request)) {
            for ($i = 0; $i < count($request['arr_global_discount_discount_type']); $i++) {
                $globalDiscount = [
                    'discount_type' => $request['arr_global_discount_discount_type'][$i],
                    'amount' => $request['arr_global_discount_amount'][$i],
                ];
                array_push($globalDiscounts, $globalDiscount);
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
            'global_discount' => $globalDiscounts,
        ];

        $productUnitArr = [];
        $productUnitCount = count($request['arr_product_unit_id']);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $perUnitDiscounts = [];
            if (array_key_exists('arr_product_unit_per_unit_discount_discount_type', $request)) {
                if (array_key_exists($i, $request['arr_product_unit_per_unit_discount_discount_type'])) {
                    foreach ($request['arr_product_unit_per_unit_discount_discount_type'][$i] as $idx => $product_unit_per_unit_discount_discount_type) {
                        $perUnitDiscount = [
                            'discount_type' => $request['arr_product_unit_per_unit_discount_discount_type'][$i][$idx],
                            'amount' => $request['arr_product_unit_per_unit_discount_amount'][$i][$idx],
                        ];
                        array_push($perUnitDiscounts, $perUnitDiscount);
                    }
                }
            }

            $perUnitSubTotalDiscounts = [];
            if (array_key_exists('arr_product_unit_per_unit_sub_total_discount_discount_type', $request)) {
                if (array_key_exists($i, $request['arr_product_unit_per_unit_sub_total_discount_discount_type'])) {
                    foreach ($request['arr_product_unit_per_unit_sub_total_discount_discount_type'][$i] as $idx => $product_unit_per_unit_discount_discount_type) {
                        $perUnitSubTotalDiscount = [
                            'discount_type' => $request['arr_product_unit_per_unit_sub_total_discount_discount_type'][$i][$idx],
                            'amount' => $request['arr_product_unit_per_unit_sub_total_discount_amount'][$i][$idx],
                        ];
                        array_push($perUnitSubTotalDiscounts, $perUnitSubTotalDiscount);
                    }
                }
            }

            array_push($productUnitArr, [
                'id' => $request['arr_product_unit_id'][$i],
                'product_unit_id' => $request['arr_product_unit_product_unit_id'][$i],
                'qty' => $request['arr_product_unit_qty'][$i],
                'product_unit_amount_per_unit' => $request['arr_product_unit_amount_per_unit'][$i],
                'product_unit_initial_price' => $request['arr_product_unit_initial_price'][$i],
                'per_unit_discount' => $perUnitDiscounts,
                'per_unit_sub_total_discount' => $perUnitSubTotalDiscounts,
                'vat_status' => $request['arr_product_unit_vat_status'][$i],
                'vat_rate' => $request['arr_product_unit_vat_rate'][$i],
                'remarks' => $request['arr_product_unit_remarks'][$i],
            ]);
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

        $globalDiscounts = [];
        if (array_key_exists('arr_global_discount_discount_type', $request)) {
            for ($i = 0; $i < count($request['arr_global_discount_discount_type']); $i++) {
                $globalDiscount = [
                    'id' => $request['arr_global_discount_id'][$i],
                    'discount_type' => $request['arr_global_discount_discount_type'][$i],
                    'amount' => $request['arr_global_discount_amount'][$i],
                ];
                array_push($globalDiscounts, $globalDiscount);
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
            'global_discount' => $globalDiscounts,
        ];

        $productUnitArr = [];
        $productUnitCount = count($request['arr_product_unit_id']);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $perUnitDiscounts = [];
            if (array_key_exists('arr_product_unit_per_unit_discount_discount_type', $request)) {
                if (array_key_exists($i, $request['arr_product_unit_per_unit_discount_discount_type'])) {
                    foreach ($request['arr_product_unit_per_unit_discount_discount_type'][$i] as $idx => $product_unit_per_unit_discount_discount_type) {
                        $perUnitDiscount = [
                            'id' => $request['arr_product_unit_per_unit_discount_id'][$i][$idx],
                            'discount_type' => $request['arr_product_unit_per_unit_discount_discount_type'][$i][$idx],
                            'amount' => $request['arr_product_unit_per_unit_discount_amount'][$i][$idx],
                        ];
                        array_push($perUnitDiscounts, $perUnitDiscount);
                    }
                }
            }

            $perUnitSubTotalDiscounts = [];
            if (array_key_exists('arr_product_unit_per_unit_sub_total_discount_discount_type', $request)) {
                if (array_key_exists($i, $request['arr_product_unit_per_unit_sub_total_discount_discount_type'])) {
                    foreach ($request['arr_product_unit_per_unit_sub_total_discount_discount_type'][$i] as $idx => $product_unit_per_unit_discount_discount_type) {
                        $perUnitSubTotalDiscount = [
                            'id' => $request['arr_product_unit_per_unit_sub_total_discount_id'][$i][$idx],
                            'discount_type' => $request['arr_product_unit_per_unit_sub_total_discount_discount_type'][$i][$idx],
                            'amount' => $request['arr_product_unit_per_unit_sub_total_discount_amount'][$i][$idx],
                        ];
                        array_push($perUnitSubTotalDiscounts, $perUnitSubTotalDiscount);
                    }
                }
            }

            array_push($productUnitArr, [
                'id' => $request['arr_product_unit_id'][$i],
                'product_unit_id' => $request['arr_product_unit_product_unit_id'][$i],
                'qty' => $request['arr_product_unit_qty'][$i],
                'product_unit_amount_per_unit' => $request['arr_product_unit_amount_per_unit'][$i],
                'product_unit_initial_price' => $request['arr_product_unit_initial_price'][$i],
                'per_unit_discount' => $perUnitDiscounts,
                'per_unit_sub_total_discount' => $perUnitSubTotalDiscounts,
                'vat_status' => $request['arr_product_unit_vat_status'][$i],
                'vat_rate' => $request['arr_product_unit_vat_rate'][$i],
                'remarks' => $request['arr_product_unit_remarks'][$i],
            ]);
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

    public function delete(PurchaseOrder $purchaseOrder)
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
}
