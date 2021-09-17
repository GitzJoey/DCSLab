<?php
return [
    'groups' => [
        'user' => [
            'api.get.admin.user.*',
            'api.post.admin.user.*',

            'api.get.common.countries.read',
        ],
        'role' => [
            'api.get.admin.role.*',
            'api.post.admin.role.*',
        ],
        'branch' => [
            'api.get.dashboard.branch.read',
            'api.post.dashboard.company.branch.*',
        ],
        'employee' => [
            'api.get.dashboard.employee.read',
            'api.post.dashboard.company.employee.*',
        ],
        'warehouse' => [
            'api.get.dashboard.warehouse.read',
            'api.post.dashboard.company.warehouse.*',
        ],
        'cash' => [
            'api.get.dashboard.cash.read',
            'api.post.dashboard.cash.*',
        ],
        'supplier' => [
            'api.get.dashboard.supplier.read',
            'api.post.dashboard.supplier.*',
        ],
        'productgroup' => [
            'api.get.dashboard.productgroup.read',
            'api.post.dashboard.productgroup.*',
        ],
        'productbrand' => [
            'api.get.dashboard.productbrand.read',
            'api.post.dashboard.productbrand.*',
        ],
        'unit' => [
            'api.get.dashboard.unit.read',
            'api.post.dashboard.unit.*',
        ],
        'product' => [
            'api.get.dashboard.product.read',
            'api.get.dashboard.productgroup.read.all_active',
            'api.get.dashboard.productbrand.read.all_active',
            'api.get.dashboard.unit.read.all_active',
            'api.post.dashboard.product.*',
        ],
        'customergroup' => [
            'api.get.dashboard.customergroup.read',
            'api.get.dashboard.cash.read.all_active',
            'api.post.dashboard.customergroup.*',
        ],
        'customer' => [
            'api.get.dashboard.customer.read',
            'api.get.dashboard.customergroup.read.all_active',
            'api.post.dashboard.customer.*',
        ],
    ]
];
