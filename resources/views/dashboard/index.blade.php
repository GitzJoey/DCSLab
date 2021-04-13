@extends('layouts.codebase.backend')

@section('title')
    {{ __('dashboard.title') }}
@endsection

@section('content')
    <div id="app">
        @{{msg}}
    </div>
@endsection

@section('js_after')
<script src="https://unpkg.com/vue@3.0.11/dist/vue.global.prod.js"></script>

<script>
const app = Vue.createApp({
    data() {
        return {
            msg: 'I love Vue <3',
        }
    },
})
app.mount('#app')
</script>
@endsection
