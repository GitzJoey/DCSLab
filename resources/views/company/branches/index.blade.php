@extends('layouts.codebase.backend')

@section('title')
    {{ __('branches.title') }}
@endsection

@section('content')
    <div id="branchVue"></div>
@endsection

@section('ziggy')
    @routes('branch')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/branch.js') }}"></script>
@endsection