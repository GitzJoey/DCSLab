@extends('layouts.codebase.backend')

@section('title')
    {{ __('employees.title') }}
@endsection

@section('content')
    <div id="employeeVue"></div>
@endsection

@section('ziggy')
    @routes('employee')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/employee.js') }}"></script>
@endsection