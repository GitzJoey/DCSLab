@extends('layouts.codebase.backend')

@section('title')
    {{ __('role.title') }}
@endsection

@section('content')
<div id="roleVue">
    <div class="block block-bordered block-themed" id="list">
        <div class="block-header bg-gray-dark">
            <h3 class="block-title"><strong>{{ __('role.table_title') }}</strong></h3>
            <div class="block-options">
                <button type="button" class="btn-block-option" data-toggle="block-option" data-action="fullscreen_toggle"></button>
                <button type="button" class="btn-block-option" data-toggle="block-option" data-action="pinned_toggle">
                    <i class="icon icon-pin"></i>
                </button>
                <button type="button" class="btn-block-option" data-toggle="block-option" data-action="content_toggle"></button>
            </div>
        </div>
        <div class="block-content">
            <table class="table table-vcenter">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('role.table.cols.name') }}</th>
                        <th>{{ __('role.table.cols.display_name') }}</th>
                        <th>{{ __('role.table.cols.description') }}</th>
                        <th>{{ __('role.table.cols.permissions') }}</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(u, uIdx) in roleList">
                        <td>@{{ u.name }}</td>
                        <td>@{{ u.display_name }}</td>
                        <td>@{{ u.description }}</td>
                        <td></td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="Delete">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="block-content block-content-full block-content-sm bg-body-light font-size-sm">
            <button type="button" class="btn btn-primary min-width-125" data-toggle="click-ripple">{{ __('buttons.create_new') }}</button>
        </div>
    </div>

    <div class="block block-bordered block-themed" id="crud">
        <div class="block-header block-header-default bg-gray-dark">
            <h3 class="block-title"><strong>{{ __('role.crud_title') }}</strong></h3>
            <div class="block-options">
                <button type="button" class="btn-block-option" data-toggle="block-option" data-action="fullscreen_toggle"></button>
                <button type="button" class="btn-block-option" data-toggle="block-option" data-action="pinned_toggle">
                    <i class="icon icon-pin"></i>
                </button>
                <button type="button" class="btn-block-option" data-toggle="block-option" data-action="content_toggle"></button>
            </div>
        </div>
        <div class="block-content">
            <form id="roleForm" method="post">
                <input type="hidden" name="hId" value=""/>
                <div class="form-group row">
                    <label for="inputName" class="col-2 col-form-label">{{ __('role.fields.name') }}</label>
                    <div class="col-md-10">
                        <input id="inputName" name="name" type="text" class="form-control" placeholder="{{ __('role.fields.name') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputName" class="col-2 col-form-label">{{ __('role.fields.name') }}</label>
                    <div class="col-md-10">
                        <input id="inputName" name="name" type="text" class="form-control" placeholder="{{ __('role.fields.name') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputName" class="col-2 col-form-label">{{ __('role.fields.name') }}</label>
                    <div class="col-md-10">
                        <input id="inputName" name="name" type="text" class="form-control" placeholder="{{ __('role.fields.name') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputName" class="col-2 col-form-label">{{ __('role.fields.name') }}</label>
                    <div class="col-md-10">
                        <input id="inputName" name="name" type="text" class="form-control" placeholder="{{ __('role.fields.name') }}">
                    </div>
                </div>

            </form>
        </div>
        <div class="block-content block-content-full block-content-sm bg-body-light font-size-sm">
            <button type="button" class="btn btn-primary min-width-125" data-toggle="click-ripple">{{ __('buttons.submit') }}</button>&nbsp;&nbsp;&nbsp;
            <button type="button" class="btn btn-secondary min-width-125" data-toggle="click-ripple">{{ __('buttons.reset') }}</button>
        </div>
    </div>
</div>
@endsection

@section('js_after')
    <script type="text/javascript">
    </script>
@endsection
