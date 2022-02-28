@extends('layouts.front.master')

@section('title')
    {{ __('auth.reset_password.reset.title') }}
@endsection

@section('header_content')
    <div id="reset" class="header-hero bg_cover background">
        <div class="container">
            <div class="auth-header-content">
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-xs-12 col-sm-12 col-md-5 bg-white bg-opacity-50 rounded border-1">
                        <form id="resetForm" class="px-3 py-3" action="{{ route('password.update') }}" method="post">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="mb-2">
                                <label class="form-label" for="email">{{ __('auth.reset_password.reset.email') }}</label>
                                <input type="email" class="form-control @error('email') border-danger @enderror" id="email" name="email" value="{{ $email }}" readonly>
                                @error('email')
                                    <div class="form-text text-danger">{{ $errors->first('email') }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="password">{{ __('auth.reset_password.reset.password') }}</label>
                                <input type="password" class="form-control @error('password') border-danger @enderror" id="password" name="password">
                                @error('password')
                                    <div class="form-text text-danger">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">{{ __('auth.reset_password.reset.password_confirmation') }}</label>
                                <input type="password" class="form-control @error('password') border-danger @enderror" id="password_confirm" name="password_confirmation">
                                @error('password')
                                    <div class="form-text text-danger">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon icon-login"></i> {{ __('auth.buttons.submit') }}
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
@endsection
