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
            'profile' => 'c,r,u,d',
            'messaging' => 'c,r,u,d',
            'settings' => 'c,r,u,d',
        ],
        'administrator' => [
            'profile' => 'c,r,u,d',
            'messaging' => 'c,r,u,d',
            'settings' => 'c,r,u,d',
        ],
        'user' => [
            'profile' => 'r,u',
            'messaging' => 'c,r,u,d',
            'settings' => 'c,r,u,d',
        ]
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete'
    ]
];
