@extends('layouts.codebase.backend')

@section('title')
    {{ __('inbox.title') }}
@endsection

@section('content')
    <div id="inboxVue"></div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/inbox.js') }}"></script>
@endsection
