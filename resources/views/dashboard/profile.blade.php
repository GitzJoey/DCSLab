@extends('layouts.codebase.backend')

@section('title')
    {{ __('profile.title') }}
@endsection

@section('css_before')
@endsection

@section('css_after')
@endsection

@section('content')
<div class="bg-image bg-image-bottom" style="background-image: url('{{ asset('images/bg7.jpg') }}');">
    <div class="bg-black-op-75 py-30">
        <div class="content content-full text-center">
            <div class="mb-15">
                <a class="img-link" href="#">
                    <img class="img-avatar img-avatar96 img-avatar-thumb" src="{{ !is_null(\Illuminate\Support\Facades\Auth::user()->profile->img_path) ? asset('storage/'.\Illuminate\Support\Facades\Auth::user()->profile->img_path):asset('images/def-user.png') }}" alt="">
                </a>
            </div>

            <h1 class="h3 text-white font-w700 mb-10">{{ Auth::user()->name }}</h1>
            <h2 class="h5 text-white-op">
                <span class="text-uppercase">{{ Auth::user()->roles()->first()->name }}</span>
            </h2>

            <button type="button" class="btn btn-rounded btn-hero btn-sm btn-alt-primary mb-5">
                <a href="{{ route('db.inbox') }}">{{ __('profile.inbox') }}</a>
            </button>
            &nbsp;
            <button type="button" class="btn btn-rounded btn-hero btn-sm btn-alt-primary mb-5">
                <a href="{{ route('db.activity') }}">{{ __('profile.activity') }}</a>
            </button>
            &nbsp;
            <button type="button" class="btn btn-rounded btn-hero btn-sm btn-alt-primary mb-5">
                <a href="{{ route('db') }}"><i class="fa fa-home"></i></a>
            </button>
        </div>
    </div>
</div>

<div id="profileVue"></div>

@endsection

@section('custom_content')
@endsection

@section('js_before')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/profile.js') }}"></script>
@endsection
