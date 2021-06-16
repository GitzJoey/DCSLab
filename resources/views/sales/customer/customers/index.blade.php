@extends('layouts.codebase.backend')

@section('title')
    {{ __('sales_customers.title') }}
@endsection

@section('content')
    <div id="customerVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/customer.js') }}"></script>
@endsection