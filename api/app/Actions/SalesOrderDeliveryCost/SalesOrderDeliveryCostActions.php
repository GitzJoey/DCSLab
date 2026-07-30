<?php

namespace App\Actions\SalesOrderDeliveryCost;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\SalesOrderDelivery\SalesOrderDeliveryActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\SalesOrderDeliveryCostCreateDTO;
use App\DTOs\SalesOrderDeliveryCostUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\JournalEntry;
use App\Models\SalesOrderDeliveryCost;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesOrderDeliveryCostActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'salesOrderDelivery.customer',
        'salesOrderDelivery.salesOrder.customer',
        'cashAccount',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $salesOrderDeliveryId,
        ?int $cashAccountId,
        ?string $startDate,
        ?string $endDate,
        ?ExecuteDTO $execute
    ) {
        $query = SalesOrderDeliveryCost::select('sales_order_delivery_costs.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'sales_order_delivery_costs.company_id')
            ->whereCompanyId('sales_order_delivery_costs', $companyId)
            ->whereBranchId('sales_order_delivery_costs', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $salesOrderDeliveryId,
            $cashAccountId,
            $startDate,
            $endDate,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sales_order_delivery_costs.code', 'like', '%'.$search.'%')
                        ->orWhere('sales_order_delivery_costs.name', 'like', '%'.$search.'%')
                        ->orWhere('sales_order_delivery_costs.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($salesOrderDeliveryId) {
                $query->where('sales_order_delivery_costs.sales_order_delivery_id', $salesOrderDeliveryId);
            }

            if ($cashAccountId) {
                $query->where('sales_order_delivery_costs.cash_account_id', $cashAccountId);
            }

            if ($startDate) {
                $query->where('sales_order_delivery_costs.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('sales_order_delivery_costs.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }
        });

        $query->orderBy('sales_order_delivery_costs.date', 'desc')
            ->orderBy('sales_order_delivery_costs.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $salesOrderDeliveryId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_order_delivery_cost_'.implode('_', $cacheParams);

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

    public function read(SalesOrderDeliveryCost $salesOrderDeliveryCost): SalesOrderDeliveryCost
    {
        return $salesOrderDeliveryCost->load(self::LIST_EAGER_LOADS);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != Config::get('dcslab.KEYWORDS.AUTO')) return $code;

        $tryCount = 0;
        do {
            $count = SalesOrderDeliveryCost::withTrashed()->where('company_id', $companyId)->count() + 1 + $tryCount;
            $code = 'SDLC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SalesOrderDeliveryCost::where('company_id', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    private function generateDate($date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d H:i:s');
        }

        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function create(SalesOrderDeliveryCostCreateDTO $data, bool $updateParent): SalesOrderDeliveryCost
    {
        $timer_start = microtime(true);

        try {
            $salesOrderDeliveryCost = new SalesOrderDeliveryCost();
            $salesOrderDeliveryCost->company_id = $data->companyId;
            $salesOrderDeliveryCost->branch_id = $data->branchId;
            $salesOrderDeliveryCost->sales_order_delivery_id = $data->salesOrderDeliveryId;
            $salesOrderDeliveryCost->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $salesOrderDeliveryCost->date = $this->generateDate($data->date);
            $salesOrderDeliveryCost->name = $data->name;
            $salesOrderDeliveryCost->cash_account_id = $data->cashAccountId;
            $salesOrderDeliveryCost->amount = $data->amount;
            $salesOrderDeliveryCost->remarks = $data->remarks;
            $salesOrderDeliveryCost->save();

            if ((float) $salesOrderDeliveryCost->amount > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromSalesOrderDeliveryCost($salesOrderDeliveryCost)
                );
            }

            $this->syncJournalEntries($salesOrderDeliveryCost);

            if ($updateParent) {
                SalesOrderDeliveryActions::updateSummary($salesOrderDeliveryCost->salesOrderDelivery);
            }

            $this->flushCache();

            return $salesOrderDeliveryCost;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesOrderDeliveryCost $salesOrderDeliveryCost, SalesOrderDeliveryCostUpdateDTO $data, bool $updateParent): SalesOrderDeliveryCost
    {
        $timer_start = microtime(true);

        try {
            $salesOrderDeliveryCost->code = $this->generateUniqueCode($salesOrderDeliveryCost->company_id, $data->code, $salesOrderDeliveryCost->id);
            $salesOrderDeliveryCost->date = $this->generateDate($data->date);
            $salesOrderDeliveryCost->name = $data->name;
            $salesOrderDeliveryCost->cash_account_id = $data->cashAccountId;
            $salesOrderDeliveryCost->amount = $data->amount;
            $salesOrderDeliveryCost->remarks = $data->remarks;
            $salesOrderDeliveryCost->save();

            $cashTransaction = $salesOrderDeliveryCost->cashTransaction;
            if ((float) $salesOrderDeliveryCost->amount > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromSalesOrderDeliveryCost($salesOrderDeliveryCost)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromSalesOrderDeliveryCost($salesOrderDeliveryCost)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $this->syncJournalEntries($salesOrderDeliveryCost);

            if ($updateParent) {
                SalesOrderDeliveryActions::updateSummary($salesOrderDeliveryCost->salesOrderDelivery);
            }

            $this->flushCache();

            return $salesOrderDeliveryCost;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesOrderDeliveryCost $salesOrderDeliveryCost, bool $updateParent): bool
    {
        $timer_start = microtime(true);

        try {
            $salesOrderDelivery = $salesOrderDeliveryCost->salesOrderDelivery;

            $cashTransaction = $salesOrderDeliveryCost->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $salesOrderDeliveryCost->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $currentMonthEarningsJournalEntry = $salesOrderDeliveryCost->currentMonthEarningsJournalEntry;
            if ($currentMonthEarningsJournalEntry) $this->journalEntryActions->delete($currentMonthEarningsJournalEntry);

            $monthEndClosingJournalEntry = $salesOrderDeliveryCost->monthEndClosingJournalEntry;
            if ($monthEndClosingJournalEntry) $this->journalEntryActions->delete($monthEndClosingJournalEntry);

            $monthToYearClosingJournalEntry = $salesOrderDeliveryCost->monthToYearClosingJournalEntry;
            if ($monthToYearClosingJournalEntry) $this->journalEntryActions->delete($monthToYearClosingJournalEntry);

            $yearToRetainedEarningsClosingJournalEntry = $salesOrderDeliveryCost->yearToRetainedEarningsClosingJournalEntry;
            if ($yearToRetainedEarningsClosingJournalEntry) $this->journalEntryActions->delete($yearToRetainedEarningsClosingJournalEntry);

            $result = $salesOrderDeliveryCost->delete();

            if ($updateParent) {
                SalesOrderDeliveryActions::updateSummary($salesOrderDelivery);
            }

            $this->flushCache();

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    /**
     * Design §5 S4 (expense-direction P&L): TRANSACTION Dr expense_freight_out /
     * Cr cash COA + full closing set (§5.1). All entries skip/delete when amount is 0.
     */
    private function syncJournalEntries(SalesOrderDeliveryCost $salesOrderDeliveryCost): void
    {
        $amount = (float) $salesOrderDeliveryCost->amount;
        $company = $salesOrderDeliveryCost->company;
        $hasAmount = $amount > 0;

        $this->syncJournalEntry(
            salesOrderDeliveryCost: $salesOrderDeliveryCost,
            journalEntry: $salesOrderDeliveryCost->journalEntry,
            journalType: JournalEntryTypeEnum::TRANSACTION->value,
            date: (string) $salesOrderDeliveryCost->date,
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->expenseFreightOutChartOfAccount?->id,
                    sequence: 1,
                    debit: $amount,
                    credit: 0,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $salesOrderDeliveryCost->cashAccount?->chartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $amount,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
            ],
        );

        $this->syncJournalEntry(
            salesOrderDeliveryCost: $salesOrderDeliveryCost,
            journalEntry: $salesOrderDeliveryCost->currentMonthEarningsJournalEntry,
            journalType: JournalEntryTypeEnum::CURRENT_MONTH_EARNINGS->value,
            date: (string) $salesOrderDeliveryCost->date,
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentMonthEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: $amount,
                    credit: 0,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->systemSuspenseChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $amount,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
            ],
        );

        $this->syncJournalEntry(
            salesOrderDeliveryCost: $salesOrderDeliveryCost,
            journalEntry: $salesOrderDeliveryCost->monthEndClosingJournalEntry,
            journalType: JournalEntryTypeEnum::MONTH_END_CLOSING->value,
            date: $this->endOfMonthDate($salesOrderDeliveryCost),
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->systemSuspenseChartOfAccount?->id,
                    sequence: 1,
                    debit: $amount,
                    credit: 0,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->expenseFreightOutChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $amount,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
            ],
        );

        $this->syncJournalEntry(
            salesOrderDeliveryCost: $salesOrderDeliveryCost,
            journalEntry: $salesOrderDeliveryCost->monthToYearClosingJournalEntry,
            journalType: JournalEntryTypeEnum::MONTH_TO_YEAR_CLOSING->value,
            date: $this->endOfMonthDate($salesOrderDeliveryCost),
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentYearEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: $amount,
                    credit: 0,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentMonthEarningsChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $amount,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
            ],
        );

        $this->syncJournalEntry(
            salesOrderDeliveryCost: $salesOrderDeliveryCost,
            journalEntry: $salesOrderDeliveryCost->yearToRetainedEarningsClosingJournalEntry,
            journalType: JournalEntryTypeEnum::YEAR_TO_RETAINED_EARNINGS_CLOSING->value,
            date: $this->endOfYearDate($salesOrderDeliveryCost),
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityRetainedEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: $amount,
                    credit: 0,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentYearEarningsChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $amount,
                    remarks: $salesOrderDeliveryCost->remarks,
                ),
            ],
        );
    }

    private function syncJournalEntry(
        SalesOrderDeliveryCost $salesOrderDeliveryCost,
        ?JournalEntry $journalEntry,
        string $journalType,
        string $date,
        array $items,
    ): void {
        if (count($items) === 0) {
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            return;
        }

        if (! $journalEntry) {
            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $salesOrderDeliveryCost->company_id,
                branchId: $salesOrderDeliveryCost->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $date,
                journalType: $journalType,
                sourceType: SalesOrderDeliveryCost::class,
                sourceId: $salesOrderDeliveryCost->id,
                referenceNo: $salesOrderDeliveryCost->code,
                remarks: $salesOrderDeliveryCost->remarks,
                items: $items,
            );
            $this->journalEntryActions->create($journalEntryDTO);
        } else {
            $journalEntryDTO = new JournalEntryUpdateDTO(
                branchId: $salesOrderDeliveryCost->branch_id,
                code: $journalEntry->code,
                date: $date,
                journalType: $journalType,
                referenceNo: $salesOrderDeliveryCost->code,
                remarks: $salesOrderDeliveryCost->remarks,
                items: $items,
            );
            $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
        }
    }

    private function endOfMonthDate(SalesOrderDeliveryCost $salesOrderDeliveryCost): string
    {
        return ($salesOrderDeliveryCost->date instanceof Carbon
            ? $salesOrderDeliveryCost->date->copy()
            : Carbon::parse((string) $salesOrderDeliveryCost->date))
            ->endOfMonth()
            ->format('Y-m-d H:i:s');
    }

    private function endOfYearDate(SalesOrderDeliveryCost $salesOrderDeliveryCost): string
    {
        return ($salesOrderDeliveryCost->date instanceof Carbon
            ? $salesOrderDeliveryCost->date->copy()
            : Carbon::parse((string) $salesOrderDeliveryCost->date))
            ->endOfYear()
            ->format('Y-m-d H:i:s');
    }
}
