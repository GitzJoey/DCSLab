<?php

namespace App\Actions\Debt;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\DebtPayment\DebtPaymentActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\DebtCreateDTO;
use App\DTOs\DebtPaymentCreateDTO;
use App\DTOs\DebtPaymentUpdateDTO;
use App\DTOs\DebtUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryLineDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\Debt;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class DebtActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'category',
        'creditor',
        'supplier',
        'cashAccount',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'category',
        'creditor',
        'supplier',
        'cashAccount',
        'payments.cashAccount',
    ];

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
        private readonly DebtPaymentActions $debtPaymentActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $categoryId,
        ?int $creditorId,
        ?int $supplierId,
        ?bool $isPaidOff,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Debt::select('debts.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('debts', $companyId)
            ->whereBranchId('debts', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $categoryId,
            $creditorId,
            $supplierId,
            $isPaidOff,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $categoryId,
                $creditorId,
                $supplierId,
                $isPaidOff,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('debts.code', 'like', '%'.$search.'%')
                            ->orWhere('debts.remarks', 'like', '%'.$search.'%')
                            ->orWhereHas('category', function ($categoryQuery) use ($search) {
                                $categoryQuery->where('debt_categories.code', 'like', '%'.$search.'%')
                                    ->orWhere('debt_categories.name', 'like', '%'.$search.'%');
                            })
                            ->orWhereHas('creditor', function ($creditorQuery) use ($search) {
                                $creditorQuery->where('debt_creditors.code', 'like', '%'.$search.'%')
                                    ->orWhere('debt_creditors.name', 'like', '%'.$search.'%');
                            })
                            ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                                $supplierQuery->where('suppliers.code', 'like', '%'.$search.'%')
                                    ->orWhere('suppliers.name', 'like', '%'.$search.'%');
                            });
                    });
                }

                if (! is_null($categoryId)) {
                    $query->where('debts.category_id', $categoryId);
                }

                if (! is_null($creditorId)) {
                    $query->where('debts.creditor_id', $creditorId);
                }

                if (! is_null($supplierId)) {
                    $query->where('debts.supplier_id', $supplierId);
                }

                if (! is_null($isPaidOff)) {
                    $query->where('debts.is_paid_off', $isPaidOff);
                }
            });

            if ($includeId) {
                $query->orWhere('debts.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(debts.id, '.$includeId.') desc');
        }
        $query->orderBy('debts.date', 'desc');
        $query->orderBy('debts.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $categoryId ?? '[null]',
                    $creditorId ?? '[null]',
                    $supplierId ?? '[null]',
                    is_null($isPaidOff) ? '[null]' : ($isPaidOff ? 'true' : 'false'),
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_debt_'.implode('_', $cacheParams);

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

    public function read(Debt $debt): Debt
    {
        return $debt->load(self::DETAIL_EAGER_LOADS);
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
                $count = Debt::whereCompanyId('debts', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'DEB'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = Debt::whereCompanyId('debts', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(DebtCreateDTO $data): Debt
    {
        $timer_start = microtime(true);

        try {
            $debt = new Debt();
            $debt->company_id = $data->companyId;
            $debt->branch_id = $data->branchId;
            $debt->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $debt->date = $this->generateDate($data->date);
            $debt->category_id = $data->categoryId;
            $debt->creditor_id = $data->creditorId;
            $debt->supplier_id = $data->supplierId;
            $debt->cash_account_id = $data->cashAccountId;
            $debt->direct_amount_received = $data->directAmountReceived;
            $debt->opening_amount_due = $data->openingAmountDue;
            $debt->due_days = $data->dueDays;
            $debt->remarks = $data->remarks;
            $debt->save();

            if ($debt->cash_account_id && $debt->direct_amount_received > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromDebt($debt)
                );
            }

            foreach ($data->payments as $payment) {
                $dto = new DebtPaymentCreateDTO(
                    companyId: $debt->company_id,
                    branchId: $debt->branch_id,
                    code: $payment['code'],
                    date: $payment['date'],
                    debtId: $debt->id,
                    cashAccountId: $payment['cash_account_id'],
                    amount: $payment['amount'],
                    remarks: $payment['remarks'],
                );
                $this->debtPaymentActions->create($dto, false);
            }

            $lines = [];

            if ((float) $debt->direct_amount_received > 0) {
                $cashAccountChartOfAccount = $debt->cashAccount?->chartOfAccount;
                if (! $cashAccountChartOfAccount) {
                    throw new InvalidArgumentException('Debt cash account chart of account must exist.');
                }

                $lines[] = new JournalEntryLineDTO(
                    chartOfAccountId: $cashAccountChartOfAccount->id,
                    debit: (float) $debt->direct_amount_received,
                    credit: 0,
                    remarks: $debt->remarks,
                );
            }

            if ((float) $debt->opening_amount_due > 0) {
                $openingCapitalChartOfAccount = $debt->company->equityCapitalOpeningCapitalChartOfAccount;
                if (! $openingCapitalChartOfAccount) {
                    throw new InvalidArgumentException('Debt opening capital chart of account must exist.');
                }

                $lines[] = new JournalEntryLineDTO(
                    chartOfAccountId: $openingCapitalChartOfAccount->id,
                    debit: (float) $debt->opening_amount_due,
                    credit: 0,
                    remarks: $debt->remarks,
                );
            }

            if ($debt->supplier_id) {
                $partyChartOfAccount = $debt->supplier?->chartOfAccount;
                if (! $partyChartOfAccount) {
                    $partyChartOfAccount = $debt->company->liabilityAccountPayableChartOfAccount;
                }
            } elseif ($debt->creditor_id) {
                $partyChartOfAccount = $debt->creditor?->chartOfAccount;
                if (! $partyChartOfAccount) {
                    $partyChartOfAccount = $debt->company->liabilityAccountPayableChartOfAccount;
                }
            } else {
                $partyChartOfAccount = $debt->company->liabilityAccountPayableChartOfAccount;
            }

            if (! $partyChartOfAccount) {
                throw new InvalidArgumentException('Debt payable chart of account must exist.');
            }

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $debt->company_id,
                branchId: $debt->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $debt->date,
                sourceType: Debt::class,
                sourceId: $debt->id,
                referenceNo: $debt->code,
                remarks: $debt->remarks,
                lines: [
                    ...$lines,
                    new JournalEntryLineDTO(
                        chartOfAccountId: $partyChartOfAccount->id,
                        debit: 0,
                        credit: (float) $debt->amount_total,
                        remarks: $debt->remarks,
                    ),
                ],
            );
            $this->journalEntryActions->create($journalEntryDTO);

            self::updateSummary($debt);

            $this->flushCache();

            return $debt;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Debt $debt, DebtUpdateDTO $data): Debt
    {
        $timer_start = microtime(true);

        try {
            $debt->code = $this->generateUniqueCode($debt->company_id, $data->code, $debt->id);
            $debt->date = $this->generateDate($data->date);
            $debt->category_id = $data->categoryId;
            $debt->creditor_id = $data->creditorId;
            $debt->supplier_id = $data->supplierId;
            $debt->cash_account_id = $data->cashAccountId;
            $debt->direct_amount_received = $data->directAmountReceived;
            $debt->opening_amount_due = $data->openingAmountDue;
            $debt->due_days = $data->dueDays;
            $debt->remarks = $data->remarks;
            $debt->save();

            $cashTransaction = $debt->cashTransaction;
            if ($debt->cash_account_id && $debt->direct_amount_received > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromDebt($debt)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromDebt($debt)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($data->deletePaymentIds as $deleteId) {
                $debtPayment = $debt->payments()->findOrFail($deleteId);
                $this->debtPaymentActions->delete($debtPayment, false);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $debtPayment = $debt->payments()->findOrFail($payment['id']);
                    $dto = new DebtPaymentUpdateDTO(
                        code: $payment['code'],
                        date: $payment['date'],
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->debtPaymentActions->update($debtPayment, $dto, false);
                } else {
                    $dto = new DebtPaymentCreateDTO(
                        companyId: $debt->company_id,
                        branchId: $debt->branch_id,
                        code: $payment['code'],
                        date: $payment['date'],
                        debtId: $debt->id,
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->debtPaymentActions->create($dto, false);
                }
            }

            $lines = [];

            if ((float) $debt->direct_amount_received > 0) {
                $cashAccountChartOfAccount = $debt->cashAccount?->chartOfAccount;
                if (! $cashAccountChartOfAccount) {
                    throw new InvalidArgumentException('Debt cash account chart of account must exist.');
                }

                $lines[] = new JournalEntryLineDTO(
                    chartOfAccountId: $cashAccountChartOfAccount->id,
                    debit: (float) $debt->direct_amount_received,
                    credit: 0,
                    remarks: $debt->remarks,
                );
            }

            if ((float) $debt->opening_amount_due > 0) {
                $openingCapitalChartOfAccount = $debt->company->equityCapitalOpeningCapitalChartOfAccount;
                if (! $openingCapitalChartOfAccount) {
                    throw new InvalidArgumentException('Debt opening capital chart of account must exist.');
                }

                $lines[] = new JournalEntryLineDTO(
                    chartOfAccountId: $openingCapitalChartOfAccount->id,
                    debit: (float) $debt->opening_amount_due,
                    credit: 0,
                    remarks: $debt->remarks,
                );
            }

            if ($debt->supplier_id) {
                $partyChartOfAccount = $debt->supplier?->chartOfAccount;
                if (! $partyChartOfAccount) {
                    $partyChartOfAccount = $debt->company->liabilityAccountPayableChartOfAccount;
                }
            } elseif ($debt->creditor_id) {
                $partyChartOfAccount = $debt->creditor?->chartOfAccount;
                if (! $partyChartOfAccount) {
                    $partyChartOfAccount = $debt->company->liabilityAccountPayableChartOfAccount;
                }
            } else {
                $partyChartOfAccount = $debt->company->liabilityAccountPayableChartOfAccount;
            }

            if (! $partyChartOfAccount) {
                throw new InvalidArgumentException('Debt payable chart of account must exist.');
            }

            $journalEntry = $debt->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $debt->company_id,
                    branchId: $debt->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $debt->date,
                    sourceType: Debt::class,
                    sourceId: $debt->id,
                    referenceNo: $debt->code,
                    remarks: $debt->remarks,
                    lines: [
                        ...$lines,
                        new JournalEntryLineDTO(
                            chartOfAccountId: $partyChartOfAccount->id,
                            debit: 0,
                            credit: (float) $debt->amount_total,
                            remarks: $debt->remarks,
                        ),
                    ],
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $debt->branch_id,
                    code: $journalEntry->code,
                    date: $debt->date,
                    referenceNo: $debt->code,
                    remarks: $debt->remarks,
                    lines: [
                        ...$lines,
                        new JournalEntryLineDTO(
                            chartOfAccountId: $partyChartOfAccount->id,
                            debit: 0,
                            credit: (float) $debt->amount_total,
                            remarks: $debt->remarks,
                        ),
                    ],
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            self::updateSummary($debt);

            $this->flushCache();

            return $debt;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(Debt $debt): void
    {
        $debt->refresh();

        $debt->amount_total = (float) $debt->direct_amount_received + (float) $debt->opening_amount_due;
        $debt->amount_paid_by_cash_account = (float) $debt->payments()->sum('amount');
        // Stock-adjustment settlement link is not implemented yet in this batch.
        $debt->amount_paid_by_stock_adjustment = 0;
        $debt->amount_due = max(
            0,
            (float) $debt->amount_total
                - (float) $debt->amount_paid_by_cash_account
                - (float) $debt->amount_paid_by_stock_adjustment
        );
        $debt->is_paid_off = $debt->amount_due == 0;
        $debt->save();
    }

    public function delete(Debt $debt): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $debt->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($debt->payments as $payment) {
                $this->debtPaymentActions->delete($payment, false);
            }

            $journalEntry = $debt->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $debt->delete();

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
