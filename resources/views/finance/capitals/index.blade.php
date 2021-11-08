@extends('layouts.codebase.backend')

@section('title')
    {{ __('capitals.title') }}
@endsection

@section('content')
    <div id="capitalVue"></div>
@endsection

@section('ziggy')
    @routes('capital')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/capital.js') }}"></script>
@endsection