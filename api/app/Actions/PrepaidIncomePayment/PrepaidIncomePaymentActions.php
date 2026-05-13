<?php

namespace App\Actions\PrepaidIncomePayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\PrepaidIncome\PrepaidIncomeActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\PrepaidIncomePaymentCreateDTO;
use App\DTOs\PrepaidIncomePaymentUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PrepaidIncomePayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PrepaidIncomePaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'prepaidIncome.category',
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

        ?int $prepaidIncomeId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = PrepaidIncomePayment::with(self::LIST_EAGER_LOADS)
            ->select('prepaid_income_payments.*')
            ->whereCompanyId('prepaid_income_payments', $companyId)
            ->whereBranchId('prepaid_income_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $prepaidIncomeId,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $prepaidIncomeId,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('prepaid_income_payments.code', 'like', '%'.$search.'%')
                            ->orWhere('prepaid_income_payments.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($prepaidIncomeId)) {
                    $query->where('prepaid_income_payments.prepaid_income_id', $prepaidIncomeId);
                }
            });

            if ($includeId) {
                $query->orWhere('prepaid_income_payments.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(prepaid_income_payments.id, '.$includeId.') desc');
        }
        $query->orderBy('prepaid_income_payments.date', 'desc');
        $query->orderBy('prepaid_income_payments.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $prepaidIncomeId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_prepaid_income_payment_'.implode('_', $cacheParams);

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

    public function read(PrepaidIncomePayment $prepaidIncomePayment): PrepaidIncomePayment
    {
        return $prepaidIncomePayment->load(self::LIST_EAGER_LOADS);
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
                $count = PrepaidIncomePayment::whereCompanyId('prepaid_income_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PREIP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PrepaidIncomePayment::whereCompanyId('prepaid_income_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PrepaidIncomePaymentCreateDTO $data, bool $updateParentSummary = true): PrepaidIncomePayment
    {
        $timer_start = microtime(true);

        try {
            $prepaidIncomePayment = new PrepaidIncomePayment();
            $prepaidIncomePayment->company_id = $data->companyId;
            $prepaidIncomePayment->branch_id = $data->branchId;
            $prepaidIncomePayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $prepaidIncomePayment->date = $this->generateDate($data->date);
            $prepaidIncomePayment->prepaid_income_id = $data->prepaidIncomeId;
            $prepaidIncomePayment->cash_account_id = $data->cashAccountId;
            $prepaidIncomePayment->amount = $data->amount;
            $prepaidIncomePayment->remarks = $data->remarks;
            $prepaidIncomePayment->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromPrepaidIncomePayment($prepaidIncomePayment)
            );

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $prepaidIncomePayment->company_id,
                branchId: $prepaidIncomePayment->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $prepaidIncomePayment->date,
                sourceType: PrepaidIncomePayment::class,
                sourceId: $prepaidIncomePayment->id,
                referenceNo: $prepaidIncomePayment->code,
                remarks: $prepaidIncomePayment->remarks,
                items: (function () use ($prepaidIncomePayment) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $prepaidIncomePayment->cashAccount?->chartOfAccount?->id,
                        debit: (float) $prepaidIncomePayment->amount,
                        credit: 0,
                        remarks: $prepaidIncomePayment->remarks,
                    );

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $prepaidIncomePayment->company->assetCurrentAccountReceivableChartOfAccount?->id,
                        debit: 0,
                        credit: (float) $prepaidIncomePayment->amount,
                        remarks: $prepaidIncomePayment->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            if ($updateParentSummary) {
                PrepaidIncomeActions::updateSummary($prepaidIncomePayment->prepaidIncome);
                $prepaidIncomePayment->refresh();
            }

            $this->flushCache();

            return $prepaidIncomePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PrepaidIncomePayment $prepaidIncomePayment, PrepaidIncomePaymentUpdateDTO $data, bool $updateParentSummary = true): PrepaidIncomePayment
    {
        $timer_start = microtime(true);

        try {
            $prepaidIncomePayment->code = $this->generateUniqueCode($prepaidIncomePayment->company_id, $data->code, $prepaidIncomePayment->id);
            $prepaidIncomePayment->date = $this->generateDate($data->date);
            $prepaidIncomePayment->cash_account_id = $data->cashAccountId;
            $prepaidIncomePayment->amount = $data->amount;
            $prepaidIncomePayment->remarks = $data->remarks;
            $prepaidIncomePayment->save();

            $cashTransaction = $prepaidIncomePayment->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPrepaidIncomePayment($prepaidIncomePayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromPrepaidIncomePayment($prepaidIncomePayment)
                );
            }

            $journalEntry = $prepaidIncomePayment->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $prepaidIncomePayment->company_id,
                    branchId: $prepaidIncomePayment->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $prepaidIncomePayment->date,
                    sourceType: PrepaidIncomePayment::class,
                    sourceId: $prepaidIncomePayment->id,
                    referenceNo: $prepaidIncomePayment->code,
                    remarks: $prepaidIncomePayment->remarks,
                    items: (function () use ($prepaidIncomePayment) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $prepaidIncomePayment->cashAccount?->chartOfAccount?->id,
                            debit: (float) $prepaidIncomePayment->amount,
                            credit: 0,
                            remarks: $prepaidIncomePayment->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $prepaidIncomePayment->company->assetCurrentAccountReceivableChartOfAccount?->id,
                            debit: 0,
                            credit: (float) $prepaidIncomePayment->amount,
                            remarks: $prepaidIncomePayment->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $prepaidIncomePayment->branch_id,
                    code: $journalEntry->code,
                    date: $prepaidIncomePayment->date,
                    referenceNo: $prepaidIncomePayment->code,
                    remarks: $prepaidIncomePayment->remarks,
                    items: (function () use ($prepaidIncomePayment) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $prepaidIncomePayment->cashAccount?->chartOfAccount?->id,
                            debit: (float) $prepaidIncomePayment->amount,
                            credit: 0,
                            remarks: $prepaidIncomePayment->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $prepaidIncomePayment->company->assetCurrentAccountReceivableChartOfAccount?->id,
                            debit: 0,
                            credit: (float) $prepaidIncomePayment->amount,
                            remarks: $prepaidIncomePayment->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            if ($updateParentSummary) {
                PrepaidIncomeActions::updateSummary($prepaidIncomePayment->prepaidIncome);
                $prepaidIncomePayment->refresh();
            }

            $this->flushCache();

            return $prepaidIncomePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(
        PrepaidIncomePayment $prepaidIncomePayment,
        bool $updateParentSummary = true,
    ): bool {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $prepaidIncomePayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $prepaidIncomePayment->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $prepaidIncomePayment->delete();

            if ($updateParentSummary) {
                PrepaidIncomeActions::updateSummary($prepaidIncomePayment->prepaidIncome);
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
