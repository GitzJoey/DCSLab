@extends('layouts.codebase.backend')

@section('title')
    {{ __('company_companies.title') }}
@endsection

@section('content')

{{ $test }}

<!-- Normal Form -->
<div class="block">
    <div class="block-header block-header-default">
        <h3 class="block-title">Company Add</h3>
        <div class="block-options">
            <button type="button" class="btn-block-option">
                <i class="si si-wrench"></i>
            </button>
        </div>
    </div>
    <div class="block-content">
        <form action="be_forms_elements_bootstrap.html" method="post" onsubmit="return false;">
            <div class="form-group">
                <label for="example-text-input">Company ID</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Code</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Name</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Address</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">City</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Contact</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Remarks</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>
           
            <div class="form-group row">
                <div class="col-12">
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox1" id="example-checkbox1" value="option1" checked>
                        <label class="custom-control-label" for="example-checkbox1">Is Active</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-alt-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<!-- END Normal Form -->

<!-- Striped Table -->
<div class="block">
    <div class="block-header block-header-default">
        <h3 class="block-title">Company List</h3>
        <div class="block-options">
            <div class="block-options-item">
                <code></code>
            </div>
        </div>
    </div>
    <div class="block-content">
        <table class="table table-striped table-vcenter">
            <thead>
                <tr>
                    <th class="text-center" style="width: 15%;">Code</th>
                    <th style="width: 70%;">Name</th>
                    <th class="text-center" style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th class="text-center" scope="row">001</th>
                    <td>Jack Greene</td>
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
                <tr>
                    <th class="text-center" scope="row">002</th>
                    <td>Andrea Gardner</td>
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
                <tr>
                    <th class="text-center" scope="row">003</th>
                    <td>Brian Cruz</td>
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
</div>
<!-- END Striped Table -->



@endsection