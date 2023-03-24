<?php

namespace App\Actions\PuchaseOrder;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProductUnit;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderActions
{
    use CacheHelper;

    public function __construct()
    {
    }

    public function create(
        array $purchaseOrderArr,
        array $productUnitArr,
        array $discountArr
    ): PurchaseOrder {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->company_id = $purchaseOrderArr['company_id'];
            $purchaseOrder->branch_id = $purchaseOrderArr['branch_id'];
            $purchaseOrder->invoice_code = $purchaseOrderArr['invoice_code'];
            $purchaseOrder->invoice_date = $purchaseOrderArr['invoice_date'];
            $purchaseOrder->shipping_date = $purchaseOrderArr['shipping_date'];
            $purchaseOrder->shipping_address = $purchaseOrderArr['shipping_address'];
            $purchaseOrder->supplier_id = $purchaseOrderArr['supplier_id'];
            $purchaseOrder->remarks = $purchaseOrderArr['remarks'];
            $purchaseOrder->status = $purchaseOrderArr['status'];
            $purchaseOrder->save();

            $productUnits = [];
            foreach ($productUnitArr as $productUnit) {
                array_push($productUnits, new PurchaseOrderProductUnit([
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
                    'vat_amount' => $productUnit['vat_amount'],
                    'remarks' => $productUnit['remarks'],
                ]));
            }

            if (! empty($productUnits)) {
                $purchaseOrder->purchaseOrderProductUnits()->saveMany($productUnits);
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

            $query = count($with) != 0 ? PurchaseOrder::with($with) : PurchaseOrder::with('company', 'branch', 'supplier', 'purchaseOrderDiscounts', 'purchaseOrderProductUnits.purchaseOrderDiscounts');

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
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $query->paginate($perPage, ['*'], 'page', $page);
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
        return $purchaseOrder->with('company', 'branch', 'supplier', 'purchaseOrderProductUnits.purchaseOrderDiscounts', 'purchaseOrderDiscounts')->where('id', '=', $purchaseOrder->id)->first();
    }

    public function update(
        PurchaseOrder $purchaseOrder,
        array $purchaseOrderArr,
        array $productUnitArr,
        array $discountArr
    ): PurchaseOrder {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseOrder->update([
                'invoice_code' => $purchaseOrder->invoice_code,
                'invoice_date' => $purchaseOrder->invoice_date,
                'shipping_date' => $purchaseOrder->shipping_date,
                'shipping_address' => $purchaseOrder->shipping_address,
                'supplier_id' => $purchaseOrder->supplier_id,
                'remarks' => $purchaseOrder->remarks,
                'status' => $purchaseOrder->status,
            ]);

            $purchaseOrderProductUnits = collect($purchaseOrder->purchaseOrderProductUnits)->map(function ($productUnit) {
                return [
                    'id' => $productUnit->id,
                    'company_id' => $productUnit->company_id,
                    'branch_id' => $productUnit->branch_id,
                    'purchase_order_id' => $productUnit->purchase_order_id,
                    'product_id' => $productUnit->product_id,
                    'product_unit_id' => $productUnit->product_unit_id,
                    'qty' => $productUnit->qty,
                    'product_unit_amount_per_unit' => $productUnit->product_unit_amount_per_unit,
                    'product_unit_initial_price' => $productUnit->product_unit_initial_price,
                    'vat_status' => $productUnit->vat_status,
                    'vat_rate' => $productUnit->vat_rate,
                    'vat_amount' => $productUnit->vat_amount,
                    'remarks' => $productUnit->remarks,
                ];
            });

            DB::table('purchase_order_product_units')->upsert($purchaseOrderProductUnits->toArray(), ['id'], [
                'product_id',
                'product_unit_id',
                'qty',
                'product_unit_amount_per_unit',
                'product_unit_initial_price',
                'vat_status',
                'vat_rate',
                'vat_amount',
                'remarks',
            ]);

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
