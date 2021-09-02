@extends('layouts.codebase.backend')

@section('title')
    {{ __('user.title') }}
@endsection

@section('content')
    <div id="userVue"></div>
@endsection

@section('ziggy')
    @routes('user')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/user.js') }}"></script>
@endsection
