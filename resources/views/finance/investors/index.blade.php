@extends('layouts.codebase.backend')

@section('title')
    {{ __('investors.title') }}
@endsection

@section('content')
    <div id="investorVue"></div>
@endsection

@section('ziggy')
    @routes('investor')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/investor.js') }}"></script>
@endsection