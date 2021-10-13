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
        'company' => [
            'api.get.dashboard.company.read',
            'api.post.dashboard.company.companies.*',
        ],
        'branch' => [
            'api.get.dashboard.branch.*',
            'api.post.dashboard.company.branches.*',

            'api.get.dashboard.company.read.all_active',
        ],
        'employee' => [
            'api.get.dashboard.employee.read',
            'api.post.dashboard.company.employees.*',

            'api.get.dashboard.company.read.all_active',
            'api.get.common.countries.read',
        ],
        'warehouse' => [
            'api.get.dashboard.warehouse.read',
            'api.post.dashboard.company.warehouses.*',

            'api.get.dashboard.company.read.all_active',
        ],
        'cash' => [
            'api.get.dashboard.cash.read',
            'api.post.dashboard.cash.*',
        ],
        'capital' => [
            'api.get.dashboard.capital.read',
            'api.get.dashboard.investor.read.all_active',
            'api.get.dashboard.capitalgroup.read.all_active',
            'api.get.dashboard.cash.read.all_active',
            'api.post.dashboard.capital.*',
        ],
        'capitalgroup' => [
            'api.get.dashboard.capitalgroup.read',
            'api.post.dashboard.capitalgroup.*',
        ],
        'investor' => [
            'api.get.dashboard.investor.read',
            'api.post.dashboard.investor.*',
        ],
        'expensegroup' => [
            'api.get.dashboard.expensegroup.read',
            'api.post.dashboard.expensegroup.*',

            'api.get.dashboard.company.read.all_active',
        ],
        'incomegroup' => [
            'api.get.dashboard.incomegroup.read',
            'api.post.dashboard.incomegroup.*',

            'api.get.dashboard.company.read.all_active',
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
