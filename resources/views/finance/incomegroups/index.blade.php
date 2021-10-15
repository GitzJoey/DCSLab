@extends('layouts.codebase.backend')

@section('title')
    {{ __('income_groups.title') }}
@endsection

@section('content')
    <div id="incomegroupVue"></div>
@endsection

@section('ziggy')
    @routes('incomegroup')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/incomegroup.js') }}"></script>
@endsection