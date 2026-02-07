@extends('setup.layout')

@section('content')
<div>
    <div class="step-indicator">
        <div class="step"></div>
        <div class="step active"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
        <div class="step"></div>
    </div>
    
    <h4 class="fw-bold mb-3">System Requirements</h4>
    <p class="text-muted small mb-4">We need to check if your server meets the minimum requirements for the Alert Dashboard.</p>

    <div class="list-group mb-4">
        @php $allMet = true; @endphp
        @foreach($requirements as $label => $met)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ $label }}</span>
                @if($met)
                    <span class="badge bg-success rounded-pill"><i class="bi bi-check"></i> Met</span>
                @else
                    @php $allMet = false; @endphp
                    <span class="badge bg-danger rounded-pill"><i class="bi bi-x"></i> Not Met</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('setup.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> Back
        </a>
        @if($allMet)
            <a href="{{ route('setup.database') }}" class="btn btn-primary">
                Continue <i class="bi bi-chevron-right ms-1"></i>
            </a>
        @else
            <button class="btn btn-primary" disabled title="Fix missing requirements to proceed">
                Continue <i class="bi bi-chevron-right ms-1"></i>
            </button>
        @endif
    </div>
</div>
@endsection
