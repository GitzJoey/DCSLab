<?php

namespace App\Actions\SalesReturnRefund;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\SalesReturn\SalesReturnActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\SalesReturnRefundCreateDTO;
use App\DTOs\SalesReturnRefundUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\SalesReturnRefund;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;

class SalesReturnRefundActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
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
                $count = SalesReturnRefund::whereCompanyId('sales_return_refunds', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'SRTR'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SalesReturnRefund::whereCompanyId('sales_return_refunds', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(SalesReturnRefundCreateDTO $data, bool $updateParentSummary = true): SalesReturnRefund
    {
        $timer_start = microtime(true);

        try {
            $salesReturnRefund = new SalesReturnRefund();
            $salesReturnRefund->company_id = $data->companyId;
            $salesReturnRefund->branch_id = $data->branchId;
            $salesReturnRefund->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $salesReturnRefund->date = $this->generateDate($data->date);
            $salesReturnRefund->sales_return_id = $data->salesReturnId;
            $salesReturnRefund->cash_account_id = $data->cashAccountId;
            $salesReturnRefund->amount = $data->amount;
            $salesReturnRefund->remarks = $data->remarks;
            $salesReturnRefund->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromSalesReturnRefund($salesReturnRefund)
            );

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $salesReturnRefund->company_id,
                branchId: $salesReturnRefund->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $salesReturnRefund->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                sourceType: SalesReturnRefund::class,
                sourceId: $salesReturnRefund->id,
                referenceNo: $salesReturnRefund->code,
                remarks: $salesReturnRefund->remarks,
                items: $this->buildJournalItems($salesReturnRefund),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            if ($updateParentSummary) {
                SalesReturnActions::updateSummary($salesReturnRefund->salesReturn);
                $salesReturnRefund->refresh();
            }

            $this->flushCache();

            return $salesReturnRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesReturnRefund $salesReturnRefund, SalesReturnRefundUpdateDTO $data, bool $updateParentSummary = true): SalesReturnRefund
    {
        $timer_start = microtime(true);

        try {
            $salesReturnRefund->code = $this->generateUniqueCode($salesReturnRefund->company_id, $data->code, $salesReturnRefund->id);
            $salesReturnRefund->date = $this->generateDate($data->date);
            $salesReturnRefund->cash_account_id = $data->cashAccountId;
            $salesReturnRefund->amount = $data->amount;
            $salesReturnRefund->remarks = $data->remarks;
            $salesReturnRefund->save();

            $cashTransaction = $salesReturnRefund->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromSalesReturnRefund($salesReturnRefund)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromSalesReturnRefund($salesReturnRefund)
                );
            }

            $journalEntry = $salesReturnRefund->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $salesReturnRefund->company_id,
                    branchId: $salesReturnRefund->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $salesReturnRefund->date,
                    journalType: JournalEntryTypeEnum::TRANSACTION->value,
                    sourceType: SalesReturnRefund::class,
                    sourceId: $salesReturnRefund->id,
                    referenceNo: $salesReturnRefund->code,
                    remarks: $salesReturnRefund->remarks,
                    items: $this->buildJournalItems($salesReturnRefund),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $salesReturnRefund->branch_id,
                    code: $journalEntry->code,
                    date: $salesReturnRefund->date,
                    journalType: JournalEntryTypeEnum::TRANSACTION->value,
                    referenceNo: $salesReturnRefund->code,
                    remarks: $salesReturnRefund->remarks,
                    items: $this->buildJournalItems($salesReturnRefund),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            if ($updateParentSummary) {
                SalesReturnActions::updateSummary($salesReturnRefund->salesReturn);
                $salesReturnRefund->refresh();
            }

            $this->flushCache();

            return $salesReturnRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesReturnRefund $salesReturnRefund, bool $updateParentSummary = true): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $salesReturn = $salesReturnRefund->salesReturn;

            $cashTransaction = $salesReturnRefund->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $salesReturnRefund->journalEntry;
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            $retval = $salesReturnRefund->delete();

            if ($updateParentSummary) {
                SalesReturnActions::updateSummary($salesReturn);
            }

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

    /**
     * Journal S9 (design §5): debit the customer AR account, credit the cash
     * COA. Uninvoiced returns use the same accounts so the entry stays
     * balance-sheet only; a profit and loss account here would never be closed
     * into earnings because refunds post no closing entries.
     */
    private function buildJournalItems(SalesReturnRefund $salesReturnRefund): array
    {
        $salesReturn = $salesReturnRefund->salesReturn;

        $debitChartOfAccountId = $salesReturn->customer?->chartOfAccount?->id
            ?? $salesReturn->company->assetCurrentAccountReceivableChartOfAccount?->id;

        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $debitChartOfAccountId,
            sequence: count($items) + 1,
            debit: (float) $salesReturnRefund->amount,
            credit: 0,
            remarks: $salesReturnRefund->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $salesReturnRefund->cashAccount?->chartOfAccount?->id,
            sequence: count($items) + 1,
            debit: 0,
            credit: (float) $salesReturnRefund->amount,
            remarks: $salesReturnRefund->remarks,
        );

        return $items;
    }
}
