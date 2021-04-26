<li class="{{ active_class(if_route_pattern('db.admin.users.*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">Finance</span></a>
    <ul>
        <li>
            <a href="">Cash</a>
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
            <a href="">Products</a>
        </li>
    </ul>
</li>

<li class="{{ active_class(if_route_pattern('db.admin.users.*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">Sales</span></a>
    <ul>
        <li>
            <a href="">Customer Group</a>
        </li>
        <li>
            <a href="">Customer</a>
        </li>
    </ul>
</li>