@extends('layouts.codebase.backend')

@section('title')
    {{ __('companies.title') }}
@endsection

@section('content')
    <div id="companyVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/company.js') }}"></script>
@endsection