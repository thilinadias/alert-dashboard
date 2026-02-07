@extends('setup.layout')

@section('content')
<div>
    <div class="step-indicator">
        <div class="step"></div>
        <div class="step"></div>
        <div class="step active"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
    </div>
    
    <h4 class="fw-bold mb-3">Database Configuration</h4>
    <p class="text-muted small mb-4">Enter your MySQL database credentials below. The application will attempt to connect to the database to verify settings.</p>

    <form action="{{ route('setup.database.post') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-bold">Host</label>
            <input type="text" name="db_host" class="form-control" value="{{ env('DB_HOST', '127.0.0.1') }}" required placeholder="127.0.0.1 or db">
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Database Name</label>
                    <input type="text" name="db_database" class="form-control" value="{{ env('DB_DATABASE', 'alert_dashboard') }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Port</label>
                    <input type="text" name="db_port" class="form-control" value="{{ env('DB_PORT', '3306') }}" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Username</label>
                    <input type="text" name="db_username" class="form-control" value="{{ env('DB_USERNAME', 'root') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Password</label>
                    <input type="password" name="db_password" class="form-control" placeholder="Optional">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('setup.requirements') }}" class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i> Back
            </a>
            <button type="submit" class="btn btn-primary">
                Test & Save <i class="bi bi-chevron-right ms-1"></i>
            </button>
        </div>
    </form>
</div>
@endsection
