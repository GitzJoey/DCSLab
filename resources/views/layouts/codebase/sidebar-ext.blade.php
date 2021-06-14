<li class="{{ active_class(if_route_pattern('db.company.*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">{{ __('sidebar-ext.db.company')}}</span></a>
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
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.finance*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">{{ __('sidebar-ext.db.finance')}}</span></a>
    <ul>
        <li>
            <a class="{{ active_class(if_route_pattern('db.finance_cashes')) }}" href="{{route('db.finance_cashes')}}">{{ __('sidebar-ext.db.finance_cashes')}}</a>
        </li>
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.product*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">{{ __('sidebar-ext.db.product')}}</span></a>
    <ul>
        <li>
            <a class="{{ active_class(if_route_pattern('db.product_groups')) }}" href="{{route('db.product_groups')}}">{{ __('sidebar-ext.db.product_groups')}}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.product_brands')) }}" href="{{route('db.product_brands')}}">{{ __('sidebar-ext.db.product_brands')}}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.product_units')) }}" href="{{route('db.product_units')}}">{{ __('sidebar-ext.db.product_units')}}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.products')) }}" href="{{route('db.products')}}">{{ __('sidebar-ext.db.products')}}</a>
        </li>
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.sales*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">{{ __('sidebar-ext.db.sales')}}</span></a>
    <ul>
        <li>
            <a class="{{ active_class(if_route_pattern('db.sales_customer_groups')) }}" href="{{route('db.sales_customer_groups')}}">{{ __('sidebar-ext.db.sales_customer_groups')}}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.sales_customers')) }}" href="{{route('db.sales_customers')}}">{{ __('sidebar-ext.db.sales_customers')}}</a>
        </li>
    </ul>
</li>