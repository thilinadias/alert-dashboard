@extends('setup.layout')

@section('content')
<div>
    <div class="step-indicator">
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step active"></div>
        <div class="step"></div>
        <div class="step"></div>
    </div>
    
    <h4 class="fw-bold mb-3">Create Admin Account</h4>
    <p class="text-muted small mb-4">Set up the initial administrator account. You will use these credentials to log in to the dashboard.</p>

    <form action="{{ route('setup.admin.post') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-bold">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="John Doe" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="admin@example.com" required>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary">
                Create Account <i class="bi bi-chevron-right ms-1"></i>
            </button>
        </div>
    </form>
</div>
@endsection
