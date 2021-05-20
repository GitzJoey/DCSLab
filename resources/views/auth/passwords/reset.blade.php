@extends('layouts.codebase.simple')

@section('title')
    {{ __('auth.reset_password.reset.title') }}
@endsection

@section('content')
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

                <form class="js-validation-signin px-30" action="{{ route('password.update') }}" method="post">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="form-group row @error('email') is-invalid @enderror">
                        <div class="col-12">
                            <div class="form-material floating">
                                <input type="email" class="form-control" id="email" name="email" value="{{ $email }}" readonly>
                                <label for="email">{{ __('auth.reset_password.reset.email') }}</label>
                                @error('email')
                                    <div class="invalid-feedback animate__fadeInDown">{{ $errors->first('email') }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group row @error('password') is-invalid @enderror">
                        <div class="col-12">
                            <div class="form-material floating">
                                <input type="password" class="form-control" id="password" name="password">
                                <label for="password">{{ __('auth.reset_password.reset.password') }}</label>
                                @error('password')
                                    <div class="invalid-feedback animate__fadeInDown">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group row @error('password') is-invalid @enderror">
                        <div class="col-12">
                            <div class="form-material floating">
                                <input type="password" class="form-control" id="password_confirm" name="password_confirmation">
                                <label for="password">{{ __('auth.reset_password.reset.password_confirmation') }}</label>
                                @error('password')
                                    <div class="invalid-feedback animate__fadeInDown">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-sm btn-hero btn-alt-primary">
                            <i class="si si-login mr-10"></i> {{ __('buttons.submit') }}
                        </button>
                        <div class="mt-30">
                            <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="{{ route('register') }}">
                                <i class="fa fa-plus mr-5"></i> {{ __('buttons.register') }}
                            </a>
                            <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="{{ route('login') }}">
                                <i class="fa fa-warning mr-5"></i> {{ __('buttons.login') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
