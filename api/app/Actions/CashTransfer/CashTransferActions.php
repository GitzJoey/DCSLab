<?php

namespace App\Actions\CashTransfer;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\CashTransferCreateDTO;
use App\DTOs\CashTransferUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\Models\CashTransfer;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class CashTransferActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'sourceCashAccount',
        'destinationCashAccount',
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
        ?ExecuteDTO $execute
    ) {
        $query = CashTransfer::select('cash_transfers.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'cash_transfers.company_id')
            ->whereCompanyId('cash_transfers', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('cash_transfers.code', 'like', '%'.$search.'%')
                        ->orWhere('cash_transfers.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($branchId) {
                $query->where('cash_transfers.branch_id', $branchId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('cash_transfers.date', 'desc')
            ->orderBy('cash_transfers.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_cash_transfer_'.implode('_', $cacheParams);

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

    public function read(CashTransfer $cashTransfer): CashTransfer
    {
        return $cashTransfer->load(self::LIST_EAGER_LOADS);
    }

    public function create(CashTransferCreateDTO $data): CashTransfer
    {
        $timer_start = microtime(true);

        try {
            $cashTransfer = new CashTransfer();
            $cashTransfer->company_id = $data->companyId;
            $cashTransfer->branch_id = $data->branchId;
            $cashTransfer->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $cashTransfer->date = $data->date;
            $cashTransfer->source_cash_account_id = $data->sourceCashAccountId;
            $cashTransfer->destination_cash_account_id = $data->destinationCashAccountId;
            $cashTransfer->amount = $data->amount;
            $cashTransfer->remarks = $data->remarks;
            $cashTransfer->save();

            $dto = CashTransactionCreateDTO::fromCashTransferSource($cashTransfer);
            $this->cashTransactionActions->create($dto);

            $dto = CashTransactionCreateDTO::fromCashTransferDestination($cashTransfer);
            $this->cashTransactionActions->create($dto);

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $cashTransfer->company_id,
                branchId: $cashTransfer->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $cashTransfer->date,
                sourceType: CashTransfer::class,
                sourceId: $cashTransfer->id,
                referenceNo: $cashTransfer->code,
                remarks: $cashTransfer->remarks,
                items: (function () use ($cashTransfer) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        chartOfAccountId: $cashTransfer->destinationCashAccount?->chartOfAccount?->id,
                        sequence: count($items) + 1,
                        debit: (float) $cashTransfer->amount,
                        credit: 0,
                        remarks: $cashTransfer->remarks,
                    );

                    $items[] = new JournalEntryItemDTO(
                        chartOfAccountId: $cashTransfer->sourceCashAccount?->chartOfAccount?->id,
                        sequence: count($items) + 1,
                        debit: 0,
                        credit: (float) $cashTransfer->amount,
                        remarks: $cashTransfer->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            $this->flushCache();

            return $cashTransfer;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(CashTransfer $cashTransfer, CashTransferUpdateDTO $data): CashTransfer
    {
        $timer_start = microtime(true);

        try {
            $cashTransactionSource = $cashTransfer->sourceCashTransaction;
            $cashTransactionDestination = $cashTransfer->destinationCashTransaction;

            $cashTransfer->code = $this->generateUniqueCode($cashTransfer->company_id, $data->code, $cashTransfer->id);
            $cashTransfer->date = $data->date;
            $cashTransfer->source_cash_account_id = $data->sourceCashAccountId;
            $cashTransfer->destination_cash_account_id = $data->destinationCashAccountId;
            $cashTransfer->amount = $data->amount;
            $cashTransfer->remarks = $data->remarks;
            $cashTransfer->save();

            if (! $cashTransactionSource) {
                $dto = CashTransactionCreateDTO::fromCashTransferSource($cashTransfer);
                $this->cashTransactionActions->create($dto);
            } else {
                $dto = CashTransactionUpdateDTO::fromCashTransferSource($cashTransfer);
                $this->cashTransactionActions->update($cashTransactionSource, $dto);
            }

            if (! $cashTransactionDestination) {
                $dto = CashTransactionCreateDTO::fromCashTransferDestination($cashTransfer);
                $this->cashTransactionActions->create($dto);
            } else {
                $dto = CashTransactionUpdateDTO::fromCashTransferDestination($cashTransfer);
                $this->cashTransactionActions->update($cashTransactionDestination, $dto);
            }

            $journalEntry = $cashTransfer->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $cashTransfer->company_id,
                    branchId: $cashTransfer->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $cashTransfer->date,
                    sourceType: CashTransfer::class,
                    sourceId: $cashTransfer->id,
                    referenceNo: $cashTransfer->code,
                    remarks: $cashTransfer->remarks,
                    items: (function () use ($cashTransfer) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            chartOfAccountId: $cashTransfer->destinationCashAccount?->chartOfAccount?->id,
                            sequence: count($items) + 1,
                            debit: (float) $cashTransfer->amount,
                            credit: 0,
                            remarks: $cashTransfer->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            chartOfAccountId: $cashTransfer->sourceCashAccount?->chartOfAccount?->id,
                            sequence: count($items) + 1,
                            debit: 0,
                            credit: (float) $cashTransfer->amount,
                            remarks: $cashTransfer->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $cashTransfer->branch_id,
                    code: $journalEntry->code,
                    date: $cashTransfer->date,
                    referenceNo: $cashTransfer->code,
                    remarks: $cashTransfer->remarks,
                    items: (function () use ($cashTransfer) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            chartOfAccountId: $cashTransfer->destinationCashAccount?->chartOfAccount?->id,
                            sequence: count($items) + 1,
                            debit: (float) $cashTransfer->amount,
                            credit: 0,
                            remarks: $cashTransfer->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            chartOfAccountId: $cashTransfer->sourceCashAccount?->chartOfAccount?->id,
                            sequence: count($items) + 1,
                            debit: 0,
                            credit: (float) $cashTransfer->amount,
                            remarks: $cashTransfer->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            $this->flushCache();

            return $cashTransfer->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CashTransfer $cashTransfer): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransactionSource = $cashTransfer->sourceCashTransaction;
            if ($cashTransactionSource) {
                $this->cashTransactionActions->delete($cashTransactionSource);
            }

            $cashTransactionDestination = $cashTransfer->destinationCashTransaction;
            if ($cashTransactionDestination) {
                $this->cashTransactionActions->delete($cashTransactionDestination);
            }

            $journalEntry = $cashTransfer->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $cashTransfer->delete();

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
            $tryCount = 0;

            do {
                $count = CashTransfer::whereCompanyId('cash_transfers', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'CT'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = CashTransfer::whereCompanyId('cash_transfers', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
