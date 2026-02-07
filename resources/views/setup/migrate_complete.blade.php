@extends('setup.layout')

@section('content')
<div class="text-center">
    <div class="step-indicator">
        <div class="step"></div>
        <div class="step"></div>
        <div class="step active"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
    </div>
    
    <div class="mb-4">
        <i class="bi bi-database-check text-success display-1"></i>
    </div>
    <h4 class="fw-bold mb-3">Database Ready!</h4>
    <p class="text-muted small mb-4">The database tables have been successfully created and seeded with default data.</p>

    <div class="d-grid">
        <a href="{{ route('setup.admin') }}" class="btn btn-primary">
            Create Admin Account <i class="bi bi-chevron-right ms-1"></i>
        </a>
    </div>
</div>
@endsection
