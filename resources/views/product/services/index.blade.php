@extends('layouts.codebase.backend')

@section('title')
    {{ __('product_service.title') }}
@endsection

@section('content')
    <div id="serviceVue"></div>
@endsection

@section('ziggy')
    @routes('service')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/service.js') }}"></script>
@endsection