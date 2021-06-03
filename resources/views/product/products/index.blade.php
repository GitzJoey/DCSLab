@extends('layouts.codebase.backend')

@section('title')
    {{ __('products.title') }}
@endsection

@section('content')
    <div id="productVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/product.js') }}"></script>
@endsection