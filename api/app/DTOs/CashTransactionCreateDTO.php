<?php

namespace App\DTOs;

use App\Enums\CapitalTransactionTypeEnum;
use App\Models\CapitalOpening;
use App\Models\CapitalTransaction;
use App\Models\CashTransfer;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Income;
use App\Models\IncomePayment;
use App\Models\PrepaidExpense;
use App\Models\PrepaidExpensePayment;
use App\Models\PrepaidIncome;
use App\Models\PrepaidIncomePayment;
use App\Models\PurchaseAdditionalCost;
use App\Models\PurchaseAdditionalCostPayment;
use App\Models\PurchaseOrderDownPayment;
use App\Models\PurchaseOrderDownPaymentRefund;
use App\Models\Receivable;
use App\Models\ReceivablePayment;

final class CashTransactionCreateDTO
{
    public function __construct(
        public readonly string $referableType,
        public readonly int $referableId,
        public readonly string $date,
        public readonly int $cashAccountId,
        public readonly float $amount,
    ) {
    }

    public static function fromCapitalOpening(CapitalOpening $capitalOpening): self
    {
        return new self(
            referableType: CapitalOpening::class,
            referableId: $capitalOpening->id,
            date: $capitalOpening->date,
            cashAccountId: $capitalOpening->cash_account_id,
            amount: (float) $capitalOpening->amount,
        );
    }

    public static function fromCapitalTransaction(CapitalTransaction $capitalTransaction): self
    {
        $type = $capitalTransaction->type instanceof CapitalTransactionTypeEnum ? $capitalTransaction->type : CapitalTransactionTypeEnum::resolveToEnum($capitalTransaction->type);
        $amount = $type === CapitalTransactionTypeEnum::IN ? (float) $capitalTransaction->amount : ((float) $capitalTransaction->amount * -1);

        return new self(
            referableType: CapitalTransaction::class,
            referableId: $capitalTransaction->id,
            date: $capitalTransaction->date,
            cashAccountId: $capitalTransaction->cash_account_id,
            amount: $amount,
        );
    }

    public static function fromExpense(Expense $expense): self
    {
        return new self(
            referableType: Expense::class,
            referableId: $expense->id,
            date: $expense->date,
            cashAccountId: $expense->paid_immediately_cash_account_id,
            amount: ((float) $expense->amount_paid_immediately * -1),
        );
    }

    public static function fromExpensePayment(ExpensePayment $expensePayment): self
    {
        return new self(
            referableType: ExpensePayment::class,
            referableId: $expensePayment->id,
            date: $expensePayment->date,
            cashAccountId: $expensePayment->cash_account_id,
            amount: ((float) $expensePayment->amount * -1),
        );
    }

    public static function fromPrepaidExpense(PrepaidExpense $prepaidExpense): self
    {
        return new self(
            referableType: PrepaidExpense::class,
            referableId: $prepaidExpense->id,
            date: $prepaidExpense->date,
            cashAccountId: $prepaidExpense->paid_immediately_cash_account_id,
            amount: ((float) $prepaidExpense->amount_paid_immediately * -1),
        );
    }

    public static function fromPrepaidExpensePayment(PrepaidExpensePayment $prepaidExpensePayment): self
    {
        return new self(
            referableType: PrepaidExpensePayment::class,
            referableId: $prepaidExpensePayment->id,
            date: $prepaidExpensePayment->date,
            cashAccountId: $prepaidExpensePayment->cash_account_id,
            amount: ((float) $prepaidExpensePayment->amount * -1),
        );
    }

    public static function fromIncome(Income $income): self
    {
        return new self(
            referableType: Income::class,
            referableId: $income->id,
            date: $income->date,
            cashAccountId: $income->paid_immediately_cash_account_id,
            amount: (float) $income->amount_paid_immediately,
        );
    }

    public static function fromIncomePayment(IncomePayment $incomePayment): self
    {
        return new self(
            referableType: IncomePayment::class,
            referableId: $incomePayment->id,
            date: $incomePayment->date,
            cashAccountId: $incomePayment->cash_account_id,
            amount: (float) $incomePayment->amount,
        );
    }

    public static function fromPrepaidIncome(PrepaidIncome $prepaidIncome): self
    {
        return new self(
            referableType: PrepaidIncome::class,
            referableId: $prepaidIncome->id,
            date: $prepaidIncome->date,
            cashAccountId: $prepaidIncome->paid_immediately_cash_account_id,
            amount: (float) $prepaidIncome->amount_paid_immediately,
        );
    }

    public static function fromPrepaidIncomePayment(PrepaidIncomePayment $prepaidIncomePayment): self
    {
        return new self(
            referableType: PrepaidIncomePayment::class,
            referableId: $prepaidIncomePayment->id,
            date: $prepaidIncomePayment->date,
            cashAccountId: $prepaidIncomePayment->cash_account_id,
            amount: (float) $prepaidIncomePayment->amount,
        );
    }

    public static function fromCashTransferSource(CashTransfer $cashTransfer): self
    {
        return new self(
            referableType: CashTransfer::class,
            referableId: $cashTransfer->id,
            date: $cashTransfer->date,
            cashAccountId: $cashTransfer->source_cash_account_id,
            amount: ((float) $cashTransfer->amount * -1),
        );
    }

    public static function fromCashTransferDestination(CashTransfer $cashTransfer): self
    {
        return new self(
            referableType: CashTransfer::class,
            referableId: $cashTransfer->id,
            date: $cashTransfer->date,
            cashAccountId: $cashTransfer->destination_cash_account_id,
            amount: (float) $cashTransfer->amount,
        );
    }

    public static function fromDebt(Debt $debt): self
    {
        return new self(
            referableType: Debt::class,
            referableId: $debt->id,
            date: $debt->date,
            cashAccountId: $debt->cash_account_id,
            amount: (float) $debt->direct_amount_received,
        );
    }

    public static function fromDebtPayment(DebtPayment $debtPayment): self
    {
        return new self(
            referableType: DebtPayment::class,
            referableId: $debtPayment->id,
            date: $debtPayment->date,
            cashAccountId: $debtPayment->cash_account_id,
            amount: ((float) $debtPayment->amount * -1),
        );
    }

    public static function fromReceivable(Receivable $receivable): self
    {
        return new self(
            referableType: Receivable::class,
            referableId: $receivable->id,
            date: $receivable->date,
            cashAccountId: $receivable->cash_account_id,
            amount: (float) $receivable->direct_amount_received,
        );
    }

    public static function fromReceivablePayment(ReceivablePayment $receivablePayment): self
    {
        return new self(
            referableType: ReceivablePayment::class,
            referableId: $receivablePayment->id,
            date: $receivablePayment->date,
            cashAccountId: $receivablePayment->cash_account_id,
            amount: (float) $receivablePayment->amount,
        );
    }

    public static function fromPurchaseOrderDownPayment(PurchaseOrderDownPayment $purchaseOrderDownPayment): self
    {
        return new self(
            referableType: PurchaseOrderDownPayment::class,
            referableId: $purchaseOrderDownPayment->id,
            date: $purchaseOrderDownPayment->date,
            cashAccountId: $purchaseOrderDownPayment->cash_account_id,
            amount: (float) $purchaseOrderDownPayment->amount,
        );
    }

    public static function fromPurchaseOrderDownPaymentRefund(PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund): self
    {
        return new self(
            referableType: PurchaseOrderDownPaymentRefund::class,
            referableId: $purchaseOrderDownPaymentRefund->id,
            date: $purchaseOrderDownPaymentRefund->date,
            cashAccountId: $purchaseOrderDownPaymentRefund->cash_account_id,
            amount: (float) $purchaseOrderDownPaymentRefund->amount,
        );
    }

    public static function fromPurchaseAdditionalCost(PurchaseAdditionalCost $purchaseAdditionalCost): self
    {
        return new self(
            referableType: PurchaseAdditionalCost::class,
            referableId: $purchaseAdditionalCost->id,
            date: $purchaseAdditionalCost->date,
            cashAccountId: $purchaseAdditionalCost->paid_immediately_cash_account_id,
            amount: (float) $purchaseAdditionalCost->amount_paid_immediately,
        );
    }

    public static function fromPurchaseAdditionalCostPayment(PurchaseAdditionalCostPayment $purchaseAdditionalCostPayment): self
    {
        return new self(
            referableType: PurchaseAdditionalCostPayment::class,
            referableId: $purchaseAdditionalCostPayment->id,
            date: $purchaseAdditionalCostPayment->date,
            cashAccountId: $purchaseAdditionalCostPayment->cash_account_id,
            amount: (float) $purchaseAdditionalCostPayment->amount,
        );
    }
}
