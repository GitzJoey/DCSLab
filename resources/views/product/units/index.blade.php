@extends('layouts.codebase.backend')

@section('title')
    {{ __('units.title') }}
@endsection

@section('content')
    <div id="unitVue"></div>
@endsection

@section('ziggy')
    @routes('unit')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/unit.js') }}"></script>
@endsection