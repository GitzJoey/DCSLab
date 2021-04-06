@extends('layouts.codebase.backend')

@section('title')
    {{ __('profile.title') }}
@endsection

@section('css_before')
@endsection

@section('css_after')
@endsection

@section('content')
<div class="bg-image bg-image-bottom" style="background-image: url('{{ asset('images/bg6.jpg') }}');">
    <div class="bg-black-op-75 py-30">
        <div class="content content-full text-center">
            <div class="mb-15">
                <a class="img-link" href="#">
                    <img class="img-avatar img-avatar96 img-avatar-thumb" src="{{ asset('images/def-user.png') }}" alt="">
                </a>
            </div>

            <h1 class="h3 text-white font-w700 mb-10">John Smith</h1>
            <h2 class="h5 text-white-op">
                Product Manager <a class="text-primary-light" href="javascript:void(0)">@GraphicXspace</a>
            </h2>

            <!-- Actions -->
            <a href="be_pages_generic_profile.html" class="btn btn-rounded btn-hero btn-sm btn-alt-secondary mb-5">
                <i class="fa fa-arrow-left mr-5"></i> Back to Profile
            </a>
            <!-- END Actions -->
        </div>
    </div>
</div>

<div class="block">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <i class="fa fa-user-circle mr-5 text-muted"></i> User Profile
        </h3>
    </div>
    <div class="block-content">
        <form action="be_pages_generic_profile.edit.html" method="POST" enctype="multipart/form-data" onsubmit="return false;">
            <div class="row items-push">
                <div class="col-lg-3">
                    <p class="text-muted">
                        Your account’s vital info. Your username will be publicly visible.
                    </p>
                </div>
                <div class="col-lg-7 offset-lg-1">
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="profile-settings-username">Username</label>
                            <input type="text" class="form-control form-control-lg" id="profile-settings-username" name="profile-settings-username" placeholder="Enter your username.." value="john.doe">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="profile-settings-name">Name</label>
                            <input type="text" class="form-control form-control-lg" id="profile-settings-name" name="profile-settings-name" placeholder="Enter your name.." value="John Doe">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="profile-settings-email">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="profile-settings-email" name="profile-settings-email" placeholder="Enter your email.." value="john.doe@example.com">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-10 col-xl-6">
                            <div class="push">
                                <img class="img-avatar" src="assets/media/avatars/avatar15.jpg" alt="">
                            </div>
                            <div class="custom-file">
                                <!-- Populating custom file input label with the selected filename (data-toggle="custom-file-input" is initialized in Helpers.coreBootstrapCustomFileInput()) -->
                                <input type="file" class="custom-file-input" id="profile-settings-avatar" name="profile-settings-avatar" data-toggle="custom-file-input">
                                <label class="custom-file-label" for="profile-settings-avatar">Choose new avatar</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-alt-primary">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('custom_content')
@endsection

@section('js_before')
@endsection

@section('js_after')
@endsection
