<?php

namespace App\Actions\PurchaseOrderReceiptCost;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\PurchaseOrderReceiptCostCreateDTO;
use App\DTOs\PurchaseOrderReceiptCostUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrderReceiptCost;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderReceiptCostActions
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
        if ($code == Config::get('dcslab.KEYWORDS.AUTO')) {
            $tryCount = 0;

            do {
                $count = PurchaseOrderReceiptCost::whereCompanyId('purchase_order_receipt_costs', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PRCC'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseOrderReceiptCost::whereCompanyId('purchase_order_receipt_costs', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PurchaseOrderReceiptCostCreateDTO $data): PurchaseOrderReceiptCost
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderReceiptCost = new PurchaseOrderReceiptCost();
            $purchaseOrderReceiptCost->company_id = $data->companyId;
            $purchaseOrderReceiptCost->branch_id = $data->branchId;
            $purchaseOrderReceiptCost->purchase_order_receipt_id = $data->purchaseOrderReceiptId;
            $purchaseOrderReceiptCost->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseOrderReceiptCost->date = $this->generateDate($data->date);
            $purchaseOrderReceiptCost->name = $data->name;
            $purchaseOrderReceiptCost->cash_account_id = $data->cashAccountId;
            $purchaseOrderReceiptCost->amount = $data->amount;
            $purchaseOrderReceiptCost->remarks = $data->remarks;
            $purchaseOrderReceiptCost->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromPurchaseOrderReceiptCost($purchaseOrderReceiptCost)
            );

            if ((float) $purchaseOrderReceiptCost->amount > 0) {
                $this->journalEntryActions->create($this->buildJournalEntryCreateDTO($purchaseOrderReceiptCost));
            }

            $this->flushCache();

            return $purchaseOrderReceiptCost;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderReceiptCost $purchaseOrderReceiptCost, PurchaseOrderReceiptCostUpdateDTO $data): PurchaseOrderReceiptCost
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderReceiptCost->code = $this->generateUniqueCode($purchaseOrderReceiptCost->company_id, $data->code, $purchaseOrderReceiptCost->id);
            $purchaseOrderReceiptCost->date = $this->generateDate($data->date);
            $purchaseOrderReceiptCost->name = $data->name;
            $purchaseOrderReceiptCost->cash_account_id = $data->cashAccountId;
            $purchaseOrderReceiptCost->amount = $data->amount;
            $purchaseOrderReceiptCost->remarks = $data->remarks;
            $purchaseOrderReceiptCost->save();

            $cashTransaction = $purchaseOrderReceiptCost->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPurchaseOrderReceiptCost($purchaseOrderReceiptCost)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromPurchaseOrderReceiptCost($purchaseOrderReceiptCost)
                );
            }

            $journalEntry = $purchaseOrderReceiptCost->journalEntry;
            if ((float) $purchaseOrderReceiptCost->amount > 0) {
                if (! $journalEntry) {
                    $this->journalEntryActions->create($this->buildJournalEntryCreateDTO($purchaseOrderReceiptCost));
                } else {
                    $journalEntryDTO = new JournalEntryUpdateDTO(
                        branchId: $purchaseOrderReceiptCost->branch_id,
                        code: $journalEntry->code,
                        date: $purchaseOrderReceiptCost->date,
                        journalType: JournalEntryTypeEnum::TRANSACTION->value,
                        referenceNo: $purchaseOrderReceiptCost->code,
                        remarks: $purchaseOrderReceiptCost->remarks,
                        items: $this->buildJournalEntryItems($purchaseOrderReceiptCost),
                    );
                    $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
                }
            } elseif ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            $this->flushCache();

            return $purchaseOrderReceiptCost;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderReceiptCost $purchaseOrderReceiptCost): bool
    {
        $timer_start = microtime(true);

        try {
            $cashTransaction = $purchaseOrderReceiptCost->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $purchaseOrderReceiptCost->journalEntry;
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            $result = $purchaseOrderReceiptCost->delete();

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

    private function buildJournalEntryCreateDTO(PurchaseOrderReceiptCost $purchaseOrderReceiptCost): JournalEntryCreateDTO
    {
        return new JournalEntryCreateDTO(
            companyId: $purchaseOrderReceiptCost->company_id,
            branchId: $purchaseOrderReceiptCost->branch_id,
            code: config('dcslab.KEYWORDS.AUTO'),
            date: $purchaseOrderReceiptCost->date,
            journalType: JournalEntryTypeEnum::TRANSACTION->value,
            sourceType: PurchaseOrderReceiptCost::class,
            sourceId: $purchaseOrderReceiptCost->id,
            referenceNo: $purchaseOrderReceiptCost->code,
            remarks: $purchaseOrderReceiptCost->remarks,
            items: $this->buildJournalEntryItems($purchaseOrderReceiptCost),
        );
    }

    /**
     * P4 — Receipt cost: Dr inventory / Cr cash account COA.
     *
     * @return JournalEntryItemDTO[]
     */
    private function buildJournalEntryItems(PurchaseOrderReceiptCost $purchaseOrderReceiptCost): array
    {
        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $purchaseOrderReceiptCost->company->assetCurrentInventoryChartOfAccount?->id,
            sequence: count($items) + 1,
            debit: (float) $purchaseOrderReceiptCost->amount,
            credit: 0,
            remarks: $purchaseOrderReceiptCost->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $purchaseOrderReceiptCost->cashAccount?->chartOfAccount?->id,
            sequence: count($items) + 1,
            debit: 0,
            credit: (float) $purchaseOrderReceiptCost->amount,
            remarks: $purchaseOrderReceiptCost->remarks,
        );

        return $items;
    }
}
