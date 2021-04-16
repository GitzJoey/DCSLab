@extends('layouts.codebase.simple')

@section('title')
    {{ __('auth.reset_password.email.title') }}
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
                    <a class="link-effect font-w700" href="{{ route('welcome') }}">
                        <i class="si si-fire"></i>
                        <span class="font-size-xl text-primary-dark">DCS</span><span class="font-size-xl">Lab</span>
                    </a>
                    <h1 class="h3 font-w700 mt-30 mb-10">{{ __('auth.reset_password.email.page_title') }}</h1>
                    @if (session('status'))
                        <h2 class="h5 font-w400 text-muted mb-0">&nbsp;</h2>
                    @else
                        <h2 class="h5 font-w400 text-muted mb-0">{{ __('auth.reset_password.email.page_title_desc') }}</h2>
                    @endif
                </div>

                @if (session('status'))
                    <div class="js-validation-signin px-30">
                        <div class="form-group row">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            </div>
                        </div>
                        <br/>
                        <br/>
                        <div class="form-group">
                            <div class="mt-30">
                                <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="{{ route('register') }}">
                                    <i class="fa fa-plus mr-5"></i> {{ __('buttons.register') }}
                                </a>
                                <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="{{ route('login') }}">
                                    <i class="fa fa-user mr-5"></i> {{ __('buttons.login') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <form class="js-validation-signin px-30" action="{{ route('password.email') }}" method="post">
                        @csrf
                        <div class="form-group row @error('email') is-invalid @enderror">
                            <div class="col-12">
                                <div class="form-material floating">
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                                    <label for="email">{{ __('auth.reset_password.email.email') }}</label>
                                    @error('email')
                                        <div class="invalid-feedback animated fadeInDown">{{ $errors->first('email') }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-sm btn-hero btn-alt-primary">
                                {{ __('buttons.submit') }}
                            </button>
                            <div class="mt-30">
                                <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="{{ route('register') }}">
                                    <i class="fa fa-plus mr-5"></i> {{ __('buttons.register') }}
                                </a>
                                <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="{{ route('login') }}">
                                    <i class="fa fa-user mr-5"></i> {{ __('buttons.login') }}
                                </a>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
