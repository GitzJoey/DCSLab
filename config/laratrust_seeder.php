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
            'users' => 'c,r,ra,u'
        ],
        'administrator' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'r,u',
            'users' => 'c,r,ra,u'
        ],
        'user' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'r,u',
        ],
        #region Extensions
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

        'ars' => 'authorizeRestore'
    ]
];
