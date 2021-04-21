@extends('layouts.codebase.backend')

@section('title')
    {{ __('role.title') }}
@endsection

@section('content')
    <div id="roleVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/role.js') }}"></script>
@endsection
