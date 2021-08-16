@extends('layouts.codebase.backend')

@section('title')
    {{ __('purchase_suppliers.title') }}
@endsection

@section('content')
    <div id="purchasesupplierVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/purchasesupplier.js') }}"></script>
@endsection