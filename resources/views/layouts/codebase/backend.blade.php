<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

        <title>@yield('title', 'DCSLab') - {{ config('app.name') }}</title>

        <meta name="description" content="DCSLab - Doctor Computer SG Lab">
        <meta name="author" content="GitzJoey">
        <meta name="robots" content="noindex, nofollow">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="shortcut icon" href="{{ asset('media/favicons/favicon.png') }}">
        <link rel="icon" sizes="192x192" type="image/png" href="{{ asset('media/favicons/favicon-192x192.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('media/favicons/apple-touch-icon-180x180.png') }}">

        @yield('css_before')

        <link rel="stylesheet" id="css-main" href="{{ mix('/css/codebase/codebase.css') }}">

        <!--<link rel="stylesheet" id="css-theme" href="{{ asset('css/codebase/themes/corporate.css') }}">-->
        <!--<link rel="stylesheet" id="css-theme" href="{{ asset('css/codebase/themes/earth.css') }}">-->
        <!--<link rel="stylesheet" id="css-theme" href="{{ asset('css/codebase/themes/elegance.css') }}">-->
        <!--<link rel="stylesheet" id="css-theme" href="{{ asset('css/codebase/themes/flat.css') }}">-->
        <!--<link rel="stylesheet" id="css-theme" href="{{ asset('css/codebase/themes/pulse.css') }}">-->

        @yield('css_after')
    </head>
    <body>
        <div id="page-container" class="sidebar-o enable-page-overlay side-scroll page-header-modern main-content-boxed">
            @include('layouts.codebase.side-overlay')

            @include('layouts.codebase.sidebar')

            @include('layouts.codebase.header')

            <main id="main-container">
                @yield('content')
            </main>

            @include('layouts.codebase.footer')
        </div>

        @yield('js_before')

        <script src="{{ mix('js/codebase/codebase.app.js') }}"></script>

        @yield('js_after')
    </body>
</html>
