<?php

namespace App\Actions\SalesOrderPayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\SalesOrder\SalesOrderActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\SalesOrderPaymentCreateDTO;
use App\DTOs\SalesOrderPaymentUpdateDTO;
use App\Enums\AllocationStatusEnum;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\SalesOrderPayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesOrderPaymentActions
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
        ?string $allocationStatus,

        ?ExecuteDTO $execute
    ) {
        $query = SalesOrderPayment::select('sales_order_payments.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'sales_order_payments.company_id')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_payments.sales_order_id')
            ->whereCompanyId('sales_order_payments', $companyId)
            ->whereBranchId('sales_order_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $startDate, $endDate, $salesOrderId, $customerId, $cashAccountId, $allocationStatus) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sales_order_payments.code', 'like', '%'.$search.'%')
                        ->orWhere('sales_order_payments.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('sales_order_payments.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('sales_order_payments.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($salesOrderId) {
                $query->where('sales_order_payments.sales_order_id', $salesOrderId);
            }

            if ($customerId) {
                $query->where('sales_orders.customer_id', $customerId);
            }

            if ($cashAccountId) {
                $query->where('sales_order_payments.cash_account_id', $cashAccountId);
            }

            if ($allocationStatus === AllocationStatusEnum::NOT_FULLY_ALLOCATED->value) {
                $query->whereColumn('sales_order_payments.amount_allocated', '<', 'sales_order_payments.amount');
            }

            if ($allocationStatus === AllocationStatusEnum::FULLY_ALLOCATED->value) {
                $query->whereColumn('sales_order_payments.amount_allocated', '>=', 'sales_order_payments.amount')
                    ->where('sales_order_payments.amount', '>', 0);
            }
        });

        $query->orderBy('sales_order_payments.date', 'desc')
            ->orderBy('sales_order_payments.id', 'asc');

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
                    $allocationStatus ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_order_payment_'.implode('_', $cacheParams);

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

    public function getAllocationStatuses(): array
    {
        return [
            [
                'name' => 'views.sales_order.filters.payment_allocation_status_not_fully_allocated',
                'code' => AllocationStatusEnum::NOT_FULLY_ALLOCATED->value,
            ],
            [
                'name' => 'views.sales_order.filters.payment_allocation_status_fully_allocated',
                'code' => AllocationStatusEnum::FULLY_ALLOCATED->value,
            ],
        ];
    }

    public function read(SalesOrderPayment $salesOrderPayment): SalesOrderPayment
    {
        return $salesOrderPayment->load(self::LIST_EAGER_LOADS);
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
                $count = SalesOrderPayment::whereCompanyId('sales_order_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'SOP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SalesOrderPayment::whereCompanyId('sales_order_payments', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(
        SalesOrderPaymentCreateDTO $data,
        bool $updateParentSummary,
    ): SalesOrderPayment {
        $timer_start = microtime(true);

        try {
            $salesOrderPayment = new SalesOrderPayment();
            $salesOrderPayment->company_id = $data->companyId;
            $salesOrderPayment->branch_id = $data->branchId;
            $salesOrderPayment->sales_order_id = $data->salesOrderId;
            $salesOrderPayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $salesOrderPayment->date = $this->generateDate($data->date);
            $salesOrderPayment->cash_account_id = $data->cashAccountId;
            $salesOrderPayment->amount = $data->amount;
            $salesOrderPayment->remarks = $data->remarks;
            $salesOrderPayment->save();

            $this->syncCashTransaction($salesOrderPayment);
            $this->syncJournalEntry($salesOrderPayment);

            if ($updateParentSummary) {
                $salesOrder = $salesOrderPayment->salesOrder;
                SalesOrderActions::updateSummary($salesOrder);
                $salesOrderPayment->refresh();
            }

            $this->flushCache();

            return $salesOrderPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        SalesOrderPayment $salesOrderPayment,
        SalesOrderPaymentUpdateDTO $data,
        bool $updateParentSummary,
    ): SalesOrderPayment {
        $timer_start = microtime(true);

        try {
            if ((float) $data->amount < (float) $salesOrderPayment->amount_allocated) {
                throw new Exception('Sales order payment amount cannot be less than its allocated amount.');
            }

            $salesOrderPayment->code = $this->generateUniqueCode($salesOrderPayment->company_id, $data->code, $salesOrderPayment->id);
            $salesOrderPayment->date = $this->generateDate($data->date);
            $salesOrderPayment->cash_account_id = $data->cashAccountId;
            $salesOrderPayment->amount = $data->amount;
            $salesOrderPayment->remarks = $data->remarks;
            $salesOrderPayment->save();

            $this->syncCashTransaction($salesOrderPayment);
            $this->syncJournalEntry($salesOrderPayment);

            if ($updateParentSummary) {
                $salesOrder = $salesOrderPayment->salesOrder;
                SalesOrderActions::updateSummary($salesOrder);
                $salesOrderPayment->refresh();
            }

            $this->flushCache();

            return $salesOrderPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesOrderPayment $salesOrderPayment): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            if ((float) $salesOrderPayment->amount_allocated > 0 || $salesOrderPayment->invoicePayments()->exists()) {
                throw new Exception('Sales order payment cannot be deleted because it is referenced by invoice payments.');
            }

            $cashTransaction = $salesOrderPayment->cashTransaction;
            if ($cashTransaction) $this->cashTransactionActions->delete($cashTransaction);

            $journalEntry = $salesOrderPayment->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $salesOrderPayment->delete();

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

    private function syncCashTransaction(SalesOrderPayment $salesOrderPayment): void
    {
        $cashTransaction = $salesOrderPayment->cashTransaction;

        if ((float) $salesOrderPayment->amount > 0) {
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromSalesOrderPayment($salesOrderPayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromSalesOrderPayment($salesOrderPayment)
                );
            }
        } elseif ($cashTransaction) {
            $this->cashTransactionActions->delete($cashTransaction);
        }
    }

    private function syncJournalEntry(SalesOrderPayment $salesOrderPayment): void
    {
        $journalEntry = $salesOrderPayment->journalEntry;

        if ((float) $salesOrderPayment->amount <= 0) {
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            return;
        }

        $items = $this->buildJournalEntryItems($salesOrderPayment);

        if (! $journalEntry) {
            $this->journalEntryActions->create(new JournalEntryCreateDTO(
                companyId: $salesOrderPayment->company_id,
                branchId: $salesOrderPayment->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $salesOrderPayment->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                sourceType: SalesOrderPayment::class,
                sourceId: $salesOrderPayment->id,
                referenceNo: $salesOrderPayment->code,
                remarks: $salesOrderPayment->remarks,
                items: $items,
            ));
        } else {
            $this->journalEntryActions->update($journalEntry, new JournalEntryUpdateDTO(
                branchId: $salesOrderPayment->branch_id,
                code: $journalEntry->code,
                date: $salesOrderPayment->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                referenceNo: $salesOrderPayment->code,
                remarks: $salesOrderPayment->remarks,
                items: $items,
            ));
        }
    }

    private function buildJournalEntryItems(SalesOrderPayment $salesOrderPayment): array
    {
        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $salesOrderPayment->cashAccount?->chartOfAccount?->id,
            sequence: count($items) + 1,
            debit: (float) $salesOrderPayment->amount,
            credit: 0,
            remarks: $salesOrderPayment->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $salesOrderPayment->company->liabilityCustomerDownPaymentChartOfAccount?->id,
            sequence: count($items) + 1,
            debit: 0,
            credit: (float) $salesOrderPayment->amount,
            remarks: $salesOrderPayment->remarks,
        );

        return $items;
    }
}
