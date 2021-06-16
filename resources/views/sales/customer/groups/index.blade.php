@extends('layouts.codebase.backend')

@section('title')
    {{ __('sales_customer_groups.title') }}
@endsection

@section('content')
    <div id="customergroupVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/customergroup.js') }}"></script>
@endsection