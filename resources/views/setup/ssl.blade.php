@extends('setup.layout')

@section('content')
<div>
    <div class="step-indicator">
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step active"></div>
    </div>
    
    <h4 class="fw-bold mb-3">SSL Management (Optional)</h4>
    <p class="text-muted small mb-4">You can generate a self-signed certificate for local testing or upload your own production certificates.</p>

    <div class="card bg-light border-0 mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-2">Option 1: Generate Self-Signed Cert</h6>
            <p class="text-muted x-small mb-3">Best for internal networks or testing. This will create a 365-day certificate for your domain.</p>
            <form action="{{ route('setup.ssl.generate') }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" name="domain" class="form-control form-control-sm" placeholder="e.g. localhost or your-ip" value="localhost">
                    <button type="submit" class="btn btn-secondary btn-sm">Generate</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card bg-light border-0 mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-2">Option 2: Upload Certificate (Manual)</h6>
            <p class="text-muted x-small">If you already have a certificate, copy it to <code>docker/certs/server.crt</code> and <code>docker/certs/server.key</code> manually or use the buttons below.</p>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm w-100" disabled>Upload .crt</button>
                <button class="btn btn-outline-secondary btn-sm w-100" disabled>Upload .key</button>
            </div>
            <p class="text-muted x-small mt-2"><i>Note: Direct upload via browser is disabled for security in this version.</i></p>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('setup.email') }}" class="btn btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> Skip
        </a>
        <a href="{{ route('setup.finish') }}" class="btn btn-primary">
            Finish Setup <i class="bi bi-check-lg ms-1"></i>
        </a>
    </div>
</div>
@endsection
