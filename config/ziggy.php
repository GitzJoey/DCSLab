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
            'api.post.dashboard.company.branches.*',
        ],
        'warehouse' => [
            'api.get.dashboard.warehouse.read',
            'api.post.dashboard.company.warehouses.*',
        ],
        'cash' => [
            'api.get.dashboard.cash.read',
            'api.post.dashboard.cashes.*',
        ],
        'supplier' => [
            'api.get.dashboard.supplier.read',
            'api.post.dashboard.suppliers.*',
        ],
        'productgroup' => [
            'api.get.dashboard.productgroup.read',
            'api.post.dashboard.productgroups.*',
        ],
        'productbrand' => [
            'api.get.dashboard.productbrands.read',
            'api.post.dashboard.productbrands.*',
        ],
        'unit' => [
            'api.get.dashboard.unit.read',
            'api.post.dashboard.units.*',
        ],
        'product' => [
            'api.get.dashboard.product.read',
            'api.get.dashboard.productgroup.read.all_active',
            'api.get.dashboard.productbrand.read.all_active',
            'api.get.dashboard.unit.read.all_active',
            'api.post.dashboard.products.*',
        ],
        'customergroup' => [
            'api.get.dashboard.customergroup.read',
            'api.get.dashboard.cash.read.all_active',
            'api.post.dashboard.customergroups.*',
        ],
        'customer' => [
            'api.get.dashboard.customer.read',
            'api.get.dashboard.customergroup.read.all_active',
            'api.post.dashboard.customers.*',
        ],
    ]
];
