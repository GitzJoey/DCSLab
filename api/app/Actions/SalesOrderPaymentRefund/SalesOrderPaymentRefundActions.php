<?php

namespace App\Actions\SalesOrderPaymentRefund;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\SalesOrder\SalesOrderActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\SalesOrderPaymentRefundCreateDTO;
use App\DTOs\SalesOrderPaymentRefundUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\SalesOrderPaymentRefund;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesOrderPaymentRefundActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'salesOrder.customer',
        'cashAccount',
    ];

    public function __construct(
        private CashTransactionActions $cashTransactionActions,
        private JournalEntryActions $journalEntryActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $startDate,
        ?string $endDate,
        ?int $salesOrderId,
        ?int $customerId,
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = SalesOrderPaymentRefund::select('sales_order_payment_refunds.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'sales_order_payment_refunds.company_id')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_payment_refunds.sales_order_id')
            ->whereCompanyId('sales_order_payment_refunds', $companyId)
            ->whereBranchId('sales_order_payment_refunds', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $startDate, $endDate, $salesOrderId, $customerId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sales_order_payment_refunds.code', 'like', '%'.$search.'%')
                        ->orWhere('sales_order_payment_refunds.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('sales_order_payment_refunds.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('sales_order_payment_refunds.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($salesOrderId) {
                $query->where('sales_order_payment_refunds.sales_order_id', $salesOrderId);
            }

            if ($customerId) {
                $query->where('sales_orders.customer_id', $customerId);
            }

            if ($cashAccountId) {
                $query->where('sales_order_payment_refunds.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('sales_order_payment_refunds.date', 'desc')
            ->orderBy('sales_order_payment_refunds.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $salesOrderId ?? '[null]',
                    $customerId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_order_payment_refund_'.implode('_', $cacheParams);

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

    public function read(SalesOrderPaymentRefund $salesOrderPaymentRefund): SalesOrderPaymentRefund
    {
        return $salesOrderPaymentRefund->load(self::LIST_EAGER_LOADS);
    }

    public function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $tryCount = 0;

            do {
                $count = SalesOrderPaymentRefund::whereCompanyId('sales_order_payment_refunds', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'SOPR'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SalesOrderPaymentRefund::whereCompanyId('sales_order_payment_refunds', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(
        SalesOrderPaymentRefundCreateDTO $data,
        bool $updateParentSummary,
    ): SalesOrderPaymentRefund {
        $timer_start = microtime(true);

        try {
            $salesOrderPaymentRefund = new SalesOrderPaymentRefund();
            $salesOrderPaymentRefund->company_id = $data->companyId;
            $salesOrderPaymentRefund->branch_id = $data->branchId;
            $salesOrderPaymentRefund->sales_order_id = $data->salesOrderId;
            $salesOrderPaymentRefund->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $salesOrderPaymentRefund->date = $this->generateDate($data->date);
            $salesOrderPaymentRefund->cash_account_id = $data->cashAccountId;
            $salesOrderPaymentRefund->amount = $data->amount;
            $salesOrderPaymentRefund->remarks = $data->remarks;
            $salesOrderPaymentRefund->save();

            $this->syncCashTransaction($salesOrderPaymentRefund);
            $this->syncJournalEntry($salesOrderPaymentRefund);

            if ($updateParentSummary) {
                $salesOrder = $salesOrderPaymentRefund->salesOrder;
                SalesOrderActions::updateSummary($salesOrder);
                $salesOrderPaymentRefund->refresh();
            }

            $this->flushCache();

            return $salesOrderPaymentRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        SalesOrderPaymentRefund $salesOrderPaymentRefund,
        SalesOrderPaymentRefundUpdateDTO $data,
        bool $updateParentSummary,
    ): SalesOrderPaymentRefund {
        $timer_start = microtime(true);

        try {
            $salesOrderPaymentRefund->code = $this->generateUniqueCode($salesOrderPaymentRefund->company_id, $data->code, $salesOrderPaymentRefund->id);
            $salesOrderPaymentRefund->date = $this->generateDate($data->date);
            $salesOrderPaymentRefund->cash_account_id = $data->cashAccountId;
            $salesOrderPaymentRefund->amount = $data->amount;
            $salesOrderPaymentRefund->remarks = $data->remarks;
            $salesOrderPaymentRefund->save();

            $this->syncCashTransaction($salesOrderPaymentRefund);
            $this->syncJournalEntry($salesOrderPaymentRefund);

            if ($updateParentSummary) {
                $salesOrder = $salesOrderPaymentRefund->salesOrder;
                SalesOrderActions::updateSummary($salesOrder);
                $salesOrderPaymentRefund->refresh();
            }

            $this->flushCache();

            return $salesOrderPaymentRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesOrderPaymentRefund $salesOrderPaymentRefund): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $salesOrderPaymentRefund->cashTransaction;
            if ($cashTransaction) $this->cashTransactionActions->delete($cashTransaction);

            $journalEntry = $salesOrderPaymentRefund->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $salesOrderPaymentRefund->delete();

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

    private function syncCashTransaction(SalesOrderPaymentRefund $salesOrderPaymentRefund): void
    {
        $cashTransaction = $salesOrderPaymentRefund->cashTransaction;

        if ((float) $salesOrderPaymentRefund->amount > 0) {
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromSalesOrderPaymentRefund($salesOrderPaymentRefund)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromSalesOrderPaymentRefund($salesOrderPaymentRefund)
                );
            }
        } elseif ($cashTransaction) {
            $this->cashTransactionActions->delete($cashTransaction);
        }
    }

    private function syncJournalEntry(SalesOrderPaymentRefund $salesOrderPaymentRefund): void
    {
        $journalEntry = $salesOrderPaymentRefund->journalEntry;

        if ((float) $salesOrderPaymentRefund->amount <= 0) {
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            return;
        }

        $items = $this->buildJournalEntryItems($salesOrderPaymentRefund);

        if (! $journalEntry) {
            $this->journalEntryActions->create(new JournalEntryCreateDTO(
                companyId: $salesOrderPaymentRefund->company_id,
                branchId: $salesOrderPaymentRefund->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $salesOrderPaymentRefund->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                sourceType: SalesOrderPaymentRefund::class,
                sourceId: $salesOrderPaymentRefund->id,
                referenceNo: $salesOrderPaymentRefund->code,
                remarks: $salesOrderPaymentRefund->remarks,
                items: $items,
            ));
        } else {
            $this->journalEntryActions->update($journalEntry, new JournalEntryUpdateDTO(
                branchId: $salesOrderPaymentRefund->branch_id,
                code: $journalEntry->code,
                date: $salesOrderPaymentRefund->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                referenceNo: $salesOrderPaymentRefund->code,
                remarks: $salesOrderPaymentRefund->remarks,
                items: $items,
            ));
        }
    }

    private function buildJournalEntryItems(SalesOrderPaymentRefund $salesOrderPaymentRefund): array
    {
        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $salesOrderPaymentRefund->company->liabilityCustomerDownPaymentChartOfAccount?->id,
            sequence: count($items) + 1,
            debit: (float) $salesOrderPaymentRefund->amount,
            credit: 0,
            remarks: $salesOrderPaymentRefund->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $salesOrderPaymentRefund->cashAccount?->chartOfAccount?->id,
            sequence: count($items) + 1,
            debit: 0,
            credit: (float) $salesOrderPaymentRefund->amount,
            remarks: $salesOrderPaymentRefund->remarks,
        );

        return $items;
    }
}
