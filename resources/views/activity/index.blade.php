@extends('layouts.codebase.backend')

@section('title')
    {{ __('activity.title') }}
@endsection

@section('content')
    <div class="block">
        <div class="block-header block-header-default">
            <h3 class="block-title">{{ __('activity.page_title') }}</h3>
            <div class="block-options">
                <button type="button" class="btn-block-option" data-toggle="block-option" data-action="fullscreen_toggle"></button>
                <button type="button" class="btn-block-option" data-toggle="block-option" data-action="content_toggle"></button>
            </div>
        </div>
        <div class="block-content block-content-full">
            <ul class="list list-timeline list-timeline-modern pull-t">
                @foreach($activity as $act)
                    <li>
                        <div class="list-timeline-time">{{ $act->created_at->diffForHumans() }}</div>
                        @if ($act->log_name == 'AuthActivity')
                            <i class="list-timeline-icon fa fa-key bg-corporate"></i>
                        @elseif ($act->log_name == 'RoutingActivity')
                            <i class="list-timeline-icon fa fa-paper-plane-o bg-success"></i>
                        @else
                            <i class="list-timeline-icon fa fa-cog bg-gray-darker"></i>
                        @endif
                        <div class="list-timeline-content">
                            <p class="font-w600">{{ $act->description }}</p>
                            @if (!empty(json_decode($act->properties)))
                                <p>{{ $act->properties }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
