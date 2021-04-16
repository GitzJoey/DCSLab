@extends('layouts.codebase.simple')

@section('content')
    <div class="hero bg-white">
        <div class="hero-inner">
            <div class="content content-full">
                <div class="py-30 text-center">
                    <div class="display-3 text-corporate">
                        <i class="fa fa-ban"></i> {{ __('errors.403.page_title') }}
                    </div>
                    <h1 class="h2 font-w700 mt-30 mb-10">{{ __('errors.403.msg') }}</h1>
                    <h2 class="h3 font-w400 text-muted mb-50">{{ __('errors.403.msg_desc') }}</h2>
                    <a class="btn btn-hero btn-rounded btn-alt-secondary" href="{{ route('db') }}">
                        <i class="fa fa-arrow-left mr-10"></i> {{ __('errors.403.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
