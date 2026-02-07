@extends('setup.layout')

@section('content')
<div>
    <div class="step-indicator">
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step active"></div>
        <div class="step"></div>
    </div>
    
    <h4 class="fw-bold mb-3">Email Configuration</h4>
    <p class="text-muted small mb-4">Configure your SMTP settings to receive notifications. You can also configure OAuth settings later in the settings panel.</p>

    <form action="{{ route('setup.email.post') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-bold">SMTP Host</label>
            <input type="text" name="host" class="form-control" placeholder="smtp.mailtrap.io">
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label small fw-bold">SMTP Username</label>
                    <input type="text" name="username" class="form-control">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Port</label>
                    <input type="text" name="port" class="form-control" placeholder="587">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small fw-bold">SMTP Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Encryption</label>
                    <select name="encryption" class="form-select">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="">None</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">From Address</label>
            <input type="email" name="from_address" class="form-control" placeholder="noreply@example.com">
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('setup.admin') }}" class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i> Back
            </a>
            <button type="submit" class="btn btn-primary">
                Save & Continue <i class="bi bi-chevron-right ms-1"></i>
            </button>
        </div>
    </form>
</div>
@endsection
