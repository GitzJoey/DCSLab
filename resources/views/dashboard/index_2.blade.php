@extends('layouts.codebase.backend')

@section('title')
    {{ __('dashboard.title') }}
@endsection

@section('content')
    <div id="app">
        {{-- Dynamic Template will generate here --}}
    </div>
@endsection

@section('js_after')
    
    
    <script src="{{ asset('js/apps/main.js') }}"></script>
</script>
@endsection
