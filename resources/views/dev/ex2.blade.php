@extends('layouts.codebase.backend')

@section('content')

    <div id="example1" class="demo">
        <p>Using mustaches: @{{ rawHtml }}</p>
        <p>Using v-html directive: <span v-html="rawHtml"></span></p>
    </div>
@endsection

@section('js_after')
    <script>
        const RenderHtmlApp = {
            data() {
                return {
                    rawHtml: '<span style="color: red">This should be red.</span>'
                }
            }
        }

        createApp(RenderHtmlApp).mount('#example1')
    </script>
@endsection
