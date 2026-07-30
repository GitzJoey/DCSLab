/**
 * Mirrors the backend PaymentTypeEnum (cash | down_payment | return).
 * Shared by the purchase-invoice and sales-invoice payment types.
 */
export type PaymentType = 'cash' | 'down_payment' | 'return';
