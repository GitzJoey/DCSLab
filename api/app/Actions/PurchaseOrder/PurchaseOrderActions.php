<?php

namespace App\Actions\PurchaseOrder;

use App\Enums\DiscountType;
use App\Enums\VatStatus;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDiscount;
use App\Models\PurchaseOrderProductUnit;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PurchaseOrderActions
{
    use CacheHelper;

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

            foreach ($purchaseOrderArr['global_discount'] as $globalDiscount) {
                $discountType = $globalDiscount['discount_type'];

                if ($discountType == DiscountType::GLOBAL_PERCENT_DISCOUNT || $discountType == DiscountType::GLOBAL_NOMINAL_DISCOUNT) {
                    $purchaseOrderDiscount = new PurchaseOrderDiscount();
                    $purchaseOrderDiscount->company_id = $purchaseOrder->company_id;
                    $purchaseOrderDiscount->branch_id = $purchaseOrder->branch_id;
                    $purchaseOrderDiscount->purchase_order_id = $purchaseOrder->id;
                    $purchaseOrderDiscount->discount_type = $discountType;
                    $purchaseOrderDiscount->amount = $globalDiscount['amount'];
                    $purchaseOrderDiscount->save();
                }
            }

            foreach ($productUnitArr as $productUnit) {
                $purchaseOrderProductUnit = new PurchaseOrderProductUnit();
                $purchaseOrderProductUnit->company_id = $purchaseOrder->company_id;
                $purchaseOrderProductUnit->branch_id = $purchaseOrder->branch_id;
                $purchaseOrderProductUnit->purchase_order_id = $purchaseOrder->id;
                $purchaseOrderProductUnit->product_id = ProductUnit::find($productUnit['product_unit_id'])->product_id;
                $purchaseOrderProductUnit->product_unit_id = $productUnit['product_unit_id'];
                $purchaseOrderProductUnit->qty = $productUnit['qty'];
                $purchaseOrderProductUnit->product_unit_amount_per_unit = $productUnit['product_unit_amount_per_unit'];
                $purchaseOrderProductUnit->product_unit_initial_price = $productUnit['product_unit_initial_price'];
                $purchaseOrderProductUnit->vat_status = $productUnit['vat_status'];
                $purchaseOrderProductUnit->vat_rate = $productUnit['vat_rate'];
                $purchaseOrderProductUnit->remarks = $productUnit['remarks'];
                $purchaseOrderProductUnit->save();

                foreach ($productUnit['per_unit_discount'] as $perUnitDiscount) {
                    $discountType = $perUnitDiscount['discount_type'];

                    if ($discountType == DiscountType::PER_UNIT_PERCENT_DISCOUNT->value || $discountType == DiscountType::PER_UNIT_NOMINAL_DISCOUNT->value) {
                        $purchaseOrderDiscount = new PurchaseOrderDiscount();
                        $purchaseOrderDiscount->company_id = $purchaseOrder->company_id;
                        $purchaseOrderDiscount->branch_id = $purchaseOrder->branch_id;
                        $purchaseOrderDiscount->purchase_order_id = $purchaseOrder->id;
                        $purchaseOrderDiscount->purchase_order_product_unit_id = $purchaseOrderProductUnit->id;
                        $purchaseOrderDiscount->discount_type = $discountType;
                        $purchaseOrderDiscount->amount = $perUnitDiscount['amount'];
                        $purchaseOrderDiscount->save();
                    }
                }

                foreach ($productUnit['per_unit_sub_total_discount'] as $perUnitSubTotalDiscount) {
                    $discountType = $perUnitSubTotalDiscount['discount_type'];

                    if ($discountType == DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT->value || $discountType == DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT->value) {
                        $purchaseOrderDiscount = new PurchaseOrderDiscount();
                        $purchaseOrderDiscount->company_id = $purchaseOrder->company_id;
                        $purchaseOrderDiscount->branch_id = $purchaseOrder->branch_id;
                        $purchaseOrderDiscount->purchase_order_id = $purchaseOrder->id;
                        $purchaseOrderDiscount->purchase_order_product_unit_id = $purchaseOrderProductUnit->id;
                        $purchaseOrderDiscount->discount_type = $discountType;
                        $purchaseOrderDiscount->amount = $perUnitSubTotalDiscount['amount'];
                        $purchaseOrderDiscount->save();
                    }
                }
            }

            $this->updateSummary($purchaseOrder, false);

            DB::commit();

            $this->flushCache();

            return $purchaseOrder;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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

            $query = count($with) != 0 ? PurchaseOrder::with($with) : PurchaseOrder::with('company', 'branch', 'supplier', 'purchaseOrderDiscounts', 'purchaseOrderProductUnits.productUnitPerUnitDiscounts', 'purchaseOrderProductUnits.productUnitPerUnitSubTotalDiscounts');

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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)' : ' (DB)'));
        }
    }

    public function read(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $purchaseOrder->with('company', 'branch', 'supplier', 'purchaseOrderDiscounts', 'purchaseOrderProductUnits.productUnitPerUnitDiscounts', 'purchaseOrderProductUnits.productUnitPerUnitSubTotalDiscounts')->where('id', '=', $purchaseOrder->id)->first();
    }

    public function update(
        PurchaseOrder $purchaseOrder,
        array $purchaseOrderArr,
        array $productUnitArr
    ): PurchaseOrder {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseOrder->update([
                'invoice_code' => $purchaseOrderArr['invoice_code'],
                'invoice_date' => $purchaseOrderArr['invoice_date'],
                'supplier_id' => $purchaseOrderArr['supplier_id'],
                'shipping_date' => $purchaseOrderArr['shipping_date'],
                'shipping_address' => $purchaseOrderArr['shipping_address'],
                'remarks' => $purchaseOrderArr['remarks'],
                'status' => $purchaseOrderArr['status'],
            ]);

            $globalDiscountArr = collect($purchaseOrderArr['global_discount'])->map(function ($globalDiscount) use ($purchaseOrder) {
                return [
                    'id' => $globalDiscount['id'],
                    'ulid' => $globalDiscount['id'] == null ? Str::ulid()->generate() : PurchaseOrderDiscount::find($globalDiscount['id'])->ulid,
                    'company_id' => $purchaseOrder->company_id,
                    'branch_id' => $purchaseOrder->branch_id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'discount_type' => $globalDiscount['discount_type'],
                    'amount' => $globalDiscount['amount'],
                ];
            })->toArray();

            $globalDiscountNewIds = [];
            foreach ($globalDiscountArr as $globalDiscount) {
                array_push($globalDiscountNewIds, $globalDiscount['id']);
            }

            $globalDiscountOldIds = $purchaseOrder->purchaseOrderDiscounts()->pluck('id')->toArray();

            $deletedGlobalDiscountIds = array_diff($globalDiscountOldIds, $globalDiscountNewIds);
            foreach ($deletedGlobalDiscountIds as $deletedGlobalDiscountId) {
                $purchaseOrder->purchaseOrderDiscounts()->where('id', '=', $deletedGlobalDiscountId)->delete();
            }

            $purchaseOrder->purchaseOrderDiscounts()->upsert(
                $globalDiscountArr,
                ['id'],
                [
                    'ulid',
                    'company_id',
                    'branch_id',
                    'purchase_order_id',
                    'discount_type',
                    'amount',
                ]
            );

            $productUnitArr = collect($productUnitArr)->map(function ($productUnit) use ($purchaseOrder) {
                return [
                    'id' => $productUnit['id'],
                    'ulid' => $productUnit['id'] == null ? Str::ulid()->generate() : PurchaseOrderProductUnit::find($productUnit['id'])->ulid,
                    'company_id' => $purchaseOrder->company_id,
                    'branch_id' => $purchaseOrder->branch_id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => ProductUnit::find($productUnit['product_unit_id'])->product_id,
                    'product_unit_id' => $productUnit['product_unit_id'],
                    'qty' => $productUnit['qty'],
                    'product_unit_amount_per_unit' => $productUnit['product_unit_amount_per_unit'],
                    'product_unit_initial_price' => $productUnit['product_unit_initial_price'],
                    'vat_status' => $productUnit['vat_status'],
                    'vat_rate' => $productUnit['vat_rate'],
                    'remarks' => $productUnit['remarks'],
                ];
            })->toArray();

            $productUnitNewIds = [];
            foreach ($productUnitArr as $purchaseOrderProductUnit) {
                array_push($productUnitNewIds, $purchaseOrderProductUnit['id']);
            }

            $productUnitOldIds = $purchaseOrder->purchaseOrderProductUnits()->pluck('id')->toArray();

            $deletedProductUnitIds = array_diff($productUnitOldIds, $productUnitNewIds);
            foreach ($deletedProductUnitIds as $deletedProductUnitId) {
                $purchaseOrder->purchaseOrderProductUnits()->where('id', '=', $deletedProductUnitId)->delete();
            }

            $purchaseOrder->purchaseOrderProductUnits()->upsert(
                $productUnitArr,
                ['id'],
                [
                    'ulid',
                    'company_id',
                    'branch_id',
                    'purchase_order_id',
                    'product_id',
                    'product_unit_id',
                    'qty',
                    'product_unit_amount_per_unit',
                    'product_unit_initial_price',
                    'vat_status',
                    'vat_rate',
                    'remarks',
                ]
            );

            $this->updateSummary($purchaseOrder, false);

            DB::commit();

            $this->flushCache();

            return $purchaseOrder;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        return 'PO-'.date('Ymd').'-'.str_pad(PurchaseOrder::max('id') + 1, 6, '0', STR_PAD_LEFT);
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $query = PurchaseOrder::where('company_id', $companyId)->where('invoice_code', $code);
        if ($exceptId) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function updateSummary(PurchaseOrder $purchaseOrder, bool $useTransactions = true): bool
    {
        ! $useTransactions ?: DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseOrderProductUnits = $purchaseOrder->purchaseOrderProductUnits;
            foreach ($purchaseOrderProductUnits as $purchaseOrderProductUnit) {
                $purchaseOrderProductUnit->product_unit_amount_total = $purchaseOrderProductUnit->qty * $purchaseOrderProductUnit->product_unit_amount_per_unit;

                $priceWithPerUnitDiscount = $purchaseOrderProductUnit->product_unit_initial_price;
                foreach ($purchaseOrderProductUnit->productUnitPerUnitDiscounts as $productUnitPerUnitDiscount) {
                    if ($productUnitPerUnitDiscount->discount_type == DiscountType::PER_UNIT_PERCENT_DISCOUNT) {
                        $priceWithPerUnitDiscount = $priceWithPerUnitDiscount * (1 - $productUnitPerUnitDiscount->amount);
                    }

                    if ($productUnitPerUnitDiscount->discount_type == DiscountType::PER_UNIT_NOMINAL_DISCOUNT) {
                        $priceWithPerUnitDiscount = $priceWithPerUnitDiscount - $productUnitPerUnitDiscount->amount;
                    }
                }

                $purchaseOrderProductUnit->product_unit_per_unit_discount = $purchaseOrderProductUnit->product_unit_initial_price - $priceWithPerUnitDiscount;
                $purchaseOrderProductUnit->product_unit_sub_total = $purchaseOrderProductUnit->qty * ($purchaseOrderProductUnit->product_unit_initial_price - $purchaseOrderProductUnit->product_unit_per_unit_discount);

                $subTotalWithPerUnitSubTotalDiscount = $purchaseOrderProductUnit->product_unit_sub_total;
                foreach ($purchaseOrderProductUnit->productUnitPerUnitSubTotalDiscounts as $productUnitPerUnitSubTotalDiscount) {
                    if ($productUnitPerUnitSubTotalDiscount->discount_type == DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT) {
                        $subTotalWithPerUnitSubTotalDiscount = $subTotalWithPerUnitSubTotalDiscount * (1 - $productUnitPerUnitSubTotalDiscount->amount);
                    }

                    if ($productUnitPerUnitSubTotalDiscount->discount_type == DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT) {
                        $subTotalWithPerUnitSubTotalDiscount = $subTotalWithPerUnitSubTotalDiscount - $productUnitPerUnitSubTotalDiscount->amount;
                    }
                }

                $purchaseOrderProductUnit->product_unit_per_unit_sub_total_discount = $purchaseOrderProductUnit->product_unit_sub_total - $subTotalWithPerUnitSubTotalDiscount;
                $purchaseOrderProductUnit->product_unit_total = $purchaseOrderProductUnit->product_unit_sub_total - $purchaseOrderProductUnit->product_unit_per_unit_sub_total_discount;

                $purchaseOrderProductUnit->save();
            }

            $purchaseOrder->total = $purchaseOrderProductUnits->sum('product_unit_sub_total');
            $purchaseOrder->grand_total = $purchaseOrderProductUnits->sum('product_unit_total');
            $purchaseOrder->save();

            $purchaseOrderProductUnits = $purchaseOrder->purchaseOrderProductUnits;
            foreach ($purchaseOrderProductUnits as $purchaseOrderProductUnit) {
                $purchaseOrderProductUnit->product_unit_sub_total = $purchaseOrderProductUnit->qty * $purchaseOrderProductUnit->product_unit_initial_price;

                $totalWithGlobalDiscount = $purchaseOrderProductUnit->product_unit_total;
                foreach ($purchaseOrder->purchaseOrderDiscounts as $purchaseOrderDiscount) {
                    if ($purchaseOrderDiscount->discount_type == DiscountType::GLOBAL_PERCENT_DISCOUNT) {
                        $totalWithGlobalDiscount = $totalWithGlobalDiscount * (1 - $purchaseOrderDiscount->amount);
                    }

                    if ($purchaseOrderDiscount->discount_type == DiscountType::GLOBAL_NOMINAL_DISCOUNT) {
                        $totalWithGlobalDiscount = $totalWithGlobalDiscount - ($purchaseOrderDiscount->amount / $purchaseOrder->grand_total);
                    }
                }

                $purchaseOrderProductUnit->product_unit_global_discount = $purchaseOrderProductUnit->product_unit_total - $totalWithGlobalDiscount;

                $grandTotal = $purchaseOrderProductUnit->product_unit_total - $purchaseOrderProductUnit->product_unit_global_discount;

                $purchaseOrderProductUnit->product_unit_final_price = $grandTotal / $purchaseOrderProductUnit->qty;

                if ($purchaseOrderProductUnit->vat_status == VatStatus::NON_VAT) {
                    $purchaseOrderProductUnit->tax_base = $grandTotal;
                    $purchaseOrderProductUnit->vat_amount = 0;
                }

                if ($purchaseOrderProductUnit->vat_status == VatStatus::INCLUDE_VAT) {
                    $purchaseOrderProductUnit->tax_base = $grandTotal / $purchaseOrderProductUnit->vat_rate;
                    $purchaseOrderProductUnit->vat_amount = $grandTotal - $purchaseOrderProductUnit->tax_base;
                }

                if ($purchaseOrderProductUnit->vat_status == VatStatus::EXCLUDE_VAT) {
                    $purchaseOrderProductUnit->tax_base = $grandTotal;
                    $purchaseOrderProductUnit->vat_amount = $grandTotal * $purchaseOrderProductUnit->vat_rate;
                }

                $purchaseOrderProductUnit->save();
            }

            ! $useTransactions ?: DB::commit();

            return true;
        } catch (Exception $e) {
            ! $useTransactions ?: DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }
}
