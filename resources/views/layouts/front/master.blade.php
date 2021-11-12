<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />

        <title> @yield('title', 'DCSLab') - {{ config('app.name') }}</title>

        <meta name="description" content="DCSLab" />
        <meta name="keywords" content="DCSLab" />
        <meta name="author" content="GitzJoey" />

        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
        <link rel="stylesheet" href="{{ mix('/css/start/main.css') }}" />
    </head>
    <body>
        <header class="header-area">
            <div class="navigation navigation-transparent">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <nav class="navbar navbar-expand-lg">
                                <a class="navbar-brand" href="{{ route('front') }}">
                                    <img src="{{ asset('images/g_logo.png') }}" alt="Logo" width="50px" height="50px">
                                </a>

                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="toggler-icon"></span>
                                    <span class="toggler-icon"></span>
                                    <span class="toggler-icon"></span>
                                </button>

                                <div class="collapse navbar-collapse sub-menu-bar" id="navbarMain">
                                    <ul class="navbar-nav m-auto">
                                        @if (Route::current()->getName() == 'front')
                                            <li class="nav-item active" data-menu="home">
                                                <a class="page-scroll text-uppercase fw-bolder" href="{{ route('front').'#home' }}">{{ __('front.menu.home') }}</a>
                                            </li>
                                        @else
                                            <li class="nav-item" data-menu="home">
                                                <a class="page-scroll text-uppercase fw-bolder" href="{{ route('front').'#home' }}">{{ __('front.menu.home') }}</a>
                                            </li>
                                        @endif
                                        <li class="nav-item" data-menu="service">
                                            <a class="page-scroll text-uppercase fw-bolder" href="{{ route('front').'#service' }}">{{ __('front.menu.services') }}</a>
                                        </li>
                                        <li class="nav-item" data-menu="pricing">
                                            <a class="page-scroll text-uppercase fw-bolder" href="{{ route('front').'#pricing' }}">{{ __('front.menu.pricing') }}</a>
                                        </li>
                                        <li class="nav-item" data-menu="contact">
                                            <a class="page-scroll text-uppercase fw-bolder" href="{{ route('front').'#contact' }}">{{ __('front.menu.contact') }}</a>
                                        </li>
                                        @if (Route::current()->getName() == 'login' ||  Route::current()->getName() == 'register')
                                            <li class="nav-item active" data-menu="dashboard">
                                                <a class="text-uppercase fw-bolder" href="{{ route('login') }}">{{ __('front.menu.dashboard') }}</a>
                                            </li>
                                        @else
                                            <li class="nav-item" data-menu="dashboard">
                                                <a class="text-uppercase fw-bolder" href="{{ route('login') }}">{{ __('front.menu.dashboard') }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>

                                <div class="navbar-social d-none d-sm-flex align-items-center">
                                    <ul>
                                        <li class="mx-1"><a href=""><i class="icon icon-social-facebook"></i></a></li>
                                        <li class="mx-1"><a href=""><i class="icon icon-social-twitter"></i></a></li>
                                        <li class="mx-1"><a href=""><i class="icon icon-social-instagram"></i></a></li>
                                        <li class="mx-1"><a href=""><i class="icon icon-social-linkedin"></i></a></li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            @yield('header_content')
        </header>

        @yield('content')

        @yield('custom_content')

        @include('layouts.front.footer')

        <script src="{{ mix('/js/start/main.js') }}"></script>
    </body>
</html>
