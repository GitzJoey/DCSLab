<?php

return [
    'unique_code' => 'The Code has already been taken',
    'unique_name' => 'The Name has already been taken',
    'unique_address' => 'The Address has already been taken',
    'valid_dropdown' => 'Invalid value',
    'valid_company' => 'Invalid company value',
    'valid_branch' => 'Invalid branch value',
    'valid_warehouse' => 'Invalid warehouse value',
    'valid_stock_adjustment_in_item' => 'Invalid stock adjustment in item value',
    'valid_stock_adjustment_out_item' => 'Invalid stock adjustment out item value',
    'valid_customer_group' => 'Invalid customer group value',
    'valid_customer' => 'Invalid customer value',
    'valid_supplier' => 'Invalid supplier value',
    'valid_cash_account' => 'Invalid cash account value',
    'too_many_tokens' => 'Too many token request',
    'must_reset_password' => 'Require to reset the password',
    'inactive_user' => 'Your profile status is inactive',
    'purchase_order' => [
        'exceed_available_down_payment' => 'Total down payment refund exceeds the available down payment',
    ],
    'company' => [
        'deactivate_default_company' => 'Default company cannot be deactivated',
        'delete_default_company' => 'Default company cannot be deleted',
        'set_company_to_non_default' => 'Set company to non default is not permitted',
    ],
    'branch' => [
        'delete_main_branch' => 'Main branch cannot be deleted',
        'set_branch_to_non_main' => 'Set branch to non main is not permitted',
    ],
    'expense_category' => [
        'parent_must_be_active' => 'Parent expense category must be active.',
        'cannot_delete_with_children' => 'Expense category cannot be deleted because it still has child categories.',
        'must_not_have_children' => 'Expense category must be a category without child categories.',
    ],
    'income_category' => [
        'parent_must_be_active' => 'Parent income category must be active.',
        'cannot_delete_with_children' => 'Income category cannot be deleted because it still has child categories.',
        'must_not_have_children' => 'Income category must be a category without child categories.',
    ],
    'expense' => [
        'amount_total_must_be_positive' => 'Either immediate payment or payable amount must be greater than zero.',
        'payments_exceed_amount_payable' => 'Total payable payments must not exceed the payable amount.',
        'invalid_payment_reference' => 'Expense payment data is invalid for this expense.',
    ],
    'income' => [
        'amount_total_must_be_positive' => 'Either immediate receipt or receivable amount must be greater than zero.',
        'payments_exceed_amount_receivable' => 'Total receivable payments must not exceed the receivable amount.',
        'invalid_payment_reference' => 'Income payment data is invalid for this income.',
    ],
    'prepaid_expense' => [
        'amount_total_must_be_positive' => 'Either immediate payment or payable amount must be greater than zero.',
        'payments_exceed_amount_payable' => 'Total payable payments must not exceed the payable amount.',
        'invalid_payment_reference' => 'Prepaid expense payment data is invalid for this prepaid expense.',
    ],
    'liability' => [
        'amount_total_must_be_positive' => 'Either received amount or existing payable amount must be greater than zero.',
        'payments_exceed_amount_payable' => 'Total liability payments must not exceed the payable amount.',
        'invalid_payment_reference' => 'Liability payment data is invalid for this liability.',
        'party_must_be_single' => 'Choose only one party, either creditor or supplier.',
        'party_is_required' => 'A creditor or supplier must be selected.',
        'cash_account_is_required_for_amount_received' => 'Cash account is required when received amount is filled.',
        'amount_received_is_required_for_cash_account' => 'Received amount must be greater than zero when cash account is selected.',
    ],
    'prepaid_income' => [
        'amount_total_must_be_positive' => 'Either immediate receipt or receivable amount must be greater than zero.',
        'payments_exceed_amount_receivable' => 'Total receivable payments must not exceed the receivable amount.',
        'invalid_payment_reference' => 'Prepaid income payment data is invalid for this prepaid income.',
    ],
    'stock_adjustment' => [
        'invalid_in_item_reference' => 'Incoming item data is invalid for this stock adjustment.',
        'invalid_out_item_reference' => 'Outgoing item data is invalid for this stock adjustment.',
        'invalid_in_item_serial_reference' => 'Incoming item serial data is invalid for this stock adjustment.',
        'invalid_out_item_serial_reference' => 'Outgoing item serial data is invalid for this stock adjustment.',
    ],
    'product' => [
        'unit' => [
            'duplicate_conversion' => 'Within one product, conversion values must be unique.',
            'duplicate_unit' => 'Within one product, units must be unique.',
            'single_base' => 'Within one product, there must be exactly one base unit.',
            'single_primary' => 'Within one product, there must be exactly one primary unit.',
            'duplicate_code' => 'Within one product, product unit code (SKU) must be unique.',
            'base_conversion_must_be_one' => 'Conversion value for the base unit must be 1.',
            'non_base_conversion_must_gt_one' => 'Conversion value for non-base units must be greater than 1.',
            'base_price_inconsistent' => 'Base unit price is inconsistent across units.',
            'cannot_delete_base_unit' => 'Base unit cannot be deleted.',
        ],
        'vat' => [
            'must_be_zero_if_not_taxable' => 'VAT rate must be 0 when product is not taxable.',
            'out_of_range' => 'VAT rate must be between 0 and 100.',
        ],
    ],
];
