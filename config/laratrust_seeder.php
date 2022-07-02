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
            'companies' => 'c,r,ra,u,d',
            'branches' => 'c,r,ra,u,d',
            'employees' => 'c,r,ra,u,d',
            'warehouses' => 'c,r,ra,u,d',
            'suppliers' => 'c,r,ra,u,d',
            'products' => 'c,r,ra,u,d',
            'brands' => 'c,r,ra,u,d',
            'productgroups' => 'c,r,ra,u,d',
            'services' => 'c,r,ra,u,d',
            'units' => 'c,r,ra,u,d',
            'purchaseorders' => 'c,r,ra,u,d',
            'salesorders' => 'c,r,ra,u,d',
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
    ],
];
