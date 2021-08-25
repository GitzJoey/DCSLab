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
        </ul>
    </li>
@endrole

@role('dev|administrator|pos-owner')
    <li class="{{ active_class(if_route_pattern('db.finance.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-wallet"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.finance')}}</span></a>
        <ul>
            <li>
                <a class="{{ active_class(if_route_pattern('db.finance.cashes')) }}" href="{{route('db.finance.cashes')}}">{{ __('sidebar-ext.db.finance.cashes')}}</a>
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
    <li class="{{ active_class(if_route_pattern('db.product.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-drawer"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.product')}}</span></a>
        <ul>
            <li>
                <a class="{{ active_class(if_route_pattern('db.product.product_groups')) }}" href="{{route('db.product.product_groups')}}">{{ __('sidebar-ext.db.product.product_groups')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.product.product_brands')) }}" href="{{route('db.product.product_brands')}}">{{ __('sidebar-ext.db.product.product_brands')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.product.product_units')) }}" href="{{route('db.product.product_units')}}">{{ __('sidebar-ext.db.product.product_units')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.product.products')) }}" href="{{route('db.product.products')}}">{{ __('sidebar-ext.db.product.products')}}</a>
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