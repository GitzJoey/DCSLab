@extends('layouts.codebase.backend')

@section('content')

{{ $test }}

<!-- Normal Form -->
<div class="block">
    <div class="block-header block-header-default">
        <h3 class="block-title">Product Brand Add</h3>
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
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
            </div>

            <div class="form-group">
                <label for="example-text-input">Name</label>
                <input type="text" class="form-control" id="example-text-input" name="example-text-input" placeholder="">
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
        <h3 class="block-title">Product Groups List</h3>
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