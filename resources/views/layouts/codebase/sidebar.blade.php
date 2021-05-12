<nav id="sidebar">
    <div class="sidebar-content">
        <div class="content-header content-header-fullrow px-15">
            <div class="content-header-section sidebar-mini-visible-b">
                <span class="content-header-item font-w700 font-size-xl float-left animated fadeIn">
                    <span class="text-dual-primary-dark">c</span><span class="text-primary">b</span>
                </span>
            </div>

            <div class="content-header-section text-center align-parent sidebar-mini-hidden">
                <button type="button" class="btn btn-circle btn-dual-secondary d-lg-none align-v-r" data-toggle="layout" data-action="sidebar_close">
                    <i class="fa fa-times text-danger"></i>
                </button>

                <div class="content-header-item">
                    <a class="link-effect font-w700" href="{{ route('db') }}">
                        <i class="si si-fire text-primary"></i>
                        <span class="font-size-xl text-dual-primary-dark">DCS</span><span class="font-size-xl text-primary">Lab</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="js-sidebar-scroll">
            <div class="content-side content-side-full content-side-user px-10 align-parent">
                <div class="sidebar-mini-visible-b align-v animated fadeIn">
                    <img class="img-avatar img-avatar32" src="{{ asset('images/def-user.png') }}" alt="">
                </div>

                <div class="sidebar-mini-hidden-b text-center">
                    <a class="img-link" href="{{ route('db.profile') }}">
                        <img class="img-avatar" src="{{ !is_null(\Illuminate\Support\Facades\Auth::user()->profile->img_path) ? asset('storage/'.\Illuminate\Support\Facades\Auth::user()->profile->img_path):asset('images/def-user.png') }}" alt="">
                    </a>
                    <ul class="list-inline mt-10">
                        <li class="list-inline-item">
                            <a class="link-effect text-dual-primary-dark font-size-sm font-w600 text-uppercase" href="{{ route('db.profile') }}">{{ Auth::user()->name }}</a>
                        </li>
                        <li class="list-inline-item">
                            <a class="link-effect text-dual-primary-dark" data-toggle="layout" data-action="sidebar_style_inverse_toggle" href="javascript:void(0)">
                                <i class="icon icon-drop"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a class="link-effect text-dual-primary-dark" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="icon icon-logout"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="content-side content-side-full">
                <ul class="nav-main">
                    <li class="nav-main-heading">
                        <span class="sidebar-mini-visible">DB</span><span class="sidebar-mini-hidden">{{ __('sidebar.header.dashboard') }}</span>
                    </li>
                    <li>
                        <a class="{{ active_class(if_route_pattern('db')) }}" href="{{ route('db') }}">
                            <i class="icon icon-cup"></i><span class="sidebar-mini-hide">{{ __('sidebar.item.dashboard.dashboard') }}</span>
                        </a>
                    </li>

                    @include('layouts.codebase.sidebar-ext')
                    @role('administrator|dev')
                        <li class="nav-main-heading">
                            <span class="sidebar-mini-visible">AD</span><span class="sidebar-mini-hidden">{{ __('sidebar.header.adm') }}</span>
                        </li>
                        <li class="{{ active_class(if_route_pattern('db.admin.users.*'), 'open') }}">
                            <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-people"></i><span class="sidebar-mini-hide">{{ __('sidebar.item.adm.users') }}</span></a>
                            <ul>
                                <li>
                                    <a class="{{ active_class(if_route_pattern('db.admin.users.users')) }}" href="{{ route('db.admin.users.users') }}">{{ __('sidebar.item.adm.users.users') }}</a>
                                </li>
                                <li>
                                    <a class="{{ active_class(if_route_pattern('db.admin.users.roles')) }}" href="{{ route('db.admin.users.roles') }}">{{ __('sidebar.item.adm.users.roles') }}</a>
                                </li>
                            </ul>
                        </li>
                    @endrole
                    @role('dev')
                        <li class="nav-main-heading">
                            <span class="sidebar-mini-visible">DV</span><span class="sidebar-mini-hidden">{{ __('sidebar.header.dev') }}</span>
                        </li>
                        <li class="{{ active_class(if_route_pattern('db.dev.tools.*'), 'open') }}">
                            <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-wrench"></i><span class="sidebar-mini-hide">{{ __('sidebar.item.dev.dev_tools') }}</span></a>
                            <ul>
                                <li>
                                    <a class="{{ active_class(if_route_pattern('db.dev.tools.db_backup')) }}" href="{{ route('db.dev.tools.db_backup') }}">{{ __('sidebar.item.dev.dev_tools.db_backup') }}</a>
                                </li>
                            </ul>
                        </li>
                        <li class="{{ active_class(if_route_pattern('db.dev.examples.*'), 'open') }}">
                            <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="icon icon-book-open"></i><span class="sidebar-mini-hide">{{ __('sidebar.item.dev.examples') }}</span></a>
                            <ul>
                                <li>
                                    <a class="{{ active_class(if_route_pattern('db.dev.examples.ex1')) }}" href="{{ route('db.dev.examples.ex1') }}">{{ __('sidebar.item.dev.examples.ex_1') }}</a>
                                </li>
                                <li>
                                    <a class="{{ active_class(if_route_pattern('db.dev.examples.ex2')) }}" href="{{ route('db.dev.examples.ex2') }}">{{ __('sidebar.item.dev.examples.ex_2') }}</a>
                                </li>
                                <li>
                                    <a class="{{ active_class(if_route_pattern('db.dev.examples.ex3')) }}" href="{{ route('db.dev.examples.ex3') }}">{{ __('sidebar.item.dev.examples.ex_3') }}</a>
                                </li>
                                <li>
                                    <a class="{{ active_class(if_route_pattern('db.dev.examples.ex4')) }}" href="{{ route('db.dev.examples.ex4') }}">{{ __('sidebar.item.dev.examples.ex_4') }}</a>
                                </li>
                                <li>
                                    <a class="{{ active_class(if_route_pattern('db.dev.examples.ex5')) }}" href="{{ route('db.dev.examples.ex5') }}">{{ __('sidebar.item.dev.examples.ex_5') }}</a>
                                </li>
                            </ul>
                        </li>
                    @endrole
                </ul>
            </div>
        </div>
    </div>
</nav>
