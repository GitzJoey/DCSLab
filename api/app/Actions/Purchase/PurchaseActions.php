<?php

namespace App\Actions\Purchase;

use App\Models\Company;
use App\Models\Purchase;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): Purchase
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchase = new Purchase();
            $purchase->company_id = $data['company_id'];
            $purchase->branch_id = $data['branch_id'];
            $purchase->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $purchase->date = $data['date'];
            $purchase->due_days = $data['due_days'];
            $purchase->warehouse_id = $data['warehouse_id'];
            $purchase->supplier_id = $data['supplier_id'];
            $purchase->purchase_order_id = $data['purchase_order_id'];
            $purchase->delivery_note_reference = $data['delivery_note_reference'];
            $purchase->purchase_tax_invoice_number = $data['purchase_tax_invoice_number'];
            $purchase->purchase_tax_invoice_vat_base = $data['purchase_tax_invoice_vat_base'];
            $purchase->purchase_tax_invoice_vat = $data['purchase_tax_invoice_vat'];
            $purchase->return_tax_invoice_number = $data['return_tax_invoice_number'];
            $purchase->return_tax_invoice_vat_base = $data['return_tax_invoice_vat_base'];
            $purchase->return_tax_invoice_vat = $data['return_tax_invoice_vat'];
            $purchase->remarks = $data['remarks'];
            $purchase->is_posted = $data['is_posted'];
            $purchase->purchase_total = $data['purchase_total'];
            $purchase->purchase_global_discount_rate = $data['purchase_global_discount_rate'];
            $purchase->purchase_global_discount_fixed = $data['purchase_global_discount_fixed'];
            $purchase->purchase_additional_cost = $data['purchase_additional_cost'];
            $purchase->purchase_rounding = $data['purchase_rounding'];
            $purchase->purchase_grand_total = $data['purchase_grand_total'];
            $purchase->return_total = $data['return_total'];
            $purchase->return_global_discount_rate = $data['return_global_discount_rate'];
            $purchase->return_global_discount_fixed = $data['return_global_discount_fixed'];
            $purchase->return_rounding = $data['return_rounding'];
            $purchase->return_grand_total = $data['return_grand_total'];
            $purchase->amount_due = $data['amount_due'];
            $purchase->amount_paid_by_purchase_order_down_payment = $data['amount_paid_by_purchase_order_down_payment'];
            $purchase->amount_paid_by_purchase_return = $data['amount_paid_by_purchase_return'];
            $purchase->amount_paid_before_invoice = $data['amount_paid_before_invoice'];
            $purchase->amount_paid_on_invoice = $data['amount_paid_on_invoice'];
            $purchase->amount_paid_after_invoice = $data['amount_paid_after_invoice'];
            $purchase->amount_paid_total = $data['amount_paid_total'];
            $purchase->amount_due = $data['amount_due'];
            $purchase->is_paid_off = $data['is_paid_off'];
            $purchase->is_valid = $data['is_valid'];
            $purchase->save();

            DB::commit();

            $this->flushCache();

            return $purchase;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function readAnyQuery(
        ?bool $withTrashed,

        ?string $search,
        int $companyId,

        ?int $limit
    ) {
        $query = Purchase::select('purchases.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'purchases.company_id')
            ->where(function ($query) use ($withTrashed, $search, $companyId) {
                if ($withTrashed == true) {
                    $query->withTrashed();
                } else {
                    $query->withoutTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                $query->whereCompanyId($companyId);
            });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('purchases.date', 'asc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query;
    }

    public function readAny(
        ?bool $useCache,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,

        bool $paginate,
        ?int $page,
        ?int $perPage,
        ?int $limit
    ): Paginator|Collection {
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheSearch = empty($search) ? '[empty]' : $search;
            $cacheKey = 'readAny_'.$companyId.'-'.$cacheSearch.'-'.$paginate.'-'.$page.'-'.$perPage;
            if ($useCache === true) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            $query = $this->readAnyQuery(
                withTrashed: $withTrashed,
                search: $search,
                companyId: $companyId,
                limit: $paginate ? null : $limit
            );

            if ($paginate) {
                $result = $query->paginate(perPage: $perPage, page: $page);
            } else {
                $result = $query->get();
            }

            $recordsCount = $result->count();

            if ($useCache === true) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
        }
    }

    public function read(Purchase $purchase): Purchase
    {
        return $purchase->load('company')->first();
    }

    public function getAllActivePurchase(
        ?array $with,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,
        ?array $includeIds,

        ?int $limit
    ) {
        $timer_start = microtime(true);

        try {
            $query = $this->readAnyQuery(
                withTrashed: $withTrashed,

                search: $search,
                companyId: $companyId,

                limit: $limit
            );

            if ($includeIds) {
                $query = $query->orWhereIn('id', $includeIds);

                $orders = $query->getQuery()->orders;
                $query->reorder();
                $query->orderByRaw('FIELD(id, '.implode(',', $includeIds).') desc');
                if (! empty($orders)) {
                    foreach ($orders as $order) {
                        $query->orderBy($order['column'], $order['direction']);
                    }
                }
            }

            return $query->get();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchase->code = $this->generateUniqueCode($purchase->company_id, $data['code'], $purchase->id);
            $purchase->date = $data['date'];
            $purchase->due_days = $data['due_days'];
            $purchase->warehouse_id = $data['warehouse_id'];
            $purchase->supplier_id = $data['supplier_id'];
            $purchase->purchase_order_id = $data['purchase_order_id'];
            $purchase->delivery_note_reference = $data['delivery_note_reference'];
            $purchase->purchase_tax_invoice_number = $data['purchase_tax_invoice_number'];
            $purchase->purchase_tax_invoice_vat_base = $data['purchase_tax_invoice_vat_base'];
            $purchase->purchase_tax_invoice_vat = $data['purchase_tax_invoice_vat'];
            $purchase->return_tax_invoice_number = $data['return_tax_invoice_number'];
            $purchase->return_tax_invoice_vat_base = $data['return_tax_invoice_vat_base'];
            $purchase->return_tax_invoice_vat = $data['return_tax_invoice_vat'];
            $purchase->remarks = $data['remarks'];
            $purchase->is_posted = $data['is_posted'];
            $purchase->purchase_total = $data['purchase_total'];
            $purchase->purchase_global_discount_rate = $data['purchase_global_discount_rate'];
            $purchase->purchase_global_discount_fixed = $data['purchase_global_discount_fixed'];
            $purchase->purchase_additional_cost = $data['purchase_additional_cost'];
            $purchase->purchase_rounding = $data['purchase_rounding'];
            $purchase->purchase_grand_total = $data['purchase_grand_total'];
            $purchase->return_total = $data['return_total'];
            $purchase->return_global_discount_rate = $data['return_global_discount_rate'];
            $purchase->return_global_discount_fixed = $data['return_global_discount_fixed'];
            $purchase->return_rounding = $data['return_rounding'];
            $purchase->return_grand_total = $data['return_grand_total'];
            $purchase->amount_due = $data['amount_due'];
            $purchase->amount_paid_by_purchase_order_down_payment = $data['amount_paid_by_purchase_order_down_payment'];
            $purchase->amount_paid_by_purchase_return = $data['amount_paid_by_purchase_return'];
            $purchase->amount_paid_before_invoice = $data['amount_paid_before_invoice'];
            $purchase->amount_paid_on_invoice = $data['amount_paid_on_invoice'];
            $purchase->amount_paid_after_invoice = $data['amount_paid_after_invoice'];
            $purchase->amount_paid_total = $data['amount_paid_total'];
            $purchase->amount_due = $data['amount_due'];
            $purchase->is_paid_off = $data['is_paid_off'];
            $purchase->is_valid = $data['is_valid'];
            $purchase->save();

            DB::commit();

            $this->flushCache();

            return $purchase->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Purchase $purchase): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchase->delete();

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

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->purchases()->withTrashed()->count() + 1 + $tryCount;
                $code = 'WH'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = Purchase::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
