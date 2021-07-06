@extends('layouts.codebase.backend')

@section('title')
    {{ __('houses.title') }}
@endsection

@section('content')
    <div id="houseVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/house.js') }}"></script>
@endsection