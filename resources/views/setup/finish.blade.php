@extends('setup.layout')

@section('content')
<div class="text-center">
    <div class="mb-4">
        <i class="bi bi-check-circle-fill text-success display-1"></i>
    </div>
    <h2 class="fw-bold mb-3">Setup Complete!</h2>
    <p class="text-muted mb-4">
        Congratulations! The Alert Dashboard has been successfully installed and configured. 
        You can now log in with your administrator credentials.
    </p>

    <div class="alert alert-warning small text-start mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Important:</strong> For security, ensure the <code>APP_INSTALLED</code> variable is set to <code>true</code> in your <code>.env</code> file. The installer has attempted to do this for you.
    </div>

    <div class="d-grid">
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
            Login to Dashboard <i class="bi bi-box-arrow-in-right ms-2"></i>
        </a>
    </div>
    
    <p class="small text-muted mt-4">
        Don't forget to configure your <strong>Google/Microsoft OAuth</strong> in the settings panel to start receiving real alerts!
    </p>
</div>
@endsection
