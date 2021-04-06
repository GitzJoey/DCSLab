@extends('layouts.codebase.simple')

@section('content')
    {{ __('You are logged in!') }}
@endsection

@section('js_after')
    <script>
        setTimeout(function(){
            window.location.href = '{{ route('dashboard') }}';
        }, 1000);
    </script>
@endsection
