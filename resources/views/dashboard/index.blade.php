@extends('layouts.codebase.backend')

@section('title')
    {{ __('dashboard.title') }}
@endsection

@section('content')
    @role('user|dev')
        <div class="row">
            <div class="col-md-12">
                <h3>{{ trans('dashboard.user_dashboard_message_1') }}</h3>
                {{ trans('dashboard.user_dashboard_message_2') }}
                {{ trans('dashboard.user_dashboard_message_3') }}
                <br>
            </div>
        </div>
        <br>
        <div class="row invisible" data-toggle="appear">
            <div class="col-6 col-md-4 col-xl-2">
                <a class="block block-link-shadow text-center" href="">
                    <div class="block-content">
                        <p class="mt-5">
                            <img alt="" src="{{ asset('images/pos_system.png') }}" width="64" height="64"/>
                        </p>
                        <p class="font-w600">POS System</p>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a class="block block-link-shadow text-center" href="">
                    <div class="block-content ribbon ribbon-bookmark ribbon-primary ribbon-left">
                        <div class="ribbon-box">Beta</div>
                        <p class="mt-5">
                            <img alt="" src="{{ asset('images/warehouse-system.jpg') }}" width="64" height="64"/>
                        </p>
                        <p class="font-w600">Warehouse System</p>
                    </div>
                </a>
            </div>
        </div>
    @endrole
@endsection

@section('js_after')
@endsection
