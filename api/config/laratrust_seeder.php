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
            //Bypass all permissions
        ],
        'administrator' => [
            'user' => 'c,r,ra,u,ac,au',
            'profile' => 'r,u',
            'company' => 'c,r,ra,u,d,rs,ac,au,ad,ars',
            'branch' => 'c,r,ra,u,d,rs,ac,au,ad,ars',
        ],
        'user' => [
            'profile' => 'r,u',
        ],

        /* #region Extensions */
        'POS-owner' => [
            'company' => 'c,r,ra,u,d,rs,ac,au,ad,ars',
            'branch' => 'c,r,ra,u,d,rs,ac,au,ad,ars',
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
