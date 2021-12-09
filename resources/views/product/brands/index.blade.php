@extends('layouts.codebase.backend')

@section('title')
    {{ __('product_brands.title') }}
@endsection

@section('content')
    <div id="brandVue"></div>
@endsection

@section('ziggy')
    @routes('brand')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/brand.js') }}"></script>
@endsection