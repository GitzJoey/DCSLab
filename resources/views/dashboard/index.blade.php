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
                @if ($roles->contains('pos-owner'))
                    <div class="block block-link-shadow text-center">
                        <div class="block-content">
                            <p class="mt-5">
                                <img alt="" src="{{ asset('images/pos_system.png') }}" width="64" height="64"/>
                            </p>
                            <p class="font-w600">POS System</p>
                            <div class="font-size-sm text-muted" data-toggle="popover" title="POS System" data-placement="bottom" data-content="Activated"><i class="icon icon-check"></i></div>
                        </div>
                    </div>
                @else
                    <a class="block block-link-shadow text-center" href="{{ route('db.activate', 'POS') }}">
                        <div class="block-content">
                            <p class="mt-5">
                                <img alt="" src="{{ asset('images/pos_system.png') }}" width="64" height="64"/>
                            </p>
                            <p class="font-w600">POS System</p>
                            <div class="font-size-sm text-muted" data-toggle="popover" title="POS System" data-placement="bottom" data-content="Click to Activate"><i class="icon icon-info"></i></div>
                        </div>
                    </a>
                @endif
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                @if ($roles->contains('whs-owner'))
                    <div class="block block-link-shadow text-center">
                        <div class="block-content ribbon ribbon-bookmark ribbon-primary ribbon-left">
                            <div class="ribbon-box">Beta</div>
                            <p class="mt-5">
                                <img alt="" src="{{ asset('images/warehouse_system.png') }}" width="64" height="64"/>
                            </p>
                            <p class="font-w600">Warehouse System</p>
                            <div class="font-size-sm text-muted" data-toggle="popover" title="Warehouse System" data-placement="bottom" data-content="Activated"><i class="icon icon-check"></i></div>
                        </div>
                    </div>
                @else
                    <a class="block block-link-shadow text-center" href="{{ route('db.activate', 'WHS') }}">
                        <div class="block-content ribbon ribbon-bookmark ribbon-primary ribbon-left">
                            <div class="ribbon-box">Beta</div>
                            <p class="mt-5">
                                <img alt="" src="{{ asset('images/warehouse_system.png') }}" width="64" height="64"/>
                            </p>
                            <p class="font-w600">Warehouse System</p>
                            <div class="font-size-sm text-muted" data-toggle="popover" title="Warehouse System" data-placement="bottom" data-content="Click to Activate"><i class="icon icon-info"></i></div>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    @endrole
@endsection

@section('js_after')
@endsection
