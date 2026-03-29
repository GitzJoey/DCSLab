<?php

namespace App\Actions\SalesOrder;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesOrderActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,

        ?string $search,
        ?int $customerId,
        ?int $customerAddressId,

        ?ExecuteDTO $execute
    ) {
        $query = SalesOrder::select('sales_orders.*')
            ->with([
                'company',
                'branch',
                'customer',
                'customerAddress',
            ])
            ->join('companies', 'companies.id', '=', 'sales_orders.company_id')
            ->whereCompanyId('sales_orders', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $customerId, $customerAddressId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('sales_orders.branch_id', $branchId);
            }

            if ($customerId) {
                $query->where('sales_orders.customer_id', $customerId);
            }

            if ($customerAddressId) {
                $query->where('sales_orders.customer_address_id', $customerAddressId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sales_orders.date', 'desc')
            ->orderBy('sales_orders.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $customerId ?? '[null]',
                    $customerAddressId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_order_'.implode('_', $cacheParams);

                if ($execute->useCache) {
                    $cacheResult = $this->readFromCache($cacheKey);
                    if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheResult;
                    }
                }

                if ($execute->pagination) {
                    $result = $query->paginate(
                        perPage: $execute->pagination->perPage,
                        columns: ['*'],
                        pageName: 'page',
                        page: $execute->pagination->page
                    );
                } else {
                    if ($execute->get?->limit) {
                        $query->limit($execute->get->limit);
                    }
                    $result = $query->get();
                }

                $recordsCount = $result->count();

                if ($execute->useCache) {
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

        return $query;
    }

    public function read(SalesOrder $salesOrder): SalesOrder
    {
        return $salesOrder->load([
            'company',
            'branch',
            'customer',
            'customerAddress',
            'saleOrderProductUnits',
            'saleOrderDownPayments',
            'saleOrderDownPaymentApplies',
        ]);
    }

    public function create(array $data): SalesOrder
    {
        $timer_start = microtime(true);

        try {
            $salesOrder = new SalesOrder();
            $salesOrder->company_id = $data['company_id'];
            $salesOrder->branch_id = $data['branch_id'];
            $salesOrder->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $salesOrder->date = $data['date'];
            $salesOrder->customer_id = $data['customer_id'];
            $salesOrder->customer_address_id = $data['customer_address_id'];
            $salesOrder->shipping_date = $data['shipping_date'];
            $salesOrder->remarks = $data['remarks'];
            $salesOrder->is_has_invoice = $data['is_has_invoice'];
            $salesOrder->is_sent = $data['is_sent'];
            $salesOrder->total = $data['total'];
            $salesOrder->global_discount_rate = $data['global_discount_rate'];
            $salesOrder->global_discount_fixed = $data['global_discount_fixed'];
            $salesOrder->grand_total = $data['grand_total'];
            $salesOrder->down_payment = $data['down_payment'];
            $salesOrder->down_payment_due_days = $data['down_payment_due_days'];
            $salesOrder->down_payment_applied = $data['down_payment_applied'];
            $salesOrder->down_payment_remaining = $data['down_payment_remaining'];
            $salesOrder->is_down_payment_paid_off = $data['is_down_payment_paid_off'];
            $salesOrder->save();

            $this->flushCache();

            return $salesOrder;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesOrder $salesOrder, array $data): SalesOrder
    {
        $timer_start = microtime(true);

        try {
            $salesOrder->company_id = $data['company_id'];
            $salesOrder->branch_id = $data['branch_id'];
            $salesOrder->code = $this->generateUniqueCode($salesOrder->company_id, $data['code'], $salesOrder->id);
            $salesOrder->date = $data['date'];
            $salesOrder->customer_id = $data['customer_id'];
            $salesOrder->customer_address_id = $data['customer_address_id'];
            $salesOrder->shipping_date = $data['shipping_date'];
            $salesOrder->remarks = $data['remarks'];
            $salesOrder->is_has_invoice = $data['is_has_invoice'];
            $salesOrder->is_sent = $data['is_sent'];
            $salesOrder->total = $data['total'];
            $salesOrder->global_discount_rate = $data['global_discount_rate'];
            $salesOrder->global_discount_fixed = $data['global_discount_fixed'];
            $salesOrder->grand_total = $data['grand_total'];
            $salesOrder->down_payment = $data['down_payment'];
            $salesOrder->down_payment_due_days = $data['down_payment_due_days'];
            $salesOrder->down_payment_applied = $data['down_payment_applied'];
            $salesOrder->down_payment_remaining = $data['down_payment_remaining'];
            $salesOrder->is_down_payment_paid_off = $data['is_down_payment_paid_off'];
            $salesOrder->save();

            $this->flushCache();

            return $salesOrder->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesOrder $salesOrder): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $retval = $salesOrder->delete();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
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
                $count = $company->salesOrders()->withTrashed()->count() + 1 + $tryCount;
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
        $result = SalesOrder::whereCompanyId('sales_orders', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
