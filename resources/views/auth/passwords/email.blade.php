@extends('layouts.front.master')

@section('title')
    {{ __('auth.reset_password.email.title') }}
@endsection

@section('header_content')
    <div id="reset_password" class="header-hero bg_cover background">
        <div class="container">
            <div class="auth-header-content">
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-xs-12 col-sm-12 col-md-5 bg-white bg-opacity-50 rounded border-1">
                        @if (session('status'))
                            <div class="px-3 py-3">
                                <div class="mb-3">
                                    <div class="alert alert-success">
                                        {{ session('status') }}
                                    </div>
                                </div>
                                <br/>
                                <br/>
                                <br/>
                                <div class="d-flex justify-content-end">
                                    <a class="mx-2" href="{{ route('register') }}">
                                        <i class="icon icon-plus" aria-hidden="true" /> {{ __('auth.buttons.register') }}
                                    </a>
                                    <a class="" href="{{ route('login') }}">
                                        <i class="icon icon-login" aria-hidden="true" /> {{ __('auth.buttons.login') }}
                                    </a>
                                </div>
                            </div>
                        @else
                            <form id="emailResetForm" class="px-3 py-3" action="{{ route('password.email') }}" method="post">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-lable" for="email">{{ __('auth.reset_password.email.email') }}</label>
                                    <input type="email" class="form-control @error('email') border-danger @enderror" id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="form-text text-danger">{{ $errors->first('email') }}</div>
                                    @enderror
                                </div>
                                <div class="mb-2">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('auth.buttons.submit') }}
                                    </button>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <a class="mx-2" href="{{ route('register') }}">
                                        <i class="icon icon-plus" aria-hidden="true" /> {{ __('auth.buttons.register') }}
                                    </a>
                                    <a class="" href="{{ route('login') }}">
                                        <i class="icon icon-login" aria-hidden="true" /> {{ __('auth.buttons.login') }}
                                    </a>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
