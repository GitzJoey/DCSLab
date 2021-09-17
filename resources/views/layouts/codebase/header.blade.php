<header id="page-header">
    <div class="content-header">
        <div class="content-header-section">
            <button type="button" class="btn btn-circle btn-dual-secondary" data-toggle="layout" data-action="sidebar_toggle">
                <i class="fa fa-navicon"></i>
            </button>

            <button type="button" class="btn btn-circle btn-dual-secondary" data-toggle="layout" data-action="header_search_on">
                <i class="fa fa-search"></i>
            </button>

            @isset($selectedCompany)
                <button type="button" class="btn btn-rounded btn-dual-secondary" id="page-header-company-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-umbrella d-sm-none"></i>
                    @foreach (auth()->user()->companies()->get() as $c)
                        @if ($c->hId == $selectedCompany)
                            <span class="d-none d-sm-inline-block">{{ $c->name }}</span>
                        @endif
                    @endforeach
                </button>
                <div class="dropdown-menu dropdown-menu-left min-width-200" aria-labelledby="page-header-company-dropdown">
                    @foreach (auth()->user()->companies()->get() as $c)
                        @if ($c->hId != $selectedCompany)
                            <a class="dropdown-item" href="{{ route('db.company.switch_company', $c->hId) }}">
                                {{ $c->name }}
                            </a>
                        @endif
                    @endforeach
                </div>
            @endisset
        </div>

        <div class="content-header-section">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-rounded btn-dual-secondary" id="page-header-user-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-user d-sm-none"></i>
                    <span class="d-none d-sm-inline-block"><i class="fa fa-user"></i></span>
                    <i class="fa fa-angle-down ml-5"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right min-width-200" aria-labelledby="page-header-user-dropdown">
                    <h5 class="h6 text-center py-10 mb-5 border-b text-uppercase">{{ Auth::user()->roles()->first()->name }}</h5>
                    <a class="dropdown-item" href="{{ route('db.profile') }}">
                        <i class="icon icon-user mr-5"></i> {{ __('dashboard.profile') }}
                    </a>
                    <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('db.inbox') }}">
                        <span><i class="icon icon-envelope-open mr-5"></i> {{ __('dashboard.inbox') }}</span>
                        @if (Auth::user()->unreadMessagesCount() > 0)
                            <span class="badge badge-primary">{{ Auth::user()->unreadMessagesCount() }}</span>
                        @endif
                    </a>
                    <a class="dropdown-item" href="{{ route('db.activity') }}">
                        <i class="icon icon-hourglass mr-5"></i> {{ __('dashboard.activity') }}
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
                    <span class="badge badge-primary badge-pill"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-right min-width-300" aria-labelledby="page-header-notifications">
                    <h5 class="h6 text-center py-10 mb-0 border-b text-uppercase">{{ __('dashboard.notifications') }}</h5>
                    <ul class="list-unstyled my-20">
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
