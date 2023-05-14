<?php

namespace App\Actions\PurchaseOrder;

use App\Enums\DiscountType;
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

                    if ($discountType == DiscountType::PER_UNIT_PERCENT_DISCOUNT || $discountType == DiscountType::PER_UNIT_NOMINAL_DISCOUNT) {
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

                    if ($discountType == DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT || $discountType == DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT) {
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

            $query = count($with) != 0 ? PurchaseOrder::with($with) : PurchaseOrder::with('company', 'branch', 'supplier', 'purchaseOrderDiscounts', 'purchaseOrderProductUnits.productUnitPerUnitDiscount', 'purchaseOrderProductUnits.productUnitPerUnitSubTotalDiscount');

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
        return $purchaseOrder->with('company', 'branch', 'supplier', 'purchaseOrderDiscounts', 'purchaseOrderProductUnits.productUnitPerUnitDiscount', 'purchaseOrderProductUnits.productUnitPerUnitSubTotalDiscount')->where('id', '=', $purchaseOrder->id)->first();
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

            // $purchaseOrderGlobalDiscountNewIds = [];
            // foreach ($purchaseOrderArr['global_discount'] as $globalDiscount) {
            //     array_push($purchaseOrderProductUnitNewIds, $purchaseOrderProductUnit['id']);
            // }

            $purchaseOrderProductUnitArr = collect($productUnitArr)->map(function ($productUnit) use ($purchaseOrder) {
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

            $purchaseOrderProductUnitNewIds = [];
            foreach ($purchaseOrderProductUnitArr as $purchaseOrderProductUnit) {
                array_push($purchaseOrderProductUnitNewIds, $purchaseOrderProductUnit['id']);
            }

            $purchaseOrderProductUnitOldIds = $purchaseOrder->purchaseOrderProductUnits()->pluck('id')->toArray();

            $deletedPurchaseOrderProductUnitIds = [];
            $deletedPurchaseOrderProductUnitIds = array_diff($purchaseOrderProductUnitOldIds, $purchaseOrderProductUnitNewIds);

            foreach ($deletedPurchaseOrderProductUnitIds as $deletedPurchaseOrderProductUnitId) {
                $purchaseOrderProductUnit = $purchaseOrder->purchaseOrderProductUnits()->where('id', '=', $deletedPurchaseOrderProductUnitId);
                $purchaseOrderProductUnit->delete();
            }

            $purchaseOrder->purchaseOrderProductUnits()->upsert(
                $purchaseOrderProductUnitArr,
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
}
