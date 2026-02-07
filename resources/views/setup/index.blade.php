@extends('setup.layout')

@section('content')
<div class="text-center">
    <div class="step-indicator">
        <div class="step active"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
    </div>
    
    <h2 class="fw-bold mb-3">Welcome to Setup</h2>
    <p class="text-muted mb-4">
        Thank you for choosing Alert Dashboard. This wizard will help you configure your system in just a few minutes. 
        Before we begin, please ensure you have your database credentials and email settings ready.
    </p>

    <div class="d-grid">
        <a href="{{ route('setup.requirements') }}" class="btn btn-primary btn-lg">
            Get Started <i class="bi bi-chevron-right ms-2"></i>
        </a>
    </div>
    
    <p class="small text-muted mt-4">
        <i class="bi bi-info-circle me-1"></i> 
        Need help? Check the <a href="https://github.com" target="_blank">documentation</a>.
    </p>
</div>
@endsection
