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
        #region Extensions
        'POS-owner' => [
            'company' => 'c,r,ra,u,d',
            'branch' => 'c,r,ra,u,d',
            'employee' => 'c,r,ra,u,d',
            'warehouse' => 'c,r,ra,u,d',
            'productgroup' => 'c,r,ra,u,d',
            'brand' => 'c,r,ra,u,d',
            'unit' => 'c,r,ra,u,d',
            'product' => 'c,r,ra,u,d',
            'service' => 'c,r,ra,u,d',
            'supplier' => 'c,r,ra,u,d',
            'purchaseorder' => 'c,r,ra,u,d',
            'salesorder' => 'c,r,ra,u,d',
        ],
        'POS-employee' => [
            'company' => 'c',
            'branch' => 'c',
            'employee' => 'c,u',
            'warehouse' => 'c,u',
            'productgroup' => 'c,u',
            'brand' => 'c,u',
            'unit' => 'c,u',
            'product' => 'c,u',
            'service' => 'c,u',
            'supplier' => 'c,u',
            'purchaseorder' => 'c,u',
            'salesorder' => 'c,u',
        ],
        'POS-supplier' => [
            'supplier' => 'r,u',
        ],
        #endregion
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
    ]
];
