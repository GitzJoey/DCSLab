<?php

return [
    'unique_code' => 'The Code has already been taken',
    'unique_name' => 'The Name has already been taken',
    'unique_address' => 'The Address has already been taken',
    'unique_slug' => 'The Slug has already been taken',
    'valid_dropdown' => 'Invalid value',
    'valid_company' => 'Invalid company value',
    'valid_branch' => 'Invalid branch value',
    'valid_warehouse' => 'Invalid warehouse value',
    'valid_customer_group' => 'Invalid customer group value',
    'valid_customer' => 'Invalid customer value',
    'too_many_tokens' => 'Too many token request',
    'must_reset_password' => 'Require to reset the password',
    'inactive_user' => 'Your profile status is inactive',
    'company' => [
        'deactivate_default_company' => 'Default company cannot be deactivated',
        'delete_default_company' => 'Default company cannot be deleted',
        'set_company_to_non_default' => 'Set company to non default is not permitted',
    ],
    'branch' => [
        'delete_main_branch' => 'Main branch cannot be deleted',
        'set_branch_to_non_main' => 'Set branch to non main is not permitted',
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
