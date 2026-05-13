<?php

namespace App\Actions\DebtPayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\Debt\DebtActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\DebtPaymentCreateDTO;
use App\DTOs\DebtPaymentUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\DebtPayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class DebtPaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'debt.category',
        'debt.creditor',
        'debt.supplier',
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

        ?int $debtId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = DebtPayment::with(self::LIST_EAGER_LOADS)
            ->select('debt_payments.*')
            ->whereCompanyId('debt_payments', $companyId)
            ->whereBranchId('debt_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $debtId,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $debtId,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('debt_payments.code', 'like', '%'.$search.'%')
                            ->orWhere('debt_payments.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($debtId)) {
                    $query->where('debt_payments.debt_id', $debtId);
                }
            });

            if ($includeId) {
                $query->orWhere('debt_payments.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(debt_payments.id, '.$includeId.') desc');
        }
        $query->orderBy('debt_payments.date', 'desc');
        $query->orderBy('debt_payments.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $debtId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_debt_payment_'.implode('_', $cacheParams);

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

    public function read(DebtPayment $debtPayment): DebtPayment
    {
        return $debtPayment->load(self::LIST_EAGER_LOADS);
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
                $count = DebtPayment::whereCompanyId('debt_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'DPM'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = DebtPayment::whereCompanyId('debt_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(DebtPaymentCreateDTO $data, bool $updateParentSummary = true): DebtPayment
    {
        $timer_start = microtime(true);

        try {
            $debtPayment = new DebtPayment();
            $debtPayment->company_id = $data->companyId;
            $debtPayment->branch_id = $data->branchId;
            $debtPayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $debtPayment->date = $this->generateDate($data->date);
            $debtPayment->debt_id = $data->debtId;
            $debtPayment->cash_account_id = $data->cashAccountId;
            $debtPayment->amount = $data->amount;
            $debtPayment->remarks = $data->remarks;
            $debtPayment->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromDebtPayment($debtPayment)
            );

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $debtPayment->company_id,
                branchId: $debtPayment->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $debtPayment->date,
                sourceType: DebtPayment::class,
                sourceId: $debtPayment->id,
                referenceNo: $debtPayment->code,
                remarks: $debtPayment->remarks,
                items: (function () use ($debtPayment) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: (function () use ($debtPayment) {
                            if ($debtPayment->debt->supplier_id) {
                                return $debtPayment->debt->supplier?->chartOfAccount?->id;
                            }

                            if ($debtPayment->debt->creditor_id) {
                                return $debtPayment->debt->creditor?->chartOfAccount?->id;
                            }

                            return $debtPayment->debt->company->liabilityAccountPayableChartOfAccount?->id;
                        })(),
                        debit: (float) $debtPayment->amount,
                        credit: 0,
                        remarks: $debtPayment->remarks,
                    );

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $debtPayment->cashAccount?->chartOfAccount?->id,
                        debit: 0,
                        credit: (float) $debtPayment->amount,
                        remarks: $debtPayment->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            if ($updateParentSummary) {
                DebtActions::updateSummary($debtPayment->debt);
                $debtPayment->refresh();
            }

            $this->flushCache();

            return $debtPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(DebtPayment $debtPayment, DebtPaymentUpdateDTO $data, bool $updateParentSummary = true): DebtPayment
    {
        $timer_start = microtime(true);

        try {
            $debtPayment->code = $this->generateUniqueCode($debtPayment->company_id, $data->code, $debtPayment->id);
            $debtPayment->date = $this->generateDate($data->date);
            $debtPayment->cash_account_id = $data->cashAccountId;
            $debtPayment->amount = $data->amount;
            $debtPayment->remarks = $data->remarks;
            $debtPayment->save();

            $cashTransaction = $debtPayment->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromDebtPayment($debtPayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromDebtPayment($debtPayment)
                );
            }

            $journalEntry = $debtPayment->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $debtPayment->company_id,
                    branchId: $debtPayment->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $debtPayment->date,
                    sourceType: DebtPayment::class,
                    sourceId: $debtPayment->id,
                    referenceNo: $debtPayment->code,
                    remarks: $debtPayment->remarks,
                    items: (function () use ($debtPayment) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: (function () use ($debtPayment) {
                                if ($debtPayment->debt->supplier_id) {
                                    return $debtPayment->debt->supplier?->chartOfAccount?->id;
                                }

                                if ($debtPayment->debt->creditor_id) {
                                    return $debtPayment->debt->creditor?->chartOfAccount?->id;
                                }

                                return $debtPayment->debt->company->liabilityAccountPayableChartOfAccount?->id;
                            })(),
                            debit: (float) $debtPayment->amount,
                            credit: 0,
                            remarks: $debtPayment->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $debtPayment->cashAccount?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $debtPayment->amount,
                            remarks: $debtPayment->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $debtPayment->branch_id,
                    code: $journalEntry->code,
                    date: $debtPayment->date,
                    referenceNo: $debtPayment->code,
                    remarks: $debtPayment->remarks,
                    items: (function () use ($debtPayment) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: (function () use ($debtPayment) {
                                if ($debtPayment->debt->supplier_id) {
                                    return $debtPayment->debt->supplier?->chartOfAccount?->id;
                                }

                                if ($debtPayment->debt->creditor_id) {
                                    return $debtPayment->debt->creditor?->chartOfAccount?->id;
                                }

                                return $debtPayment->debt->company->liabilityAccountPayableChartOfAccount?->id;
                            })(),
                            debit: (float) $debtPayment->amount,
                            credit: 0,
                            remarks: $debtPayment->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $debtPayment->cashAccount?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $debtPayment->amount,
                            remarks: $debtPayment->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            if ($updateParentSummary) {
                DebtActions::updateSummary($debtPayment->debt);
                $debtPayment->refresh();
            }

            $this->flushCache();

            return $debtPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(
        DebtPayment $debtPayment,
        bool $updateParentSummary = true,
    ): bool {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $debtPayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $debtPayment->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $debtPayment->delete();

            if ($updateParentSummary) {
                DebtActions::updateSummary($debtPayment->debt);
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
