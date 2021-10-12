@extends('layouts.front.master')

@section('title')
    {{ __('auth.reset_password.reset.title') }}
@endsection

@section('header_content')
    <div id="reset" class="header-hero bg_cover background">
        <div class="container">
            <div class="auth-header-content">
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col-5 bg-white bg-opacity-50 rounded border-1">
                        <form class="px-3 py-3" action="{{ route('password.update') }}" method="post">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="mb-3">
                                <label class="form-label" for="email">{{ __('auth.reset_password.reset.email') }}</label>
                                <input type="email" class="form-control @error('email') border-danger @enderror" id="email" name="email" value="{{ $email }}" readonly>
                                @error('email')
                                    <div class="form-text text-danger">{{ $errors->first('email') }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">{{ __('auth.reset_password.reset.password') }}</label>
                                <input type="password" class="form-control @error('password') border-danger @enderror" id="password" name="password">
                                @error('password')
                                    <div class="form-text text-danger">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                            <div class="mb-5">
                                <label class="form-label" for="password">{{ __('auth.reset_password.reset.password_confirmation') }}</label>
                                <input type="password" class="form-control @error('password') border-danger @enderror" id="password_confirm" name="password_confirmation">
                                @error('password')
                                    <div class="form-text text-danger">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-sm btn-hero btn-alt-primary">
                                    <i class="si si-login mr-10"></i> {{ __('auth.buttons.submit') }}
                                </button>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a class="mx-2" href="{{ route('register') }}">
                                    <i class="icon icon-plus"></i> {{ __('auth.buttons.register') }}
                                </a>
                                <a class="" href="{{ route('login') }}">
                                    <i class="icon icon-login"></i> {{ __('auth.buttons.login') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
                    <div class="bg-image" style="background-image: url('{{ asset('images/bg1.jpg') }}');">
    <div class="row mx-0 bg-black-op">
        <div class="hero-static col-md-6 col-xl-8 d-none d-md-flex align-items-md-end">
            <div class="p-30 invisible" data-toggle="appear">
                <p class="font-size-h3 font-w600 text-white">
                    Get Inspired and Create.
                </p>
                <p class="font-italic text-white-op">
                    Copyright &copy; <span class="js-year-copy"></span>
                </p>
            </div>
        </div>
        <div class="hero-static col-md-6 col-xl-4 d-flex align-items-center bg-white invisible animate__fadeInRight" data-toggle="appear">
            <div class="content content-full">
                <div class="px-30 py-10">
                    <a class="link-effect font-w700" href="{{ route('welcome') }}">
                        <i class="si si-fire"></i>
                        <span class="font-size-xl text-primary-dark">DCS</span><span class="font-size-xl">Lab</span>
                    </a>
                    <h1 class="h3 font-w700 mt-30 mb-10">{{ __('auth.reset_password.reset.page_title') }}</h1>
                    <h2 class="h5 font-w400 text-muted mb-0">{{ __('auth.reset_password.reset.page_title_desc') }}</h2>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
