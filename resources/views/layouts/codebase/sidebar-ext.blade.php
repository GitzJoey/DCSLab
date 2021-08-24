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
                <a class="{{ active_class(if_route_pattern('db.cashes')) }}" href="{{route('db.cashes')}}">{{ __('sidebar-ext.db.cashes')}}</a>
            </li>
        </ul>
    </li>
@endrole

@role('dev|administrator|pos-owner')
    <li class="{{ active_class(if_route_pattern('db.purchase.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-note"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.purchase')}}</span></a>
        <ul>
            <li>
                <a class="{{ active_class(if_route_pattern('db.suppliers')) }}" href="{{route('db.suppliers')}}">{{ __('sidebar-ext.db.suppliers')}}</a>
            </li>
        </ul>
    </li>
@endrole

@role('dev|administrator|pos-owner')
    <li class="{{ active_class(if_route_pattern('db.product.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-drawer"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.product')}}</span></a>
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
@endrole

@role('dev|administrator|pos-owner')
    <li class="{{ active_class(if_route_pattern('db.sales.*'), 'open') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-handbag"></i><span class="sidebar-mini-hidden">{{ __('sidebar-ext.db.sales')}}</span></a>
        <ul>
            <li>
                <a class="{{ active_class(if_route_pattern('db.customer_groups')) }}" href="{{route('db.customer_groups')}}">{{ __('sidebar-ext.db.customer_groups')}}</a>
            </li>
            <li>
                <a class="{{ active_class(if_route_pattern('db.customers')) }}" href="{{route('db.customers')}}">{{ __('sidebar-ext.db.customers')}}</a>
            </li>
        </ul>
    </li>
@endrole