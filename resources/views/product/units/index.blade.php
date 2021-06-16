@extends('layouts.codebase.backend')

@section('title')
    {{ __('product_units.title') }}
@endsection

@section('content')
    <div id="productunitVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/productunit.js') }}"></script>
@endsection