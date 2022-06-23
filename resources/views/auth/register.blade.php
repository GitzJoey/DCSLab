@extends('layouts.front.master')

@section('title')
    {{ __('auth.register.title') }}
@endsection

@section('header_content')
    <div id="register" class="header-hero bg_cover background">
        <div class="container">
            <div class="auth-header-content">
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-xs-12 col-sm-12 col-md-5 bg-white bg-opacity-50 rounded border-1">
                        <form id="registerForm" class="px-3 py-3" action="{{ route('register') }}" method="post">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label" for="name"> {{ __('auth.register.name') }}</label>
                                <input type="text" class="form-control @error('name') border-danger @enderror" id="name" name="name" value="{{ old('name') }}">
                                @error('name')
                                    <div class="form-text text-danger">{{ $errors->first('name') }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="email">{{ __('auth.register.email') }}</label>
                                <input type="email" class="form-control @error('email') border-danger @enderror" id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="form-text text-danger">{{ $errors->first('email') }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="text-label" for="password">{{ __('auth.register.password') }}</label>
                                <input type="password" class="form-control @error('password') border-danger @enderror" id="password" name="password">
                                @error('password')
                                    <div class="form-text text-danger">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="password_confirmation">{{ __('auth.register.password_confirmation') }}</label>
                                <input type="password" class="form-control @error('password') border-danger @enderror" id="password_confirmation" name="password_confirmation">
                                @error('password')
                                    <div class="form-text text-danger">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input class="form-check-input @error('terms') border-danger @enderror" type="checkbox" value="checked" id="terms" name="terms">
                                    <label class="form-check-label" for="remember">
                                        {{ __('auth.register.agree_1').__('auth.register.agree_2') }}
                                    </label>
                                </div>
                                @error('terms')
                                    <div class="form-text text-danger">{{ $errors->first('terms') }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <div class="col-12">
                                    <img class="img px-1" src="{{ captcha_src('flat') }}" alt="" /><a href="{{ route('register') }}"><button class="btn bg-white"><i class="icon icon-refresh" aria-hidden="true" /></button></a>
                                    <input class="form-control @error('captcha') border-danger @enderror" type="text" name="captcha">
                                    @error('captcha')
                                        <div class="form-text text-danger">{{ $errors->first('captcha') }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-2">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('auth.buttons.submit') }}
                                </button>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a class="mx-2" href="#" data-bs-toggle="modal" data-bs-target="#modal-terms">
                                    <i class="icon icon-book-open" aria-hidden="true" /> {{ __('auth.buttons.read_term') }}
                                </a>
                                <a class="" href="{{ route('login') }}">
                                    <i class="icon icon-login" aria-hidden="true" /> {{ __('auth.buttons.login') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
@endsection

@section('custom_content')
<div class="modal fade" id="modal-terms" tabindex="-1" role="dialog" aria-labelledby="modal-terms" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-slidedown" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('auth.register.terms_and_cond') }}</h3>
            </div>
            <div class="modal-body">
                <p>&nbsp;</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('auth.buttons.close') }}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="icon icon-check" aria-hidden="true" /> {{ __('auth.buttons.accept') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
