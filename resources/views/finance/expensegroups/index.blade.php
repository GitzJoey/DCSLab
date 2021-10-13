@extends('layouts.codebase.backend')

@section('title')
    {{ __('expense_groups.title') }}
@endsection

@section('content')
    <div id="expensegroupVue"></div>
@endsection

@section('ziggy')
    @routes('expensegroup')
@endsection

@section('js_after')
    <script src="{{ mix('js/apps/expensegroup.js') }}"></script>
@endsection