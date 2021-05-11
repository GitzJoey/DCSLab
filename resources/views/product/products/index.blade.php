@extends('layouts.codebase.backend')

@section('title')
    {{ __('products.title') }}
@endsection

@section('content')

{{ $test }}

<!-- Normal Form -->
<div class="block">
    <div class="block-header block-header-default">
        <h3 class="block-title">Add New Product</h3>
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

            <div class="form-group row">
                <label class="col-12" for="example-select">Group Name</label>
                <div class="col-md-12">
                    <select class="form-control" id="example-select" name="example-select">
                        <option value="0">Please select Group Name</option>
                        <option value="1">Oil</option>
                        <option value="2">Cigarrret</option>
                        <option value="3">Rice</option>
                    </select>             
                </div>
            </div>

            <div class="form-group row">
                <label class="col-12" for="example-select">Brand Name</label>
                <div class="col-md-12">
                    <select class="form-control" id="example-select" name="example-select">
                        <option value="0">Please select Brand Name</option>
                        <option value="1">Sampoerna</option>
                        <option value="2">Rose Brand</option>
                        <option value="3">Indomie</option>
                    </select>             
                </div>
            </div>

            <div class="form-group">
                <label for="example-text-input">Name</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group row">
                <label class="col-12" for="example-select">Unit Name</label>
                <div class="col-md-12">
                    <select class="form-control" id="example-select" name="example-select">
                        <option value="0">Please select Unit Name</option>
                        <option value="1">Pcs</option>
                        <option value="2">Gr</option>
                        <option value="3">Dozen</option>
                    </select>             
                </div>
            </div>

            <div class="form-group">
                <label for="example-text-input">Price</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group row">
                <label class="col-12" for="example-select">Tax</label>
                <div class="col-md-12">
                    <select class="form-control" id="example-select" name="example-select">
                        <option value="0">Please select Tax</option>
                        <option value="1">No Tax</option>
                        <option value="2">Include Tax</option>
                        <option value="3">Exclude Tax</option>
                    </select>             
                </div>
            </div>

            <div class="form-group">
                <label for="example-text-input">Information</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Estimated Capital Price</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Point</label>
                <input type="number" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group row">
                <label class="col-12">Select</label>                            
                <div class="col-12">                            
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox1" id="example-checkbox1" value="option1">
                        <label class="custom-control-label" for="example-checkbox1">Is Use Serial</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox2" id="example-checkbox2" value="option2">
                        <label class="custom-control-label" for="example-checkbox2">is Buy</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox3" id="example-checkbox3" value="option3">
                        <label class="custom-control-label" for="example-checkbox3">Is Production Materials</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox4" id="example-checkbox4" value="option4">
                        <label class="custom-control-label" for="example-checkbox4">Is Production Result</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox5" id="example-checkbox5" value="option5">
                        <label class="custom-control-label" for="example-checkbox5">Is Sell</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-5">
                        <input class="custom-control-input" type="checkbox" name="example-checkbox6" id="example-checkbox6" value="option6">
                        <label class="custom-control-label" for="example-checkbox6">Is Active</label>
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
        <h3 class="block-title">Products List</h3>
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