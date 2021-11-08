@extends('layouts.codebase.backend')

@section('title')
    {{ __('capital_groups.title') }}
@endsection

@section('content')
    <div id="capitalgroupVue"></div>
@endsection

@section('ziggy')
    @routes('capitalgroup')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/capitalgroup.js') }}"></script>
@endsection