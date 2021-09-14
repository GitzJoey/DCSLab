@extends('layouts.codebase.backend')

@section('title')
    {{ __('warehouses.title') }}
@endsection

@section('content')
    <div id="warehouseVue"></div>
@endsection

@section('ziggy')
    @routes('warehouse')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/warehouse.js') }}"></script>
@endsection