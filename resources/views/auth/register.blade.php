@extends('layouts.codebase.simple')

@section('title')
    {{ __('auth.register.title') }}
@endsection

@section('content')
<div class="bg-image" style="background-image: url('{{ asset('images/bg1.jpg') }}');">
    <div class="row mx-0 bg-earth-op">
        <div class="hero-static col-md-6 col-xl-8 d-none d-md-flex align-items-md-end">
            <div class="p-30 invisible" data-toggle="appear">
                <p class="font-size-h3 font-w600 text-white mb-5">
                    We're very happy you are joining our community!
                </p>
                <p class="font-size-h5 text-white">
                    <i class="fa fa-angles-right"></i> Create your account today and receive our special offers.
                </p>
                <p class="font-italic text-white-op">
                    Copyright &copy; <span class="js-year-copy"></span>
                </p>
            </div>
        </div>
        <div class="hero-static col-md-6 col-xl-4 d-flex align-items-center bg-white">
            <div class="content content-full">
                <div class="px-30 py-10">
                    <a class="link-effect font-w700" href="index.html">
                        <i class="si si-fire"></i>
                        <span class="font-size-xl text-primary-dark">DCS</span><span class="font-size-xl">Lab</span>
                    </a>
                    <h1 class="h3 font-w700 mt-30 mb-10">Create New Account</h1>
                    <h2 class="h5 font-w400 text-muted mb-0">Please add your details</h2>
                </div>

                <form class="px-30" action="{{ route('register') }}" method="post">
                    @csrf
                    <div class="form-group row @error('name') is-invalid @enderror">
                        <div class="col-12">
                            <div class="form-material floating">
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                                <label for="name"> {{ __('auth.register.name') }}</label>
                                @error('name')
                                    <div class="invalid-feedback animate__fadeInDown">{{ $errors->first('name') }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group row @error('email') is-invalid @enderror">
                        <div class="col-12">
                            <div class="form-material floating">
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                                <label for="email">{{ __('auth.register.email') }}</label>
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
                                <label for="password">{{ __('auth.register.password') }}</label>
                                @error('password')
                                    <div class="invalid-feedback animate__fadeInDown">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group row @error('password') is-invalid @enderror">
                        <div class="col-12">
                            <div class="form-material floating">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                <label for="password_confirmation">{{ __('auth.register.password_confirmation') }}</label>
                                @error('password')
                                    <div class="invalid-feedback animate__fadeInDown">{{ $errors->first('password') }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group row @error('terms') is-invalid @enderror">
                        <div class="col-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="terms" name="terms">
                                <label class="custom-control-label" for="terms">{{ __('auth.register.agree_1').__('auth.register.agree_2') }}</label>
                                @error('terms')
                                    <div class="invalid-feedback animate__fadeInDown">{{ $errors->first('terms') }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-sm btn-hero btn-alt-success">
                            {{ __('buttons.submit') }}
                        </button>
                        <div class="mt-30">
                            <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="#" data-toggle="modal" data-target="#modal-terms">
                                <i class="fa fa-book text-muted mr-5"></i> {{ __('buttons.read_term') }}
                            </a>
                            <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="{{ route('login') }}">
                                <i class="fa fa-user text-muted mr-5"></i> {{ __('buttons.login') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_content')
<div class="modal fade" id="modal-terms" tabindex="-1" role="dialog" aria-labelledby="modal-terms" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-slidedown" role="document">
        <div class="modal-content">
            <div class="block block-themed block-transparent mb-0">
                <div class="block-header bg-primary-dark">
                    <h3 class="block-title">{{ __('auth.register.terms_and_cond') }}</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                            <i class="si si-close"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content">
                    <p>&nbsp;</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-alt-success" data-dismiss="modal">
                    <i class="fa fa-check"></i> {{ __('buttons.accept') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
