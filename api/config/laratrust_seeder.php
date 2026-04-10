<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'developer' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'setting' => 'r,u',
        ],
        'administrator' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'setting' => 'r,u',

            'user' => 'c,r,ra,u',
        ],
        'user' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'setting' => 'r,u',
        ],
        /* #region Extensions */
        'POS-owner' => [
            'company' => 'c,r,ra,u,d',
            'branch' => 'c,r,ra,u,d',
            'warehouse' => 'c,r,ra,u,d',
            'investor' => 'c,r,ra,u,d',
            'cash_account' => 'c,r,ra,u,d',
            'product_category' => 'c,r,ra,u,d',
            'brand' => 'c,r,ra,u,d',
            'unit' => 'c,r,ra,u,d',
            'vat_profile' => 'c,r,ra,u,d',
            'product' => 'c,r,ra,u,d',
            'supplier' => 'c,r,ra,u,d',
            'customer_group' => 'c,r,ra,u,d',
            'customer_address' => 'c,r,ra,u,d',
            'customer' => 'c,r,ra,u,d',
            'stock_adjustment_category' => 'c,r,ra,u,d',

            'capital_opening' => 'c,r,ra,u,d',
            'capital_transaction' => 'c,r,ra,u,d',

            'stock_adjustment' => 'c,r,ra,u,d',
            'stock_adjustment_in_item' => 'c,r,ra,u,d',
            'stock_adjustment_in_item_serial' => 'c,r,ra,u,d',
            'stock_adjustment_out_item' => 'c,r,ra,u,d',
            'stock_adjustment_out_item_serial' => 'c,r,ra,u,d',

            'stock_transfer' => 'c,r,ra,u,d',
            'stock_transfer_item' => 'c,r,ra,u,d',
            'stock_transfer_item_serial' => 'c,r,ra,u,d',
        ],
        /* #endregion */
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'ra' => 'readAny',
        'u' => 'update',
        'd' => 'delete',

        'rs' => 'restore',

        'ac' => 'authorizeCreate',
        'au' => 'authorizeUpdate',
        'ad' => 'authorizeDelete',

        'ars' => 'authorizeRestore',
    ],
];
