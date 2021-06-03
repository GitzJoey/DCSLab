@extends('layouts.codebase.backend')

@section('title')
    {{ __('product_brands.title') }}
@endsection

@section('content')
    <div id="productbrandVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/productbrand.js') }}"></script>
@endsection