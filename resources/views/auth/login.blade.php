@extends('layouts.front.master')

@section('title')
    {{ __('auth.login.title') }}
@endsection

@section('header_content')
    <div id="login" class="header-hero bg_cover background">
        <div class="container">
            <div class="auth-header-content">
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-xs-12 col-sm-12 col-md-5 bg-white bg-opacity-50 rounded border-1">
                        <form id="loginForm" class="px-3 py-3" action="{{ route('login') }}" method="post">
                            @csrf
                            <div class="mb-2">
                                <label for="email" class="form-label">{{ __('auth.login.email') }}</label>
                                <input type="email" class="form-control @error('email') border-danger @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('auth.login.email') }}">
                                @error('email')
                                    <div class="form-text text-danger">{{ $errors->first('email') }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="password">{{ __('auth.login.password') }}</label>
                                <input type="password" class="form-control @error('password') border-danger @enderror" id="password" name="password" placeholder="*********">
                                @error('password')
                                    <div class="form-text text-danger">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        {{ __('auth.login.remember_me') }}
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon icon-login" aria-hidden="true"></i> {{ __('auth.buttons.login') }}
                                </button>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a class="me-2" href="{{ route('register') }}">
                                    <i class="icon icon-plus" aria-hidden="true"></i> {{ __('auth.buttons.register') }}
                                </a>
                                <a class="" href="{{ route('password.request') }}">
                                    <i class="icon icon-support" aria-hidden="true"></i> {{ __('auth.buttons.reset_password') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
