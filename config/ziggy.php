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
            'api.post.dashboard.capital.*',
            
            'api.get.dashboard.company.read.all_active',
            'api.get.dashboard.investor.read.all_active',
            'api.get.dashboard.capitalgroup.read.all_active',
            'api.get.dashboard.cash.read.all_active',
        ],
        'capitalgroup' => [
            'api.get.dashboard.capitalgroup.read',
            'api.post.dashboard.capitalgroup.*',

            'api.get.dashboard.company.read.all_active',
        ],
        'investor' => [
            'api.get.dashboard.investor.read',
            'api.post.dashboard.investor.*',

            'api.get.dashboard.company.read.all_active',
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
        'productunit' => [
            'api.get.dashboard.productunit.read',
            'api.post.dashboard.productunit.*',
        ],
        'unit' => [
            'api.get.dashboard.unit.read',
            'api.post.dashboard.unit.*',
        ],
        'product' => [
            'api.get.dashboard.product.read',
            'api.get.dashboard.product.read.product',
            'api.get.dashboard.product.read.service',
            'api.get.dashboard.productgroup.read.product',
            'api.get.dashboard.productbrand.read.all',
            'api.get.dashboard.unit.read.product',
            'api.get.dashboard.productunit.read.all',
            'api.get.dashboard.supplier.read.all',
            'api.post.dashboard.product.*',
        ],
        'service' => [
            'api.get.dashboard.product.read.service',
            'api.get.dashboard.productgroup.read.service',
            'api.get.dashboard.unit.read.service',
            'api.post.dashboard.service.*',
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
