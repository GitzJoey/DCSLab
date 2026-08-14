<?php

namespace App\Actions\PurchaseReturnRefund;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\PurchaseReturn\PurchaseReturnActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\PurchaseReturnRefundCreateDTO;
use App\DTOs\PurchaseReturnRefundUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseReturnRefund;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReturnRefundActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseReturn.supplier',
        'purchaseReturn.purchaseInvoice',
        'cashAccount',
    ];

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $purchaseReturnId,
        ?int $cashAccountId,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReturnRefund::select('purchase_return_refunds.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_return_refunds.company_id')
            ->whereCompanyId('purchase_return_refunds', $companyId)
            ->whereBranchId('purchase_return_refunds', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseReturnId,
            $cashAccountId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_return_refunds.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase_return_refunds.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($purchaseReturnId) {
                $query->where('purchase_return_refunds.purchase_return_id', $purchaseReturnId);
            }

            if ($cashAccountId) {
                $query->where('purchase_return_refunds.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('purchase_return_refunds.date', 'desc')
            ->orderBy('purchase_return_refunds.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $purchaseReturnId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_return_refund_'.implode('_', $cacheParams);

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

    public function read(PurchaseReturnRefund $purchaseReturnRefund): PurchaseReturnRefund
    {
        return $purchaseReturnRefund->load(self::LIST_EAGER_LOADS);
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
                $count = PurchaseReturnRefund::whereCompanyId('purchase_return_refunds', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PRTR'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseReturnRefund::whereCompanyId('purchase_return_refunds', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PurchaseReturnRefundCreateDTO $data, bool $updateParentSummary = true): PurchaseReturnRefund
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturnRefund = new PurchaseReturnRefund();
            $purchaseReturnRefund->company_id = $data->companyId;
            $purchaseReturnRefund->branch_id = $data->branchId;
            $purchaseReturnRefund->purchase_return_id = $data->purchaseReturnId;
            $purchaseReturnRefund->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseReturnRefund->date = $this->generateDate($data->date);
            $purchaseReturnRefund->cash_account_id = $data->cashAccountId;
            $purchaseReturnRefund->amount = $data->amount;
            $purchaseReturnRefund->remarks = $data->remarks;
            $purchaseReturnRefund->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromPurchaseReturnRefund($purchaseReturnRefund)
            );

            $this->saveJournalEntry($purchaseReturnRefund);

            if ($updateParentSummary) {
                PurchaseReturnActions::updateSummary($purchaseReturnRefund->purchaseReturn);
                $purchaseReturnRefund->refresh();
            }

            $this->flushCache();

            return $purchaseReturnRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReturnRefund $purchaseReturnRefund, PurchaseReturnRefundUpdateDTO $data, bool $updateParentSummary = true): PurchaseReturnRefund
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturnRefund->code = $this->generateUniqueCode($purchaseReturnRefund->company_id, $data->code, $purchaseReturnRefund->id);
            $purchaseReturnRefund->date = $this->generateDate($data->date);
            $purchaseReturnRefund->cash_account_id = $data->cashAccountId;
            $purchaseReturnRefund->amount = $data->amount;
            $purchaseReturnRefund->remarks = $data->remarks;
            $purchaseReturnRefund->save();

            $cashTransaction = $purchaseReturnRefund->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPurchaseReturnRefund($purchaseReturnRefund)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromPurchaseReturnRefund($purchaseReturnRefund)
                );
            }

            $this->saveJournalEntry($purchaseReturnRefund);

            if ($updateParentSummary) {
                PurchaseReturnActions::updateSummary($purchaseReturnRefund->purchaseReturn);
                $purchaseReturnRefund->refresh();
            }

            $this->flushCache();

            return $purchaseReturnRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReturnRefund $purchaseReturnRefund, bool $updateParentSummary = true): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $purchaseReturn = $purchaseReturnRefund->purchaseReturn;

            $cashTransaction = $purchaseReturnRefund->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $purchaseReturnRefund->journalEntry;
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            $retval = $purchaseReturnRefund->delete();

            if ($updateParentSummary) {
                PurchaseReturnActions::updateSummary($purchaseReturn);
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
     * Journal P9 (design §5): debit the cash COA, credit the supplier AP
     * account. Uninvoiced returns use the same accounts so the entry stays
     * balance-sheet only; a profit and loss account here would never be closed
     * into earnings because refunds post no closing entries.
     */
    private function saveJournalEntry(PurchaseReturnRefund $purchaseReturnRefund): void
    {
        $purchaseReturn = $purchaseReturnRefund->purchaseReturn;
        $amount = (float) $purchaseReturnRefund->amount;

        $journalEntry = $purchaseReturnRefund->journalEntry;

        if ($amount <= 0) {
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            return;
        }

        $creditChartOfAccountId = $purchaseReturn->supplier?->chartOfAccount?->id
            ?? $purchaseReturn->company->liabilityAccountPayableChartOfAccount?->id;

        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $purchaseReturnRefund->cashAccount?->chartOfAccount?->id,
            sequence: count($items) + 1,
            debit: $amount,
            credit: 0,
            remarks: $purchaseReturnRefund->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $creditChartOfAccountId,
            sequence: count($items) + 1,
            debit: 0,
            credit: $amount,
            remarks: $purchaseReturnRefund->remarks,
        );

        if (! $journalEntry) {
            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $purchaseReturnRefund->company_id,
                branchId: $purchaseReturnRefund->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $purchaseReturnRefund->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                sourceType: PurchaseReturnRefund::class,
                sourceId: $purchaseReturnRefund->id,
                referenceNo: $purchaseReturnRefund->code,
                remarks: $purchaseReturnRefund->remarks,
                items: $items,
            );
            $this->journalEntryActions->create($journalEntryDTO);
        } else {
            $journalEntryDTO = new JournalEntryUpdateDTO(
                branchId: $purchaseReturnRefund->branch_id,
                code: $journalEntry->code,
                date: $purchaseReturnRefund->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                referenceNo: $purchaseReturnRefund->code,
                remarks: $purchaseReturnRefund->remarks,
                items: $items,
            );
            $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
        }
    }
}
