@extends('layouts.codebase.backend')

@section('title')
    {{ __('cashes.title') }}
@endsection

@section('content')
    <div id="cashVue"></div>
@endsection

@section('ziggy')
    @routes('cash')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/cash.js') }}"></script>
@endsection