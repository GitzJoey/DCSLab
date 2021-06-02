@extends('layouts.codebase.backend')

@section('title')
    {{ __('product_groups.title') }}
@endsection

@section('content')
    <div id="productgroupVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/productgroup.js') }}"></script>
@endsection