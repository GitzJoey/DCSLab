<li class="{{ active_class(if_route_pattern('db.company.*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">{{ __('name.db.company')}}</span></a>
    <ul>
        <li>
            <a class="{{ active_class(if_route_pattern('db.company.companies')) }}" href="{{route('db.company.companies')}}">{{ __('name.db.company.companies') }}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.company.branches')) }}" href="{{route('db.company.branches')}}">{{ __('name.db.company.branches') }}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.company.warehouses')) }}" href="{{route('db.company.warehouses')}}">{{ __('name.db.company.warehouses') }}</a>
        </li>
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.finance*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">{{ __('name.db.finance')}}</span></a>
    <ul>
        <li>
            <a class="{{ active_class(if_route_pattern('db.finance_cashes')) }}" href="{{route('db.finance_cashes')}}">{{ __('name.db.finance_cashes')}}</a>
        </li>
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.product*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">{{ __('name.db.product')}}</span></a>
    <ul>
        <li>
            <a class="{{ active_class(if_route_pattern('db.product_groups')) }}" href="{{route('db.product_groups')}}">{{ __('name.db.product_groups')}}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.product_brands')) }}" href="{{route('db.product_brands')}}">{{ __('name.db.product_brands')}}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.product_units')) }}" href="{{route('db.product_units')}}">{{ __('name.db.product_units')}}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.products')) }}" href="{{route('db.products')}}">{{ __('name.db.products')}}</a>
        </li>
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.sales*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">{{ __('name.db.sales')}}</span></a>
    <ul>
        <li>
            <a class="{{ active_class(if_route_pattern('db.sales_customer_groups')) }}" href="{{route('db.sales_customer_groups')}}">{{ __('name.db.sales_customer_groups')}}</a>
        </li>
        <li>
            <a class="{{ active_class(if_route_pattern('db.sales_customers')) }}" href="{{route('db.sales_customers')}}">{{ __('name.db.sales_customers')}}</a>
        </li>
    </ul>
</li>