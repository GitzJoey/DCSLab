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
    <link rel="stylesheet" href="{{ mix('/css/start/main.css') }}" />
</head>
<body>
    @yield('content')

    @include('layouts.front.footer')

    <script src="{{ mix('/js/start/main.js') }}"></script>
</body>
</html>
