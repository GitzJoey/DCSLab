<?php

namespace App\Actions\CapitalTransaction;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\DTOs\CapitalTransactionCreateDTO;
use App\DTOs\CapitalTransactionUpdateDTO;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryLineDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\Enums\CapitalTransactionTypeEnum;
use App\Models\CapitalTransaction;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class CapitalTransactionActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'investor',
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
        ?int $investorId,
        ?int $cashAccountId,
        ?string $type,

        ?ExecuteDTO $execute
    ) {
        $query = CapitalTransaction::select('capital_transactions.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'capital_transactions.company_id')
            ->whereCompanyId('capital_transactions', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $investorId, $cashAccountId, $type) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('capital_transactions.code', 'like', '%'.$search.'%')
                        ->orWhere('capital_transactions.type', 'like', '%'.$search.'%')
                        ->orWhere('capital_transactions.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($branchId) {
                $query->where('capital_transactions.branch_id', $branchId);
            }

            if ($investorId) {
                $query->where('capital_transactions.investor_id', $investorId);
            }

            if ($cashAccountId) {
                $query->where('capital_transactions.cash_account_id', $cashAccountId);
            }

            if ($type) {
                $query->where('capital_transactions.type', $type);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('capital_transactions.date', 'desc')
            ->orderBy('capital_transactions.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $investorId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $type ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_capital_transaction_'.implode('_', $cacheParams);

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

    public function getTypes(): array
    {
        return [
            ['name' => 'views.capital_transaction.types.in', 'code' => CapitalTransactionTypeEnum::IN->value],
            ['name' => 'views.capital_transaction.types.out', 'code' => CapitalTransactionTypeEnum::OUT->value],
        ];
    }

    public function read(CapitalTransaction $capitalTransaction): CapitalTransaction
    {
        return $capitalTransaction->load(self::LIST_EAGER_LOADS);
    }

    public function create(CapitalTransactionCreateDTO $data): CapitalTransaction
    {
        $timer_start = microtime(true);

        try {
            $capitalTransaction = new CapitalTransaction();
            $capitalTransaction->company_id = $data->companyId;
            $capitalTransaction->branch_id = $data->branchId;
            $capitalTransaction->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $capitalTransaction->date = $data->date;
            $capitalTransaction->investor_id = $data->investorId;
            $capitalTransaction->cash_account_id = $data->cashAccountId;
            $capitalTransaction->type = $data->type;
            $capitalTransaction->amount = $data->amount;
            $capitalTransaction->remarks = $data->remarks;
            $capitalTransaction->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromCapitalTransaction($capitalTransaction)
            );

            $cashAccountChartOfAccount = $capitalTransaction->cashAccount->chartOfAccount;
            if (! $cashAccountChartOfAccount) {
                throw new InvalidArgumentException('Capital transaction cash account chart of account must exist.');
            }

            $type = $capitalTransaction->type instanceof CapitalTransactionTypeEnum
                ? $capitalTransaction->type
                : CapitalTransactionTypeEnum::resolveToEnum($capitalTransaction->type);

            if ($type === CapitalTransactionTypeEnum::IN) {
                $investorChartOfAccount = $capitalTransaction->investor->additionalCapitalChartOfAccount;
                if (! $investorChartOfAccount) {
                    throw new InvalidArgumentException('Capital transaction investor additional capital chart of account must exist.');
                }

                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $capitalTransaction->company_id,
                    branchId: $capitalTransaction->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $capitalTransaction->date,
                    sourceType: CapitalTransaction::class,
                    sourceId: $capitalTransaction->id,
                    referenceNo: $capitalTransaction->code,
                    remarks: $capitalTransaction->remarks,
                    lines: [
                        new JournalEntryLineDTO(
                            chartOfAccountId: $cashAccountChartOfAccount->id,
                            debit: (float) $capitalTransaction->amount,
                            credit: 0,
                            remarks: $capitalTransaction->remarks,
                        ),
                        new JournalEntryLineDTO(
                            chartOfAccountId: $investorChartOfAccount->id,
                            debit: 0,
                            credit: (float) $capitalTransaction->amount,
                            remarks: $capitalTransaction->remarks,
                        ),
                    ],
                );
            } else {
                $investorChartOfAccount = $capitalTransaction->investor->drawingChartOfAccount;
                if (! $investorChartOfAccount) {
                    throw new InvalidArgumentException('Capital transaction investor drawing chart of account must exist.');
                }

                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $capitalTransaction->company_id,
                    branchId: $capitalTransaction->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $capitalTransaction->date,
                    sourceType: CapitalTransaction::class,
                    sourceId: $capitalTransaction->id,
                    referenceNo: $capitalTransaction->code,
                    remarks: $capitalTransaction->remarks,
                    lines: [
                        new JournalEntryLineDTO(
                            chartOfAccountId: $investorChartOfAccount->id,
                            debit: (float) $capitalTransaction->amount,
                            credit: 0,
                            remarks: $capitalTransaction->remarks,
                        ),
                        new JournalEntryLineDTO(
                            chartOfAccountId: $cashAccountChartOfAccount->id,
                            debit: 0,
                            credit: (float) $capitalTransaction->amount,
                            remarks: $capitalTransaction->remarks,
                        ),
                    ],
                );
            }
            $this->journalEntryActions->create($journalEntryDTO);

            $this->flushCache();

            return $capitalTransaction;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(CapitalTransaction $capitalTransaction, CapitalTransactionUpdateDTO $data): CapitalTransaction
    {
        $timer_start = microtime(true);

        try {
            $capitalTransaction->code = $this->generateUniqueCode($capitalTransaction->company_id, $data->code, $capitalTransaction->id);
            $capitalTransaction->date = $data->date;
            $capitalTransaction->investor_id = $data->investorId;
            $capitalTransaction->cash_account_id = $data->cashAccountId;
            $capitalTransaction->type = $data->type;
            $capitalTransaction->amount = $data->amount;
            $capitalTransaction->remarks = $data->remarks;
            $capitalTransaction->save();

            $cashTransaction = $capitalTransaction->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromCapitalTransaction($capitalTransaction)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromCapitalTransaction($capitalTransaction)
                );
            }

            $cashAccountChartOfAccount = $capitalTransaction->cashAccount->chartOfAccount;
            if (! $cashAccountChartOfAccount) {
                throw new InvalidArgumentException('Capital transaction cash account chart of account must exist.');
            }

            $type = $capitalTransaction->type instanceof CapitalTransactionTypeEnum
                ? $capitalTransaction->type
                : CapitalTransactionTypeEnum::resolveToEnum($capitalTransaction->type);

            $journalEntry = $capitalTransaction->journalEntry;
            if ($type === CapitalTransactionTypeEnum::IN) {
                $investorChartOfAccount = $capitalTransaction->investor->additionalCapitalChartOfAccount;
                if (! $investorChartOfAccount) {
                    throw new InvalidArgumentException('Capital transaction investor additional capital chart of account must exist.');
                }

                if (! $journalEntry) {
                    $journalEntryDTO = new JournalEntryCreateDTO(
                        companyId: $capitalTransaction->company_id,
                        branchId: $capitalTransaction->branch_id,
                        code: config('dcslab.KEYWORDS.AUTO'),
                        date: $capitalTransaction->date,
                        sourceType: CapitalTransaction::class,
                        sourceId: $capitalTransaction->id,
                        referenceNo: $capitalTransaction->code,
                        remarks: $capitalTransaction->remarks,
                        lines: [
                            new JournalEntryLineDTO(
                                chartOfAccountId: $cashAccountChartOfAccount->id,
                                debit: (float) $capitalTransaction->amount,
                                credit: 0,
                                remarks: $capitalTransaction->remarks,
                            ),
                            new JournalEntryLineDTO(
                                chartOfAccountId: $investorChartOfAccount->id,
                                debit: 0,
                                credit: (float) $capitalTransaction->amount,
                                remarks: $capitalTransaction->remarks,
                            ),
                        ],
                    );
                    $this->journalEntryActions->create($journalEntryDTO);
                } else {
                    $journalEntryDTO = new JournalEntryUpdateDTO(
                        branchId: $capitalTransaction->branch_id,
                        code: $journalEntry->code,
                        date: $capitalTransaction->date,
                        referenceNo: $capitalTransaction->code,
                        remarks: $capitalTransaction->remarks,
                        lines: [
                            new JournalEntryLineDTO(
                                chartOfAccountId: $cashAccountChartOfAccount->id,
                                debit: (float) $capitalTransaction->amount,
                                credit: 0,
                                remarks: $capitalTransaction->remarks,
                            ),
                            new JournalEntryLineDTO(
                                chartOfAccountId: $investorChartOfAccount->id,
                                debit: 0,
                                credit: (float) $capitalTransaction->amount,
                                remarks: $capitalTransaction->remarks,
                            ),
                        ],
                    );
                    $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
                }
            } else {
                $investorChartOfAccount = $capitalTransaction->investor->drawingChartOfAccount;
                if (! $investorChartOfAccount) {
                    throw new InvalidArgumentException('Capital transaction investor drawing chart of account must exist.');
                }

                if (! $journalEntry) {
                    $journalEntryDTO = new JournalEntryCreateDTO(
                        companyId: $capitalTransaction->company_id,
                        branchId: $capitalTransaction->branch_id,
                        code: config('dcslab.KEYWORDS.AUTO'),
                        date: $capitalTransaction->date,
                        sourceType: CapitalTransaction::class,
                        sourceId: $capitalTransaction->id,
                        referenceNo: $capitalTransaction->code,
                        remarks: $capitalTransaction->remarks,
                        lines: [
                            new JournalEntryLineDTO(
                                chartOfAccountId: $investorChartOfAccount->id,
                                debit: (float) $capitalTransaction->amount,
                                credit: 0,
                                remarks: $capitalTransaction->remarks,
                            ),
                            new JournalEntryLineDTO(
                                chartOfAccountId: $cashAccountChartOfAccount->id,
                                debit: 0,
                                credit: (float) $capitalTransaction->amount,
                                remarks: $capitalTransaction->remarks,
                            ),
                        ],
                    );
                    $this->journalEntryActions->create($journalEntryDTO);
                } else {
                    $journalEntryDTO = new JournalEntryUpdateDTO(
                        branchId: $capitalTransaction->branch_id,
                        code: $journalEntry->code,
                        date: $capitalTransaction->date,
                        referenceNo: $capitalTransaction->code,
                        remarks: $capitalTransaction->remarks,
                        lines: [
                            new JournalEntryLineDTO(
                                chartOfAccountId: $investorChartOfAccount->id,
                                debit: (float) $capitalTransaction->amount,
                                credit: 0,
                                remarks: $capitalTransaction->remarks,
                            ),
                            new JournalEntryLineDTO(
                                chartOfAccountId: $cashAccountChartOfAccount->id,
                                debit: 0,
                                credit: (float) $capitalTransaction->amount,
                                remarks: $capitalTransaction->remarks,
                            ),
                        ],
                    );
                    $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
                }
            }

            $this->flushCache();

            return $capitalTransaction->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CapitalTransaction $capitalTransaction): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $capitalTransaction->cashTransaction;
            if ($cashTransaction) $this->cashTransactionActions->delete($cashTransaction);

            $journalEntry = $capitalTransaction->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $capitalTransaction->delete();

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
                $count = CapitalTransaction::whereCompanyId('capital_transactions', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'CT'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = CapitalTransaction::whereCompanyId('capital_transactions', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
