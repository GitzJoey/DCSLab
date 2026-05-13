<?php

namespace App\Actions\ReceivablePayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\Receivable\ReceivableActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\ReceivablePaymentCreateDTO;
use App\DTOs\ReceivablePaymentUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\ReceivablePayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class ReceivablePaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'receivable.category',
        'receivable.customer',
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

        ?int $receivableId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = ReceivablePayment::with(self::LIST_EAGER_LOADS)
            ->select('receivable_payments.*')
            ->whereCompanyId('receivable_payments', $companyId)
            ->whereBranchId('receivable_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $receivableId,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $receivableId,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('receivable_payments.code', 'like', '%'.$search.'%')
                            ->orWhere('receivable_payments.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($receivableId)) {
                    $query->where('receivable_payments.receivable_id', $receivableId);
                }
            });

            if ($includeId) {
                $query->orWhere('receivable_payments.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(receivable_payments.id, '.$includeId.') desc');
        }
        $query->orderBy('receivable_payments.date', 'desc');
        $query->orderBy('receivable_payments.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $receivableId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_receivable_payment_'.implode('_', $cacheParams);

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

    public function read(ReceivablePayment $receivablePayment): ReceivablePayment
    {
        return $receivablePayment->load(self::LIST_EAGER_LOADS);
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
                $count = ReceivablePayment::whereCompanyId('receivable_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'RPM'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = ReceivablePayment::whereCompanyId('receivable_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(ReceivablePaymentCreateDTO $data, bool $updateParentSummary = true): ReceivablePayment
    {
        $timer_start = microtime(true);

        try {
            $receivablePayment = new ReceivablePayment();
            $receivablePayment->company_id = $data->companyId;
            $receivablePayment->branch_id = $data->branchId;
            $receivablePayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $receivablePayment->date = $this->generateDate($data->date);
            $receivablePayment->receivable_id = $data->receivableId;
            $receivablePayment->cash_account_id = $data->cashAccountId;
            $receivablePayment->amount = $data->amount;
            $receivablePayment->remarks = $data->remarks;
            $receivablePayment->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromReceivablePayment($receivablePayment)
            );

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $receivablePayment->company_id,
                branchId: $receivablePayment->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $receivablePayment->date,
                sourceType: ReceivablePayment::class,
                sourceId: $receivablePayment->id,
                referenceNo: $receivablePayment->code,
                remarks: $receivablePayment->remarks,
                items: (function () use ($receivablePayment) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $receivablePayment->cashAccount?->chartOfAccount?->id,
                        debit: (float) $receivablePayment->amount,
                        credit: 0,
                        remarks: $receivablePayment->remarks,
                    );

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $receivablePayment->receivable->customer?->chartOfAccount?->id,
                        debit: 0,
                        credit: (float) $receivablePayment->amount,
                        remarks: $receivablePayment->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            if ($updateParentSummary) {
                ReceivableActions::updateSummary($receivablePayment->receivable);
                $receivablePayment->refresh();
            }

            $this->flushCache();

            return $receivablePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(ReceivablePayment $receivablePayment, ReceivablePaymentUpdateDTO $data, bool $updateParentSummary = true): ReceivablePayment
    {
        $timer_start = microtime(true);

        try {
            $receivablePayment->code = $this->generateUniqueCode($receivablePayment->company_id, $data->code, $receivablePayment->id);
            $receivablePayment->date = $this->generateDate($data->date);
            $receivablePayment->cash_account_id = $data->cashAccountId;
            $receivablePayment->amount = $data->amount;
            $receivablePayment->remarks = $data->remarks;
            $receivablePayment->save();

            $cashTransaction = $receivablePayment->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromReceivablePayment($receivablePayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromReceivablePayment($receivablePayment)
                );
            }

            $journalEntry = $receivablePayment->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $receivablePayment->company_id,
                    branchId: $receivablePayment->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $receivablePayment->date,
                    sourceType: ReceivablePayment::class,
                    sourceId: $receivablePayment->id,
                    referenceNo: $receivablePayment->code,
                    remarks: $receivablePayment->remarks,
                    items: (function () use ($receivablePayment) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $receivablePayment->cashAccount?->chartOfAccount?->id,
                            debit: (float) $receivablePayment->amount,
                            credit: 0,
                            remarks: $receivablePayment->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $receivablePayment->receivable->customer?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $receivablePayment->amount,
                            remarks: $receivablePayment->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $receivablePayment->branch_id,
                    code: $journalEntry->code,
                    date: $receivablePayment->date,
                    referenceNo: $receivablePayment->code,
                    remarks: $receivablePayment->remarks,
                    items: (function () use ($receivablePayment) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $receivablePayment->cashAccount?->chartOfAccount?->id,
                            debit: (float) $receivablePayment->amount,
                            credit: 0,
                            remarks: $receivablePayment->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $receivablePayment->receivable->customer?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $receivablePayment->amount,
                            remarks: $receivablePayment->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            if ($updateParentSummary) {
                ReceivableActions::updateSummary($receivablePayment->receivable);
                $receivablePayment->refresh();
            }

            $this->flushCache();

            return $receivablePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(
        ReceivablePayment $receivablePayment,
        bool $updateParentSummary = true,
    ): bool {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $receivablePayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $receivablePayment->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $receivablePayment->delete();

            if ($updateParentSummary) {
                ReceivableActions::updateSummary($receivablePayment->receivable);
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
}
