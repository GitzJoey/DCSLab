<?php

return [
    'skip-route-function' => true,
    'groups' => [
        'all' => ['api.*'],
        'all_get' => ['api.get.*'],
        'all_post' => ['api.post.*'],
    ],
];