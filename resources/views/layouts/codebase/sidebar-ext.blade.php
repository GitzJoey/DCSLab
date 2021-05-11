<li class="{{ active_class(if_route_pattern('db.admin.users.*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">Company</span></a>
    <ul>
        <li>
            <a href="{{route('db.company_companies')}}">Companies</a>
        </li>
        <li>
            <a href="{{route('db.company_branches')}}">Branches</a>
        </li>
        <li>
            <a href="{{route('db.company_warehouses')}}">Warehouses</a>
        </li>
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.admin.users.*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">Finance</span></a>
    <ul>
        <li>
            <a href="{{route('db.finance_cashes')}}">Cashes</a>
        </li>
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.admin.users.*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">Product</span></a>
    <ul>
        <li>
            <a href="{{route('db.product_groups')}}">Groups</a>
        </li>
        <li>
            <a href="{{route('db.product_brands')}}">Brands</a>
        </li>
        <li>
            <a href="{{route('db.product_units')}}">Units</a>
        </li>
        <li>
            <a href="{{route('db.product_products')}}">Products</a>
        </li>
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.admin.users.*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">Sales</span></a>
    <ul>
        <li>
            <a href="{{route('db.sales_customer_groups')}}">Customer Groups</a>
        </li>
        <li>
            <a href="{{route('db.sales_customers')}}">Customers</a>
        </li>
    </ul>
</li>