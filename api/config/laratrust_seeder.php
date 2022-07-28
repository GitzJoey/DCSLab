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
            'settings' => 'r,u',
        ],
        'administrator' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'r,u',

            'users' => 'c,r,ra,u',
        ],
        'user' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'r,u',
        ],
        #region Extensions
        'POS-owner' => [
            'company' => 'c,r,ra,u,d',
            'branch' => 'c,r,ra,u,d',
            'employee' => 'c,r,ra,u,d',
            'warehouse' => 'c,r,ra,u,d',
            'supplier' => 'c,r,ra,u,d',
            'product' => 'c,r,ra,u,d',
            'brand' => 'c,r,ra,u,d',
            'productgroup' => 'c,r,ra,u,d',
            'service' => 'c,r,ra,u,d',
            'unit' => 'c,r,ra,u,d',
            'purchaseorder' => 'c,r,ra,u,d',
            'salesorder' => 'c,r,ra,u,d',
        ],
        'POS-employee' => [
            'companies' => 'c',
            'branches' => 'c',
            'employees' => 'c,u',
            'warehouses' => 'c,u',
            'suppliers' => 'c,u',
            'products' => 'c,u',
            'brands' => 'c,u',
            'productgroups' => 'c,u',
            'services' => 'c,u',
            'units' => 'c,u',
            'purchaseorders' => 'c,u',
            'salesorders' => 'c,u',
        ],
        'POS-supplier' => [
            'suppliers' => 'r,u',
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
