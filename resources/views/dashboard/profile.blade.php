@extends('layouts.codebase.backend')

@section('title')
    {{ __('profile.title') }}
@endsection

@section('css_before')
@endsection

@section('css_after')
@endsection

@section('content')
<div class="bg-image bg-image-bottom" style="background-image: url('{{ asset('images/bg6.jpg') }}');">
    <div class="bg-black-op-75 py-30">
        <div class="content content-full text-center">
            <div class="mb-15">
                <a class="img-link" href="#">
                    <img class="img-avatar img-avatar96 img-avatar-thumb" src="{{ asset('images/def-user.png') }}" alt="">
                </a>
            </div>

            <h1 class="h3 text-white font-w700 mb-10">John Smith</h1>
            <h2 class="h5 text-white-op">
                Product Manager <a class="text-primary-light" href="javascript:void(0)">@GraphicXspace</a>
            </h2>

            <!-- Actions -->
            <a href="be_pages_generic_profile.html" class="btn btn-rounded btn-hero btn-sm btn-alt-secondary mb-5">
                <i class="fa fa-arrow-left mr-5"></i> Back to Profile
            </a>
            <!-- END Actions -->
        </div>
    </div>
</div>

@endsection

@section('custom_content')
@endsection

@section('js_before')
@endsection

@section('js_after')
@endsection
