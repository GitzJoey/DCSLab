<footer id="page-footer">
    <div class="content py-20 font-size-sm clearfix">
        <div class="float-right">
            <a href="{{ route('logs') }}" target="_blank"><span class="fa fa-code fa-fw"></span> <small>{{ round(microtime(true) - LARAVEL_START, 2) }}s</small></a>
        </div>
        <div class="float-left">
            <strong>Copyright &copy; {{ \Carbon\Carbon::today()->format('Y') }} <a href="https://www.github.com/GitzJoey">GitzJoey</a>&nbsp;&amp;&nbsp;<a href="#">Contributors</a>.</strong> All rights reserved. Powered By Coffee &amp; Curiosity.
        </div>
    </div>
</footer>
