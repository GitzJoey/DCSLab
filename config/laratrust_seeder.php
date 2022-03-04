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
        'dev' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'r,u',
            'users' => 'c,r,u',
            'devtools' => 'c,r,u,d',
        ],
        'administrator' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'r,u',
            'users' => 'c,r,u'
        ],
        'user' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'r,u',
        ],
        #region Extensions
        'POS-owner' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'r,u',

            'supplier' => 'c,r,u,d',
            'product' => 'c,r,u,d',
        ],
        'POS-supplier' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'r,u',
        ],
        #endregion
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete'
    ]
];
