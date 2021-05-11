@extends('layouts.codebase.backend')

@section('title')
    {{ __('sales_customers.title') }}
@endsection

@section('content')

{{ $test }}

<!-- Normal Form -->
<div class="block">
    <div class="block-header block-header-default">
        <h3 class="block-title">Add New Customer</h3>
        <div class="block-options">
            <button type="button" class="btn-block-option">
                <i class="si si-wrench"></i>
            </button>
        </div>
    </div>
    <div class="block-content">
        <form action="be_forms_elements_bootstrap.html" method="post" onsubmit="return false;">

            <div class="form-group">
                <label for="example-text-input">Code</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Name</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>
           
            <div class="form-group row">
                <label class="col-12" for="example-select">Sales Customer Group</label>
                <div class="col-md-12">
                    <select class="form-control" id="example-select" name="example-select">
                        <option value="0">Sales Customer Group</option>
                        <option value="1">Retail</option>
                        <option value="2">Wholesale Price</option>
                        <option value="3">Distributor</option>
                    </select>             
                </div>
            </div>

            <div class="form-group">
                <label for="example-text-input">Sales Territory</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group row">
                <div class="col-12">
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox1" id="example-checkbox1" value="option1">
                        <label class="custom-control-label" for="example-checkbox1">Use Limit Outstanding Notes</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="example-text-input">Limit Outstanding Notes</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group row">
                <div class="col-12">
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox2" id="example-checkbox2" value="option2">
                        <label class="custom-control-label" for="example-checkbox2">Use Limit Payable Nominal</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="example-text-input">Limit Payable Nominal</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group row">
                <div class="col-12">
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox3" id="example-checkbox3" value="option3">
                        <label class="custom-control-label" for="example-checkbox3">Use Limit Due Date</label>
                    </div>
                </div>
            </div>

             <div class="form-group">
                <label for="example-text-input">Limit Due Date</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Term</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
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
                <label for="example-text-input">Tax ID</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group row">
                <div class="col-12">
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox4" id="example-checkbox4" value="option4">
                        <label class="custom-control-label" for="example-checkbox4">Is Active</label>
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
        <h3 class="block-title">Customer Groups List</h3>
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