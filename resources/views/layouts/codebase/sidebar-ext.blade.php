<li class="{{ active_class(if_route_pattern('db.admin.users.*'), 'open') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-cup" class="si si-moustache"></i><span class="">Product</span></a>
    <ul>
        <li>
            <a href="{{route('db.product_groups')}}">Groups</a>
        </li>
        <li>
            <a href="">Brands</a>
        </li>
        <li>
            <a href="">Units</a>
        </li>
        <li>
            <a href="">Products</a>
        </li>
    </ul>
</li>