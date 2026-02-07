<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New SLA Policy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.sla-policies.store') }}" method="POST" class="row g-3">
                        @csrf
                        
                        <div class="col-12">
                            <label for="name" class="form-label">Policy Name</label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Gold Tier">
                        </div>

                        <div class="col-md-6">
                            <label for="response_time_minutes" class="form-label">Response Time Objective (Minutes)</label>
                            <input type="number" name="response_time_minutes" id="response_time_minutes" class="form-control" required min="1" value="60">
                            <div class="form-text">Time to Acknowledge (TTA) target.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="resolution_time_minutes" class="form-label">Resolution Time Objective (Minutes)</label>
                            <input type="number" name="resolution_time_minutes" id="resolution_time_minutes" class="form-control" required min="1" value="240">
                            <div class="form-text">Time to Resolve (TTR) target.</div>
                        </div>

                        <div class="col-12 text-end">
                            <a href="{{ route('admin.sla-policies.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Policy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
