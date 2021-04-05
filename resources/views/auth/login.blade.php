@extends('layouts.codebase.simple')

@section('title')
    {{ __('auth.login.title') }}
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
        <div class="hero-static col-md-6 col-xl-4 d-flex align-items-center bg-white invisible" data-toggle="appear" data-class="animated fadeInRight">
            <div class="content content-full">
                <div class="px-30 py-10">
                    <a class="link-effect font-w700" href="index.html">
                        <i class="si si-fire"></i>
                        <span class="font-size-xl text-primary-dark">DCS</span><span class="font-size-xl">Lab</span>
                    </a>
                    <h1 class="h3 font-w700 mt-30 mb-10">Dashboard</h1>
                    <h2 class="h5 font-w400 text-muted mb-0">Please sign in</h2>
                </div>

                <form class="js-validation-signin px-30" action="{{ route('login') }}" method="post">
                    @csrf
                    <div class="form-group row @error('email') is-invalid @enderror">
                        <div class="col-12">
                            <div class="form-material floating">
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                                <label for="email">{{ __('auth.login.email') }}</label>
                                @error('email')
                                    <div class="invalid-feedback animated fadeInDown">{{ $errors->first('email') }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group row @error('password') is-invalid @enderror">
                        <div class="col-12">
                            <div class="form-material floating">
                                <input type="password" class="form-control" id="password" name="password">
                                <label for="password">{{ __('auth.login.password') }}</label>
                                @error('password')
                                    <div class="invalid-feedback animated fadeInDown">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                <label class="custom-control-label" for="remember">{{ __('auth.login.remember_me') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-sm btn-hero btn-alt-primary">
                            <i class="si si-login mr-10"></i> {{ __('buttons.login') }}
                        </button>
                        <div class="mt-30">
                            <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="{{ route('register') }}">
                                <i class="fa fa-plus mr-5"></i> {{ __('buttons.register') }}
                            </a>
                            <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="{{ route('password.request') }}">
                                <i class="fa fa-warning mr-5"></i> {{ __('buttons.reset_password') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
