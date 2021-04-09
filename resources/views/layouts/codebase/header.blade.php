<header id="page-header">
    <div class="content-header">
        <div class="content-header-section">
            <button type="button" class="btn btn-circle btn-dual-secondary" data-toggle="layout" data-action="sidebar_toggle">
                <i class="fa fa-navicon"></i>
            </button>

            <button type="button" class="btn btn-circle btn-dual-secondary" data-toggle="layout" data-action="header_search_on">
                <i class="fa fa-search"></i>
            </button>
        </div>

        <div class="content-header-section">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-rounded btn-dual-secondary" id="page-header-user-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-user d-sm-none"></i>
                    <span class="d-none d-sm-inline-block">{{ Auth::user()->name }}</span>
                    <i class="fa fa-angle-down ml-5"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right min-width-200" aria-labelledby="page-header-user-dropdown">
                    <h5 class="h6 text-center py-10 mb-5 border-b text-uppercase">{{ Auth::user()->roles()->first()->name }}</h5>
                    <a class="dropdown-item" href="{{ route('db.profile') }}">
                        <i class="icon icon-user mr-5"></i> {{ __('dashboard.profile') }}
                    </a>
                    <a class="dropdown-item d-flex align-items-center justify-content-between" href="#">
                        <span><i class="icon icon-envelope-open mr-5"></i> {{ __('dashboard.inbox') }}</span>
                        <span class="badge badge-primary">3</span>
                    </a>
                    <a class="dropdown-item" href="#" data-toggle="layout" data-action="side_overlay_toggle">
                        <i class="icon icon-wrench mr-5"></i> {{ __('dashboard.settings') }}
                    </a>

                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="icon icon-logout mr-5"></i> {{ __('buttons.logout') }}
                    </a>
                    <form id="logout-form" action="{{ url('/logout') }}" method="POST">{{ csrf_field() }}</form>
                </div>
            </div>

            <div class="btn-group" role="group">
                <button type="button" class="btn btn-rounded btn-dual-secondary" id="page-header-language-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-globe"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right min-width-150" aria-labelledby="page-header-language-dropdown">
                    @foreach(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <a class="dropdown-item" hreflang="{{$localeCode}}" href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL($localeCode) }}">
                            @if (App::getLocale() == $localeCode)
                                <strong>{{ $properties['native'] }}</strong>
                            @else
                                {{ $properties['native'] }}
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="btn-group" role="group">
                <button type="button" class="btn btn-rounded btn-dual-secondary" id="page-header-notifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-flag"></i>
                    <span class="badge badge-primary badge-pill">5</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right min-width-300" aria-labelledby="page-header-notifications">
                    <h5 class="h6 text-center py-10 mb-0 border-b text-uppercase">{{ __('dashboard.notifications') }}</h5>
                    <ul class="list-unstyled my-20">
                        <li>
                            <a class="text-body-color-dark media mb-15" href="javascript:void(0)">
                                <div class="ml-5 mr-15">
                                    <i class="fa fa-fw fa-check text-success"></i>
                                </div>
                                <div class="media-body pr-10">
                                    <p class="mb-0">You’ve upgraded to a VIP account successfully!</p>
                                    <div class="text-muted font-size-sm font-italic">15 min ago</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="text-body-color-dark media mb-15" href="javascript:void(0)">
                                <div class="ml-5 mr-15">
                                    <i class="fa fa-fw fa-exclamation-triangle text-warning"></i>
                                </div>
                                <div class="media-body pr-10">
                                    <p class="mb-0">Please check your payment info since we can’t validate them!</p>
                                    <div class="text-muted font-size-sm font-italic">50 min ago</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="text-body-color-dark media mb-15" href="javascript:void(0)">
                                <div class="ml-5 mr-15">
                                    <i class="fa fa-fw fa-times text-danger"></i>
                                </div>
                                <div class="media-body pr-10">
                                    <p class="mb-0">Web server stopped responding and it was automatically restarted!</p>
                                    <div class="text-muted font-size-sm font-italic">4 hours ago</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="text-body-color-dark media mb-15" href="javascript:void(0)">
                                <div class="ml-5 mr-15">
                                    <i class="fa fa-fw fa-exclamation-triangle text-warning"></i>
                                </div>
                                <div class="media-body pr-10">
                                    <p class="mb-0">Please consider upgrading your plan. You are running out of space.</p>
                                    <div class="text-muted font-size-sm font-italic">16 hours ago</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="text-body-color-dark media mb-15" href="javascript:void(0)">
                                <div class="ml-5 mr-15">
                                    <i class="fa fa-fw fa-plus text-primary"></i>
                                </div>
                                <div class="media-body pr-10">
                                    <p class="mb-0">New purchases! +$250</p>
                                    <div class="text-muted font-size-sm font-italic">1 day ago</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center mb-0" href="javascript:void(0)">
                        <i class="fa fa-flag mr-5"></i> View All
                    </a>
                </div>
            </div>

            <button type="button" class="btn btn-circle btn-dual-secondary" data-toggle="layout" data-action="side_overlay_toggle">
                <i class="fa fa-tasks"></i>
            </button>
        </div>
    </div>

    <div id="page-header-search" class="overlay-header">
        <div class="content-header content-header-fullrow">
            <form action="#" method="POST">
                @csrf
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-secondary" data-toggle="layout" data-action="header_search_off">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <input type="text" class="form-control" placeholder="{{ __('dashboard.search_placeholder') }}" id="page-header-search-input" name="page-header-search-input">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="page-header-loader" class="overlay-header bg-primary">
        <div class="content-header content-header-fullrow text-center">
            <div class="content-header-item">
                <i class="fa fa-sun-o fa-spin text-white"></i>
            </div>
        </div>
    </div>
</header>
