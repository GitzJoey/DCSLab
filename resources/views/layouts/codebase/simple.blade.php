<!doctype html>
<html lang="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

        <title>@yield('title', 'DCSLab') - {{ config('app.name') }}</title>

        <meta name="description" content="DCSLab - Doctor Computer SG Lab">
        <meta name="author" content="GitzJoey">
        <meta name="robots" content="noindex, nofollow">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">

        @yield('css_before')

        <link rel="stylesheet" id="css-main" href="{{ mix('/css/codebase/codebase.css') }}">

        <link rel="stylesheet" id="css-theme" href="{{ asset('css/codebase/themes/corporate.css') }}">

        @yield('css_after')
    </head>
    <body>
        <div id="page-container" class="main-content-boxed">
            <main id="main-container">
                @yield('content')
            </main>
        </div>

        @yield('custom_content')

        @yield('js_before')

        <script src="{{ mix('js/codebase/codebase.app.js') }}"></script>

        @yield('js_after')
    </body>
</html>
