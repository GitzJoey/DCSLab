<aside id="side-overlay">
    <div class="content-header content-header-fullrow">
        <div class="content-header-section align-parent">
            <button type="button" class="btn btn-circle btn-dual-secondary align-v-r" data-toggle="layout" data-action="side_overlay_close">
                <i class="fa fa-times text-danger"></i>
            </button>

            <div class="content-header-item">
                <a class="img-link mr-5" href="#">
                    <img class="img-avatar img-avatar32" src="{{ asset('images/def-user.png') }}" alt="">
                </a>
                <a class="align-middle link-effect text-primary-dark font-w600" href="{{ route('db.profile') }}">{{ Auth::user()->name }}</a>
            </div>
        </div>
    </div>

    <div class="content-side">
        <hr />
        @include('layouts.codebase.side-overlay-ext')
    </div>
</aside>
