<!doctype html>
<html lang="{{ app()->getLocale() }}" class="theme-1">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

        <title>{{ config('app.name') }}</title>

        <meta name="description" content="DCSLab - Doctor Computer SG Lab">
        <meta name="author" content="GitzJoey">
        <meta name="robots" content="noindex, nofollow">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">

        @vite('resources/css/midone/app.css')
    </head>
    <body class="main">
        <div id="app"></div>
        @vite('resources/js/midone/main.js')
    </body>
</html>
