<?php

namespace App\Actions\PurchaseOrder;

use App\Enums\DiscountType;
use App\Enums\VatStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDiscount;
use App\Models\PurchaseOrderProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PurchaseOrderActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(
        array $purchaseOrderArr,
        array $productUnitArr
    ): PurchaseOrder {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseOrder = $this->savePurchaseOrder($purchaseOrderArr);

            $this->saveProductUnit($purchaseOrder, $productUnitArr);

            $this->saveGlobalDiscount($purchaseOrder, $purchaseOrderArr['global_discount']);

            $this->updateSummary($purchaseOrder->refresh(), false);

            DB::commit();

            $this->flushCache();

            return $purchaseOrder;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readAny(
        int $companyId,
        int $branchId = null,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                if (! $branchId) {
                    $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                } else {
                    $cacheKey = 'read_'.$companyId.'-'.$branchId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                }

                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            $query = count($with) != 0 ? PurchaseOrder::with($with) : PurchaseOrder::with('company', 'branch', 'supplier', 'globalDiscounts', 'productUnits.perUnitDiscounts', 'productUnits.perUnitSubTotalDiscounts');

            if (! $companyId) {
                return null;
            }
            $query = $query->whereCompanyId($companyId);

            if ($branchId) {
                $query = $query->whereBranchId($branchId);
            }

            if ($withTrashed) {
                $query = $query->withTrashed();
            }

            if (empty($search)) {
                $query = $query->latest();
            } else {
                $query = $query->where('invoice_code', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? abs($perPage) : Config::get('dcslab.PAGINATION_LIMIT');
                $page = is_numeric($page) ? abs($page) : 1;

                $result = $query->paginate(
                    perPage: $perPage,
                    page: $page
                );
            } else {
                $result = $query->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function read(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $purchaseOrder->refresh()->with('company', 'branch', 'supplier', 'globalDiscounts', 'productUnits.perUnitDiscounts', 'productUnits.perUnitSubTotalDiscounts')->where('id', '=', $purchaseOrder->id)->first();
    }

    public function update(
        PurchaseOrder $purchaseOrder,
        array $purchaseOrderArr,
        array $productUnitArr
    ): PurchaseOrder {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $this->updatePurchaseOrder($purchaseOrder, $purchaseOrderArr);

            $this->updateProductUnit($purchaseOrder, $productUnitArr);

            $this->updateGlobalDiscount($purchaseOrder, $purchaseOrderArr['global_discount']);

            $this->updateSummary($purchaseOrder->refresh(), false);

            DB::commit();

            $this->flushCache();

            return $purchaseOrder->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrder $purchaseOrder): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseOrder->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCode(): string
    {
        return 'PO-'.date('Ymd').'-'.str_pad(PurchaseOrder::max('id') + 1, 6, '0', STR_PAD_LEFT);
    }

    public function isUniqueCode(string $code, int $companyId, int $exceptId = null): bool
    {
        $query = PurchaseOrder::where('company_id', $companyId)->where('invoice_code', $code);
        if ($exceptId) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function savePurchaseOrder(array $purchaseOrderArr): PurchaseOrder
    {
        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->company_id = $purchaseOrderArr['company_id'];
        $purchaseOrder->branch_id = $purchaseOrderArr['branch_id'];
        $purchaseOrder->invoice_code = $purchaseOrderArr['invoice_code'];
        $purchaseOrder->invoice_date = $purchaseOrderArr['invoice_date'];
        $purchaseOrder->supplier_id = $purchaseOrderArr['supplier_id'];
        $purchaseOrder->shipping_date = $purchaseOrderArr['shipping_date'];
        $purchaseOrder->shipping_address = $purchaseOrderArr['shipping_address'];
        $purchaseOrder->remarks = $purchaseOrderArr['remarks'];
        $purchaseOrder->status = $purchaseOrderArr['status'];
        $purchaseOrder->save();

        return $purchaseOrder;
    }

    public function saveProductUnit(PurchaseOrder $purchaseOrder, array $productUnitArr): Collection
    {
        foreach ($productUnitArr as $productUnit) {
            $newProductUnit = new PurchaseOrderProductUnit();
            $newProductUnit->company_id = $purchaseOrder->company_id;
            $newProductUnit->branch_id = $purchaseOrder->branch_id;
            $newProductUnit->purchase_order_id = $purchaseOrder->id;
            $newProductUnit->product_id = $productUnit['product_id'];
            $newProductUnit->product_unit_id = $productUnit['product_unit_id'];
            $newProductUnit->qty = $productUnit['qty'];
            $newProductUnit->product_unit_amount_per_unit = $productUnit['product_unit_amount_per_unit'];
            $newProductUnit->product_unit_initial_price = $productUnit['product_unit_initial_price'];
            $newProductUnit->vat_status = $productUnit['vat_status'];
            $newProductUnit->vat_rate = $productUnit['vat_rate'];
            $newProductUnit->remarks = $productUnit['remarks'];
            $newProductUnit->save();

            $this->savePerUnitDiscount(
                $purchaseOrder,
                $newProductUnit,
                $productUnit['per_unit_discounts']
            );

            $this->savePerUnitSubTotalDiscount(
                $purchaseOrder,
                $newProductUnit,
                $productUnit['per_unit_sub_total_discounts']
            );
        }

        return $purchaseOrder->refresh()->productUnits;
    }

    public function savePerUnitDiscount(PurchaseOrder $purchaseOrder, PurchaseOrderProductUnit $productUnit, array $perUnitDiscountArr): Collection
    {
        $discounts = [];
        foreach ($perUnitDiscountArr as $perUnitDiscount) {
            $discount = new PurchaseOrderDiscount();
            $discount->company_id = $purchaseOrder->company_id;
            $discount->branch_id = $purchaseOrder->branch_id;
            $discount->purchase_order_id = $purchaseOrder->id;
            $discount->purchase_order_product_unit_id = $productUnit->id;
            $discount->order = $perUnitDiscount['order'];
            $discount->discount_type = $perUnitDiscount['discount_type'];
            $discount->amount = $perUnitDiscount['amount'];
            array_push($discounts, $discount);
        }
        $productUnit->perUnitDiscounts()->saveMany($discounts);

        return $productUnit->perUnitDiscounts;
    }

    public function savePerUnitSubTotalDiscount(PurchaseOrder $purchaseOrder, PurchaseOrderProductUnit $productUnit, array $perUnitSubTotalDiscountArr): Collection
    {
        $discounts = [];
        foreach ($perUnitSubTotalDiscountArr as $perUnitSubTotalDiscount) {
            $discount = new PurchaseOrderDiscount();
            $discount->company_id = $purchaseOrder->company_id;
            $discount->branch_id = $purchaseOrder->branch_id;
            $discount->purchase_order_id = $purchaseOrder->id;
            $discount->purchase_order_product_unit_id = $productUnit->id;
            $discount->order = $perUnitSubTotalDiscount['order'];
            $discount->discount_type = $perUnitSubTotalDiscount['discount_type'];
            $discount->amount = $perUnitSubTotalDiscount['amount'];
            array_push($discounts, $discount);
        }

        $productUnit->perUnitSubTotalDiscounts()->saveMany($discounts);

        return $productUnit->perUnitSubTotalDiscounts;
    }

    public function saveGlobalDiscount(PurchaseOrder $purchaseOrder, array $globalDiscountArr): Collection
    {
        $discounts = [];
        foreach ($globalDiscountArr as $globalDiscount) {
            $discount = new PurchaseOrderDiscount();
            $discount->company_id = $purchaseOrder->company_id;
            $discount->branch_id = $purchaseOrder->branch_id;
            $discount->purchase_order_id = $purchaseOrder->id;
            $discount->order = $globalDiscount['order'];
            $discount->discount_type = $globalDiscount['discount_type'];
            $discount->amount = $globalDiscount['amount'];
            array_push($discounts, $discount);
        }

        $purchaseOrder->globalDiscounts()->saveMany($discounts);

        return $purchaseOrder->refresh()->globalDiscounts;
    }

    public function updatePurchaseOrder(PurchaseOrder $purchaseOrder, array $purchaseOrderArr): PurchaseOrder
    {
        $purchaseOrder->update([
            'invoice_code' => $purchaseOrderArr['invoice_code'],
            'invoice_date' => $purchaseOrderArr['invoice_date'],
            'supplier_id' => $purchaseOrderArr['supplier_id'],
            'shipping_date' => $purchaseOrderArr['shipping_date'],
            'shipping_address' => $purchaseOrderArr['shipping_address'],
            'remarks' => $purchaseOrderArr['remarks'],
            'status' => $purchaseOrderArr['status'],
        ]);

        return $purchaseOrder;
    }

    public function updateProductUnit(PurchaseOrder $purchaseOrder, array $productUnitArr): Collection
    {
        $productUnitArr = collect($productUnitArr)->map(function ($productUnit) use ($purchaseOrder) {
            $perUnitDiscount = array_key_exists('per_unit_discounts', $productUnit) ? $productUnit['per_unit_discounts'] : [];
            $perUnitSubTotalDiscount = array_key_exists('per_unit_sub_total_discounts', $productUnit) ? $productUnit['per_unit_sub_total_discounts'] : [];

            return [
                'id' => $productUnit['id'],
                'ulid' => $productUnit['ulid'],
                'company_id' => $purchaseOrder->company_id,
                'branch_id' => $purchaseOrder->branch_id,
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $productUnit['product_id'],
                'product_unit_id' => $productUnit['product_unit_id'],
                'qty' => $productUnit['qty'],
                'product_unit_amount_per_unit' => $productUnit['product_unit_amount_per_unit'],
                'product_unit_initial_price' => $productUnit['product_unit_initial_price'],
                'vat_status' => $productUnit['vat_status'],
                'vat_rate' => $productUnit['vat_rate'],
                'remarks' => $productUnit['remarks'],
                'per_unit_discounts' => $perUnitDiscount,
                'per_unit_sub_total_discounts' => $perUnitSubTotalDiscount,
            ];
        })->toArray();

        $productUnitNewIds = collect($productUnitArr)->pluck('id')->toArray();
        $purchaseOrder->productUnits()->whereNotIn('id', $productUnitNewIds)->delete();

        foreach ($productUnitArr as $productUnit) {
            $newProductUnit = new PurchaseOrderProductUnit();
            if ($productUnit['id']) {
                $newProductUnit = PurchaseOrderProductUnit::find($productUnit['id']);
            }

            $newProductUnit->ulid = $productUnit['ulid'];
            $newProductUnit->company_id = $purchaseOrder->company_id;
            $newProductUnit->branch_id = $purchaseOrder->branch_id;
            $newProductUnit->purchase_order_id = $purchaseOrder->id;
            $newProductUnit->product_id = $productUnit['product_id'];
            $newProductUnit->product_unit_id = $productUnit['product_unit_id'];
            $newProductUnit->qty = $productUnit['qty'];
            $newProductUnit->product_unit_amount_per_unit = $productUnit['product_unit_amount_per_unit'];
            $newProductUnit->product_unit_initial_price = $productUnit['product_unit_initial_price'];
            $newProductUnit->vat_status = $productUnit['vat_status'];
            $newProductUnit->vat_rate = $productUnit['vat_rate'];
            $newProductUnit->remarks = $productUnit['remarks'];
            $newProductUnit->save();

            $this->updatePerUnitDiscount($purchaseOrder, $newProductUnit, $productUnit['per_unit_discounts']);
            $this->updatePerUnitSubTotalDiscount($purchaseOrder, $newProductUnit, $productUnit['per_unit_sub_total_discounts']);
        }

        return $purchaseOrder->refresh()->productUnits;
    }

    public function updatePerUnitDiscount(PurchaseOrder $purchaseOrder, PurchaseOrderProductUnit $productUnit, array $perUnitDiscountArr): Collection
    {
        $perUnitDiscountArr = collect($perUnitDiscountArr)->map(function ($perUnitDiscount) use ($purchaseOrder, $productUnit) {
            return [
                'id' => $perUnitDiscount['id'],
                'ulid' => $perUnitDiscount['ulid'],
                'company_id' => $purchaseOrder->company_id,
                'branch_id' => $purchaseOrder->branch_id,
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_product_unit_id' => $productUnit->id,
                'order' => $perUnitDiscount['order'],
                'discount_type' => $perUnitDiscount['discount_type'],
                'amount' => $perUnitDiscount['amount'],
            ];
        })->toArray();

        $perUnitDiscountNewIds = collect($perUnitDiscountArr)->pluck('id')->toArray();
        $productUnit->perUnitDiscounts()->whereNotIn('id', $perUnitDiscountNewIds)->delete();

        $productUnit->perUnitDiscounts()->upsert(
            $perUnitDiscountArr,
            ['id'],
            [
                'ulid',
                'company_id',
                'branch_id',
                'purchase_order_id',
                'order',
                'discount_type',
                'amount',
            ]
        );

        return $productUnit->perUnitDiscounts;
    }

    public function updatePerUnitSubTotalDiscount(PurchaseOrder $purchaseOrder, PurchaseOrderProductUnit $productUnit, array $perUnitSubTotalDiscountArr): Collection
    {
        $perUnitSubTotalDiscountArr = collect($perUnitSubTotalDiscountArr)->map(function ($perUnitSubTotalDiscount) use ($purchaseOrder, $productUnit) {
            return [
                'id' => $perUnitSubTotalDiscount['id'],
                'ulid' => $perUnitSubTotalDiscount['ulid'],
                'company_id' => $purchaseOrder->company_id,
                'branch_id' => $purchaseOrder->branch_id,
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_product_unit_id' => $productUnit->id,
                'order' => $perUnitSubTotalDiscount['order'],
                'discount_type' => $perUnitSubTotalDiscount['discount_type'],
                'amount' => $perUnitSubTotalDiscount['amount'],
            ];
        })->toArray();

        $perUnitSubTotalDiscountNewIds = collect($perUnitSubTotalDiscountArr)->pluck('id')->toArray();
        $productUnit->perUnitSubTotalDiscounts()->whereNotIn('id', $perUnitSubTotalDiscountNewIds)->delete();

        $productUnit->perUnitSubTotalDiscounts()->upsert(
            $perUnitSubTotalDiscountArr,
            ['id'],
            [
                'ulid',
                'company_id',
                'branch_id',
                'purchase_order_id',
                'purchase_order_product_unit_id',
                'order',
                'discount_type',
                'amount',
            ]
        );

        return $productUnit->perUnitSubTotalDiscounts;
    }

    public function updateGlobalDiscount(PurchaseOrder $purchaseOrder, array $globalDiscountArr): Collection
    {
        $globalDiscountArr = collect($globalDiscountArr)->map(function ($globalDiscount) use ($purchaseOrder) {
            return [
                'id' => $globalDiscount['id'],
                'ulid' => $globalDiscount['ulid'],
                'company_id' => $purchaseOrder->company_id,
                'branch_id' => $purchaseOrder->branch_id,
                'purchase_order_id' => $purchaseOrder->id,
                'order' => $globalDiscount['order'],
                'discount_type' => $globalDiscount['discount_type'],
                'amount' => $globalDiscount['amount'],
            ];
        })->toArray();

        $globalDiscountNewIds = collect($globalDiscountArr)->pluck('id')->toArray();
        $purchaseOrder->globalDiscounts()->whereNotIn('id', $globalDiscountNewIds)->delete();

        $purchaseOrder->globalDiscounts()->upsert(
            $globalDiscountArr,
            ['id'],
            [
                'ulid',
                'company_id',
                'branch_id',
                'purchase_order_id',
                'order',
                'discount_type',
                'amount',
            ]
        );

        return $purchaseOrder->refresh()->globalDiscounts;
    }

    public function calculatePerUnitDiscountFromFreeArray(float $valueBeforeDiscount, array $discOrderArr, array $discTypeArr, array $discAmountArr)
    {
        $discountArr = [];
        for ($i = 0; $i < count($discOrderArr); $i++) {
            $discount = [
                'order' => $discOrderArr[$i],
                'discount_type' => $discTypeArr[$i],
                'amount' => $discAmountArr[$i],
            ];
            array_push($discountArr, $discount);
        }

        $valueAfterDiscount = $valueBeforeDiscount;
        foreach ($discountArr as $discount) {
            $discountType = $discount['discount_type'];

            if (gettype($discountType) == 'integer' || gettype($discountType) == 'string') {
                $discountType = DiscountType::resolveToEnum($discountType);
            }

            switch ($discountType) {
                case DiscountType::PER_UNIT_PERCENT_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount * (1 - $discount['amount']);
                    break;
                case DiscountType::PER_UNIT_NOMINAL_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount - $discount['amount'];
                    break;
            }
        }

        if (! $valueAfterDiscount) {
            $valueAfterDiscount = 0;
        }

        return $valueBeforeDiscount - $valueAfterDiscount;
    }

    public function calculatePerUnitDiscountFromNestedArray(float $valueBeforeDiscount, array $discountArr)
    {
        $valueAfterDiscount = $valueBeforeDiscount;
        for ($i = 0; $i < count($discountArr); $i++) {
            $discountType = $discountArr[$i]['discount_type'];

            if (gettype($discountType) == 'integer' || gettype($discountType) == 'string') {
                $discountType = DiscountType::resolveToEnum($discountType);
            }

            switch ($discountType) {
                case DiscountType::PER_UNIT_PERCENT_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount * (1 - $discountArr[$i]['amount']);
                    break;
                case DiscountType::PER_UNIT_NOMINAL_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount - $discountArr[$i]['amount'];
                    break;
            }
        }

        if (! $valueAfterDiscount) {
            $valueAfterDiscount = 0;
        }

        return $valueBeforeDiscount - $valueAfterDiscount;
    }

    public function calculatePerUnitSubTotalDiscountFromFreeArray(float $valueBeforeDiscount, array $discOrderArr, array $discTypeArr, array $discAmountArr)
    {
        $discountArr = [];
        for ($i = 0; $i < count($discOrderArr); $i++) {
            $discount = [
                'order' => $discOrderArr[$i],
                'discount_type' => $discTypeArr[$i],
                'amount' => $discAmountArr[$i],
            ];
            array_push($discountArr, $discount);
        }

        $valueAfterDiscount = $valueBeforeDiscount;
        foreach ($discountArr as $discount) {
            $discountType = $discount['discount_type'];

            if (gettype($discountType) == 'integer' || gettype($discountType) == 'string') {
                $discountType = DiscountType::resolveToEnum($discountType);
            }

            switch ($discountType) {
                case DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount * (1 - $discount['amount']);
                    break;
                case DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount - $discount['amount'];
                    break;
            }
        }

        if (! $valueAfterDiscount) {
            $valueAfterDiscount = 0;
        }

        return $valueBeforeDiscount - $valueAfterDiscount;
    }

    public function calculatePerUnitSubTotalDiscountFromNestedArray(float $valueBeforeDiscount, array $discountArr)
    {
        $valueAfterDiscount = $valueBeforeDiscount;
        for ($i = 0; $i < count($discountArr); $i++) {
            $discountType = $discountArr[$i]['discount_type'];

            if (gettype($discountType) == 'integer' || gettype($discountType) == 'string') {
                $discountType = DiscountType::resolveToEnum($discountType);
            }

            switch ($discountType) {
                case DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount * (1 - $discountArr[$i]['amount']);
                    break;
                case DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount - $discountArr[$i]['amount'];
                    break;
            }
        }

        if (! $valueAfterDiscount) {
            $valueAfterDiscount = 0;
        }

        return $valueBeforeDiscount - $valueAfterDiscount;
    }

    public function calculateGlobalDiscountFromFreeArray(float $valueBeforeDiscount, array $discOrderArr, array $discTypeArr, array $discAmountArr)
    {
        $discountArr = [];
        for ($i = 0; $i < count($discOrderArr); $i++) {
            $discount = [
                'order' => $discOrderArr[$i],
                'discount_type' => $discTypeArr[$i],
                'amount' => $discAmountArr[$i],
            ];
            array_push($discountArr, $discount);
        }

        $valueAfterDiscount = $valueBeforeDiscount;
        foreach ($discountArr as $discount) {
            $discountType = $discount['discount_type'];

            if (gettype($discountType) == 'integer' || gettype($discountType) == 'string') {
                $discountType = DiscountType::resolveToEnum($discountType);
            }

            switch ($discountType) {
                case DiscountType::GLOBAL_PERCENT_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount * (1 - $discount['amount']);
                    break;
                case DiscountType::GLOBAL_NOMINAL_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount - $discount['amount'];
                    break;
            }
        }

        if (! $valueAfterDiscount) {
            $valueAfterDiscount = 0;
        }

        return $valueBeforeDiscount - $valueAfterDiscount;
    }

    public function calculateGlobalDiscountFromNestedArray(float $valueBeforeDiscount, array $discountArr)
    {
        $valueAfterDiscount = $valueBeforeDiscount;
        foreach ($discountArr as $discount) {
            $discountType = $discount['discount_type'];

            if (gettype($discountType) == 'integer' || gettype($discountType) == 'string') {
                $discountType = DiscountType::resolveToEnum($discountType);
            }

            switch ($discountType) {
                case DiscountType::GLOBAL_PERCENT_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount * (1 - $discount['amount']);
                    break;
                case DiscountType::GLOBAL_NOMINAL_DISCOUNT:
                    $valueAfterDiscount = $valueAfterDiscount - $discount['amount'];
                    break;
            }
        }

        if (! $valueAfterDiscount) {
            $valueAfterDiscount = 0;
        }

        return $valueBeforeDiscount - $valueAfterDiscount;
    }

    public function updateSummary(PurchaseOrder $purchaseOrder, bool $useTransactions = true): bool
    {
        ! $useTransactions ?: DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $productUnits = $purchaseOrder->productUnits;
            foreach ($productUnits as $productUnit) {
                $productUnit->product_unit_amount_total = $productUnit->qty * $productUnit->product_unit_amount_per_unit;

                $priceWithPerUnitDiscount = $productUnit->product_unit_initial_price;
                foreach ($productUnit->perUnitDiscounts as $perUnitDiscount) {
                    if ($perUnitDiscount->discount_type == DiscountType::PER_UNIT_PERCENT_DISCOUNT) {
                        $priceWithPerUnitDiscount = $priceWithPerUnitDiscount * (1 - $perUnitDiscount->amount);
                    }

                    if ($perUnitDiscount->discount_type == DiscountType::PER_UNIT_NOMINAL_DISCOUNT) {
                        $priceWithPerUnitDiscount = $priceWithPerUnitDiscount - $perUnitDiscount->amount;
                    }
                }

                $productUnit->product_unit_per_unit_discount = $productUnit->product_unit_initial_price - $priceWithPerUnitDiscount;
                $productUnit->product_unit_sub_total = $productUnit->qty * ($productUnit->product_unit_initial_price - $productUnit->product_unit_per_unit_discount);

                $subTotalWithPerUnitSubTotalDiscount = $productUnit->product_unit_sub_total;
                foreach ($productUnit->perUnitSubTotalDiscounts as $perUnitSubTotalDiscount) {
                    if ($perUnitSubTotalDiscount->discount_type == DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT) {
                        $subTotalWithPerUnitSubTotalDiscount = $subTotalWithPerUnitSubTotalDiscount * (1 - $perUnitSubTotalDiscount->amount);
                    }

                    if ($perUnitSubTotalDiscount->discount_type == DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT) {
                        $subTotalWithPerUnitSubTotalDiscount = $subTotalWithPerUnitSubTotalDiscount - $perUnitSubTotalDiscount->amount;
                    }
                }

                $productUnit->product_unit_per_unit_sub_total_discount = $productUnit->product_unit_sub_total - $subTotalWithPerUnitSubTotalDiscount;
                $productUnit->product_unit_total = $productUnit->product_unit_sub_total - $productUnit->product_unit_per_unit_sub_total_discount;

                $productUnit->save();
            }

            $purchaseOrder->total = $productUnits->sum('product_unit_sub_total');
            $purchaseOrder->grand_total = $productUnits->sum('product_unit_total');
            $purchaseOrder->save();

            $productUnits = $purchaseOrder->productUnits;
            foreach ($productUnits as $productUnit) {
                $productUnit->product_unit_sub_total = $productUnit->qty * $productUnit->product_unit_initial_price;

                $totalWithGlobalDiscount = $productUnit->product_unit_total;
                foreach ($purchaseOrder->globalDiscounts as $purchaseOrderDiscount) {
                    if ($purchaseOrderDiscount->discount_type == DiscountType::GLOBAL_PERCENT_DISCOUNT) {
                        $totalWithGlobalDiscount = $totalWithGlobalDiscount * (1 - $purchaseOrderDiscount->amount);
                    }

                    if ($purchaseOrderDiscount->discount_type == DiscountType::GLOBAL_NOMINAL_DISCOUNT) {
                        $totalWithGlobalDiscount = $totalWithGlobalDiscount - ($purchaseOrderDiscount->amount / $purchaseOrder->grand_total);
                    }
                }

                $productUnit->product_unit_global_discount = $productUnit->product_unit_total - $totalWithGlobalDiscount;

                $grandTotal = $productUnit->product_unit_total - $productUnit->product_unit_global_discount;

                $productUnit->product_unit_final_price = $grandTotal / $productUnit->qty;

                if ($productUnit->vat_status == VatStatus::NON_VAT) {
                    $productUnit->tax_base = $grandTotal;
                    $productUnit->vat_amount = 0;
                }

                if ($productUnit->vat_status == VatStatus::INCLUDE_VAT) {
                    $productUnit->tax_base = $grandTotal / $productUnit->vat_rate;
                    $productUnit->vat_amount = $grandTotal - $productUnit->tax_base;
                }

                if ($productUnit->vat_status == VatStatus::EXCLUDE_VAT) {
                    $productUnit->tax_base = $grandTotal;
                    $productUnit->vat_amount = $grandTotal * $productUnit->vat_rate;
                }

                $productUnit->save();
            }

            ! $useTransactions ?: DB::commit();

            return true;
        } catch (Exception $e) {
            ! $useTransactions ?: DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function insertArr(array $originalArray, string $insertAfterKey, array $insertArray): array
    {
        $insertIndex = array_search($insertAfterKey, array_keys($originalArray)) + 1;

        $modifiedArray = array_slice($originalArray, 0, $insertIndex, true) +
                            $insertArray +
                            array_slice($originalArray, $insertIndex, null, true);

        return $modifiedArray;
    }
}
