<?php

namespace App\Actions\Sale;

use App\Models\Company;
use App\Models\Sale;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SaleActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): Sale
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $sale = new Sale();
            $sale->company_id = $data['company_id'];
            $sale->branch_id = $data['branch_id'];
            $sale->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $sale->date = $data['date'];
            $sale->due_days = $data['due_days'];
            $sale->warehouse_id = $data['warehouse_id'];
            $sale->customer_id = $data['customer_id'];
            $sale->delivery_note_reference = $data['delivery_note_reference'];

            $sale->tax_invoice_number = $data['tax_invoice_number'];
            $sale->tax_invoice_vat_base = $data['tax_invoice_vat_base'];
            $sale->tax_invoice_vat = $data['tax_invoice_vat'];
            $sale->return_tax_invoice_number = $data['return_tax_invoice_number'];
            $sale->return_tax_invoice_vat_base = $data['return_tax_invoice_vat_base'];
            $sale->return_tax_invoice_vat = $data['return_tax_invoice_vat'];

            $sale->remarks = $data['remarks'];
            $sale->is_posted = $data['is_posted'];

            $sale->total = $data['total'];
            $sale->global_discount_rate = $data['global_discount_rate'];
            $sale->global_discount_fixed = $data['global_discount_fixed'];
            $sale->additional_cost = $data['additional_cost'];
            $sale->rounding = $data['rounding'];
            $sale->grand_total = $data['grand_total'];

            $sale->return_total = $data['return_total'];
            $sale->return_global_discount_rate = $data['return_global_discount_rate'];
            $sale->return_global_discount_fixed = $data['return_global_discount_fixed'];
            $sale->return_rounding = $data['return_rounding'];
            $sale->return_grand_total = $data['return_grand_total'];

            $sale->amount_due = $data['amount_due'];
            $sale->amount_paid_by_sale_order_down_payment = $data['amount_paid_by_sale_order_down_payment'];
            $sale->amount_paid_by_sale_return = $data['amount_paid_by_sale_return'];
            $sale->amount_paid_before_invoice = $data['amount_paid_before_invoice'];
            $sale->amount_paid_on_invoice = $data['amount_paid_on_invoice'];
            $sale->amount_paid_after_invoice = $data['amount_paid_after_invoice'];
            $sale->amount_paid_total = $data['amount_paid_total'];
            $sale->amount_due = $data['amount_due'];

            $sale->is_paid_off = $data['is_paid_off'];
            $sale->is_valid = $data['is_valid'];
            $sale->save();

            DB::commit();

            $this->flushCache();

            return $sale;
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
        $query = Sale::select('sales.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'sales.company_id')
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
            ->orderBy('sales.date', 'dsc');

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

    public function read(Sale $sale): Sale
    {
        return $sale->load('company')->first();
    }

    public function getAllActiveSale(
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

    public function update(Sale $sale, array $data): Sale
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $sale->code = $this->generateUniqueCode($sale->company_id, $data['code'], $sale->id);
            $sale->date = $data['date'];
            $sale->due_days = $data['due_days'];
            $sale->warehouse_id = $data['warehouse_id'];
            $sale->customer_id = $data['customer_id'];
            $sale->delivery_note_reference = $data['delivery_note_reference'];

            $sale->tax_invoice_number = $data['tax_invoice_number'];
            $sale->tax_invoice_vat_base = $data['tax_invoice_vat_base'];
            $sale->tax_invoice_vat = $data['tax_invoice_vat'];
            $sale->return_tax_invoice_number = $data['return_tax_invoice_number'];
            $sale->return_tax_invoice_vat_base = $data['return_tax_invoice_vat_base'];
            $sale->return_tax_invoice_vat = $data['return_tax_invoice_vat'];

            $sale->remarks = $data['remarks'];
            $sale->is_posted = $data['is_posted'];

            $sale->total = $data['total'];
            $sale->global_discount_rate = $data['global_discount_rate'];
            $sale->global_discount_fixed = $data['global_discount_fixed'];
            $sale->additional_cost = $data['additional_cost'];
            $sale->rounding = $data['rounding'];
            $sale->grand_total = $data['grand_total'];

            $sale->return_total = $data['return_total'];
            $sale->return_global_discount_rate = $data['return_global_discount_rate'];
            $sale->return_global_discount_fixed = $data['return_global_discount_fixed'];
            $sale->return_rounding = $data['return_rounding'];
            $sale->return_grand_total = $data['return_grand_total'];

            $sale->amount_due = $data['amount_due'];
            $sale->amount_paid_by_sale_order_down_payment = $data['amount_paid_by_sale_order_down_payment'];
            $sale->amount_paid_by_sale_return = $data['amount_paid_by_sale_return'];
            $sale->amount_paid_before_invoice = $data['amount_paid_before_invoice'];
            $sale->amount_paid_on_invoice = $data['amount_paid_on_invoice'];
            $sale->amount_paid_after_invoice = $data['amount_paid_after_invoice'];
            $sale->amount_paid_total = $data['amount_paid_total'];
            $sale->amount_due = $data['amount_due'];

            $sale->is_paid_off = $data['is_paid_off'];
            $sale->is_valid = $data['is_valid'];
            $sale->save();

            DB::commit();

            $this->flushCache();

            return $sale->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Sale $sale): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $sale->delete();

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
                $count = $company->sales()->withTrashed()->count() + 1 + $tryCount;
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
        $result = Sale::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
