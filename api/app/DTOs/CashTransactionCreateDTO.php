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
use App\Models\PurchaseInvoicePayment;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseOrderPaymentRefund;
use App\Models\PurchaseOrderReceiptCost;
use App\Models\PurchaseReturnRefund;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\SalesInvoicePayment;
use App\Models\SalesOrderDeliveryCost;
use App\Models\SalesOrderPayment;
use App\Models\SalesOrderPaymentRefund;
use App\Models\SalesReturnRefund;

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

    public static function fromPurchaseOrderPayment(PurchaseOrderPayment $purchaseOrderPayment): self
    {
        return new self(
            referableType: PurchaseOrderPayment::class,
            referableId: $purchaseOrderPayment->id,
            date: $purchaseOrderPayment->date,
            cashAccountId: $purchaseOrderPayment->cash_account_id,
            amount: ((float) $purchaseOrderPayment->amount * -1),
        );
    }

    public static function fromPurchaseOrderPaymentRefund(PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund): self
    {
        return new self(
            referableType: PurchaseOrderPaymentRefund::class,
            referableId: $purchaseOrderPaymentRefund->id,
            date: $purchaseOrderPaymentRefund->date,
            cashAccountId: $purchaseOrderPaymentRefund->cash_account_id,
            amount: (float) $purchaseOrderPaymentRefund->amount,
        );
    }

    public static function fromPurchaseInvoicePayment(PurchaseInvoicePayment $purchaseInvoicePayment): self
    {
        return new self(
            referableType: PurchaseInvoicePayment::class,
            referableId: $purchaseInvoicePayment->id,
            date: $purchaseInvoicePayment->date,
            cashAccountId: $purchaseInvoicePayment->cash_account_id,
            amount: ((float) $purchaseInvoicePayment->amount * -1),
        );
    }

    public static function fromPurchaseOrderReceiptCost(PurchaseOrderReceiptCost $purchaseOrderReceiptCost): self
    {
        return new self(
            referableType: PurchaseOrderReceiptCost::class,
            referableId: $purchaseOrderReceiptCost->id,
            date: $purchaseOrderReceiptCost->date,
            cashAccountId: $purchaseOrderReceiptCost->cash_account_id,
            amount: ((float) $purchaseOrderReceiptCost->amount * -1),
        );
    }

    public static function fromPurchaseReturnRefund(PurchaseReturnRefund $purchaseReturnRefund): self
    {
        return new self(
            referableType: PurchaseReturnRefund::class,
            referableId: $purchaseReturnRefund->id,
            date: $purchaseReturnRefund->date,
            cashAccountId: $purchaseReturnRefund->cash_account_id,
            amount: (float) $purchaseReturnRefund->amount,
        );
    }

    public static function fromSalesOrderPayment(SalesOrderPayment $salesOrderPayment): self
    {
        return new self(
            referableType: SalesOrderPayment::class,
            referableId: $salesOrderPayment->id,
            date: $salesOrderPayment->date,
            cashAccountId: $salesOrderPayment->cash_account_id,
            amount: (float) $salesOrderPayment->amount,
        );
    }

    public static function fromSalesOrderPaymentRefund(SalesOrderPaymentRefund $salesOrderPaymentRefund): self
    {
        return new self(
            referableType: SalesOrderPaymentRefund::class,
            referableId: $salesOrderPaymentRefund->id,
            date: $salesOrderPaymentRefund->date,
            cashAccountId: $salesOrderPaymentRefund->cash_account_id,
            amount: ((float) $salesOrderPaymentRefund->amount * -1),
        );
    }

    public static function fromSalesInvoicePayment(SalesInvoicePayment $salesInvoicePayment): self
    {
        return new self(
            referableType: SalesInvoicePayment::class,
            referableId: $salesInvoicePayment->id,
            date: $salesInvoicePayment->date,
            cashAccountId: $salesInvoicePayment->cash_account_id,
            amount: (float) $salesInvoicePayment->amount,
        );
    }

    public static function fromSalesOrderDeliveryCost(SalesOrderDeliveryCost $salesOrderDeliveryCost): self
    {
        return new self(
            referableType: SalesOrderDeliveryCost::class,
            referableId: $salesOrderDeliveryCost->id,
            date: $salesOrderDeliveryCost->date,
            cashAccountId: $salesOrderDeliveryCost->cash_account_id,
            amount: ((float) $salesOrderDeliveryCost->amount * -1),
        );
    }

    public static function fromSalesReturnRefund(SalesReturnRefund $salesReturnRefund): self
    {
        return new self(
            referableType: SalesReturnRefund::class,
            referableId: $salesReturnRefund->id,
            date: $salesReturnRefund->date,
            cashAccountId: $salesReturnRefund->cash_account_id,
            amount: ((float) $salesReturnRefund->amount * -1),
        );
    }
}
