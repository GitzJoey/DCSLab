@extends('layouts.codebase.backend')

@section('title')
    {{ __('dashboard.title') }}
@endsection

@section('content')
    <div id="test">
        <Test/>
    </div>
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/main.app.js') }}"></script>
    <script type="text/javascript">
        const app = createApp({
            components: {
                Test
            }
        });
    </script>
@endsection
