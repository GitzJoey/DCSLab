@if (!\Illuminate\Support\Facades\Auth::user()->hasVerifiedEmail())
    <div class="animate__animated animate__bounceInDown">
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fa fa-fw fa-exclamation-triangle mr-10"></i>
            <p class="mb-0">
                Email is not verified, click the <a class="alert-link" href="{{ route('db.user.verify_email') }}">link</a> to verify!
            </p>
        </div>
    </div>
@endif

@if (session('flash-messages'))
    <div class="animate__animated animate__bounceInDown">
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fa fa-fw fa-exclamation-triangle mr-10"></i>
            <p class="mb-0">
                {{ session('flash-messages') }}
            </p>
        </div>
    </div>
@endif
