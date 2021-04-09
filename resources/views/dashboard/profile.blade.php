@extends('layouts.codebase.backend')

@section('title')
    {{ __('profile.title') }}
@endsection

@section('css_before')
@endsection

@section('css_after')
@endsection

@section('content')
<div class="bg-image bg-image-bottom" style="background-image: url('{{ asset('images/bg7.jpg') }}');">
    <div class="bg-black-op-75 py-30">
        <div class="content content-full text-center">
            <div class="mb-15">
                <a class="img-link" href="#">
                    <img class="img-avatar img-avatar96 img-avatar-thumb" src="{{ asset('images/def-user.png') }}" alt="">
                </a>
            </div>

            <h1 class="h3 text-white font-w700 mb-10">{{ Auth::user()->name }}</h1>
            <h2 class="h5 text-white-op">
                <span class="text-uppercase">{{ Auth::user()->roles()->first()->name }}</span>
            </h2>

            <button type="button" class="btn btn-rounded btn-hero btn-sm btn-alt-primary mb-5">
                {{ __('dashboard.inbox') }}
            </button>
            &nbsp;
            <button type="button" class="btn btn-rounded btn-hero btn-sm btn-alt-primary mb-5">
                {{ __('dashboard.settings') }}
            </button>
            &nbsp;
            <button type="button" class="btn btn-rounded btn-hero btn-sm btn-alt-primary mb-5">
                <i class="fa fa-home"></i>
            </button>
        </div>
    </div>
</div>

<div class="block">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <i class="fa fa-user-circle mr-5 text-muted"></i> {{ __('profile.page_title') }}
        </h3>
    </div>
    <div class="block-content">
        <form action="#" method="POST" enctype="multipart/form-data">
            <div class="row items-push">
                <div class="col-lg-3">
                    <p class="text-muted">
                        &nbsp;
                    </p>
                </div>
                <div class="col-lg-7 offset-lg-1">
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="name">{{ __('profile.fields.name') }}</label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name" value="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="email">{{ __('profile.fields.company_name') }}</label>
                            <input type="email" class="form-control form-control-lg" id="company_name" name="company_name">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="email">{{ __('profile.fields.email') }}</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-10 col-xl-6">
                            <div class="push">
                                <img class="img-avatar" src="{{ asset('images/def-user.png') }}" alt="">
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="avatar" name="avatar" data-toggle="custom-file-input">
                                <label class="custom-file-label" for="avatar">Browse...</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="email">{{ __('profile.fields.address') }}</label>
                            <input type="email" class="form-control form-control-lg" id="address" name="address">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="email">{{ __('profile.fields.city') }}</label>
                            <input type="email" class="form-control form-control-lg" id="city" name="city">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="email">{{ __('profile.fields.postal_code') }}</label>
                            <input type="email" class="form-control form-control-lg" id="postal_code" name="postal_code">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="email">{{ __('profile.fields.country') }}</label>
                            <input type="email" class="form-control form-control-lg" id="country" name="country">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="email">{{ __('profile.fields.ic_num') }}</label>
                            <input type="email" class="form-control form-control-lg" id="ic_num" name="ic_num">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="email">{{ __('profile.fields.tax_id') }}</label>
                            <input type="email" class="form-control form-control-lg" id="tax_id" name="tax_id">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="email">{{ __('profile.fields.remarks') }}</label>
                            <input type="email" class="form-control form-control-lg" id="remarks" name="remarks">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-alt-primary">{{ __('buttons.update') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('custom_content')
@endsection

@section('js_before')
@endsection

@section('js_after')
@endsection
