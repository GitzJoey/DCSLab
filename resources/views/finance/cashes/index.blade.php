@extends('layouts.codebase.backend')

@section('title')
    {{ __('finance_cashes.title') }}
@endsection

@section('content')
    <div id="financecashVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/financecash.js') }}"></script>
@endsection