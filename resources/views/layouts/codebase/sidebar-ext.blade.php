@role('dev|administrator|pos-owner')
    <li class="{{ active_class(if_route_pattern('db.company.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-briefcase"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.company')}}</span></a>
        <ul>
            <li>
                <a class="{{ active_class(if_route_pattern('db.company.companies')) }}" href="{{route('db.company.companies')}}">{{ __('sidebar-ext.db.company.companies') }}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.company.branches')) }}" href="{{route('db.company.branches')}}">{{ __('sidebar-ext.db.company.branches') }}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.company.warehouses')) }}" href="{{route('db.company.warehouses')}}">{{ __('sidebar-ext.db.company.warehouses') }}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.company.employees')) }}" href="{{route('db.company.employees')}}">{{ __('sidebar-ext.db.company.employees') }}</a>
            </li>
        </ul>
    </li>
@endrole

@role('dev|administrator|pos-owner')
    <li class="{{ active_class(if_route_pattern('db.product.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-drawer"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.product')}}</span></a>
        <ul>
            <li>
                <a class="{{ active_class(if_route_pattern('db.product.product_groups')) }}" href="{{route('db.product.product_groups')}}">{{ __('sidebar-ext.db.product.product_groups')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.product.brands')) }}" href="{{route('db.product.brands')}}">{{ __('sidebar-ext.db.product.brands')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.product.units')) }}" href="{{route('db.product.units')}}">{{ __('sidebar-ext.db.product.units')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.product.products')) }}" href="{{route('db.product.products')}}">{{ __('sidebar-ext.db.product.products')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.product.services')) }}" href="{{route('db.product.services')}}">{{ __('sidebar-ext.db.product.services')}}</a>
            </li>
        </ul>
    </li>
@endrole

@role('dev|administrator|pos-owner')
    <li class="{{ active_class(if_route_pattern('db.finance.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-wallet"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.finance')}}</span></a>
        <ul>
            <li>
                <a class="{{ active_class(if_route_pattern('db.finance.chart_of_accounts')) }}" href="{{route('db.finance.chart_of_accounts')}}">{{ __('sidebar-ext.db.finance.chart_of_accounts')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.finance.cashes')) }}" href="{{route('db.finance.cashes')}}">{{ __('sidebar-ext.db.finance.cashes')}}</a>
            </li>
            <li class="{{ active_class(if_route_pattern('db.finance.capital.*'), 'open') }}">
                <a class="nav-submenu" data-toggle="nav-submenu" href="#"><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.finance.capital')}}</span></a>
                <ul>
                    <li>
                        <a class="{{ active_class(if_route_pattern('db.finance.capital.investors')) }}" href="{{route('db.finance.capital.investors')}}">{{ __('sidebar-ext.db.finance.capital.investors')}}</a>
                    </li>
                    <li>
                        <a class="{{ active_class(if_route_pattern('db.finance.capital.capital_groups')) }}" href="{{route('db.finance.capital.capital_groups')}}">{{ __('sidebar-ext.db.finance.capital.capital_groups')}}</a>
                    </li>
                    <li>
                        <a class="{{ active_class(if_route_pattern('db.finance.capital.capitals')) }}" href="{{route('db.finance.capital.capitals')}}">{{ __('sidebar-ext.db.finance.capital.capitals')}}</a>
                    </li>
                </ul>
            </li>
            <li class="{{ active_class(if_route_pattern('db.finance.expense.*'), 'open') }}">
                <a class="nav-submenu" data-toggle="nav-submenu" href="#"><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.finance.expense')}}</span></a>
                <ul>
                    <li>
                        <a class="{{ active_class(if_route_pattern('db.finance.expense.expense_groups')) }}" href="{{route('db.finance.expense.expense_groups')}}">{{ __('sidebar-ext.db.finance.expense.expense_groups')}}</a>
                    </li>
                    <li>
                        <a class="{{ active_class(if_route_pattern('db.finance.expense.expenses')) }}" href="{{route('db.finance.expense.expenses')}}">{{ __('sidebar-ext.db.finance.expense.expenses')}}</a>
                    </li>
                </ul>
            </li>
            <li class="{{ active_class(if_route_pattern('db.finance.income.*'), 'open') }}">
                <a class="nav-submenu" data-toggle="nav-submenu" href="#"><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.finance.income')}}</span></a>
                <ul>
                    <li>
                        <a class="{{ active_class(if_route_pattern('db.finance.income.income_groups')) }}" href="{{route('db.finance.income.income_groups')}}">{{ __('sidebar-ext.db.finance.income.income_groups')}}</a>
                    </li>
                    <li>
                        <a class="{{ active_class(if_route_pattern('db.finance.income.incomes')) }}" href="{{route('db.finance.income.incomes')}}">{{ __('sidebar-ext.db.finance.income.incomes')}}</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
@endrole

@role('dev|administrator|pos-owner')
    <li class="{{ active_class(if_route_pattern('db.purchase.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-note"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.purchase')}}</span></a>
        <ul>
            <li>
                <a class="{{ active_class(if_route_pattern('db.purchase.suppliers')) }}" href="{{route('db.purchase.suppliers')}}">{{ __('sidebar-ext.db.purchase.suppliers')}}</a>
            </li>
        </ul>
    </li>
@endrole

@role('dev|administrator|pos-owner')
    <li class="{{ active_class(if_route_pattern('db.sales.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-handbag"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.sales')}}</span></a>
        <ul>
            <li>
                <a class="{{ active_class(if_route_pattern('db.sales.customer_groups')) }}" href="{{route('db.sales.customer_groups')}}">{{ __('sidebar-ext.db.sales.customer_groups')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.sales.customers')) }}" href="{{route('db.sales.customers')}}">{{ __('sidebar-ext.db.sales.customers')}}</a>
            </li>
        </ul>
    </li>
@endrole