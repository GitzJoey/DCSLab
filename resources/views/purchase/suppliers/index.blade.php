@extends('layouts.codebase.backend')

@section('title')
    {{ __('suppliers.title') }}
@endsection

@section('content')
    <div id="supplierVue"></div>
@endsection

@section('ziggy')
    @routes('supplier')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/supplier.js') }}"></script>
@endsection