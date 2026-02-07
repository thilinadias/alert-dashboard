<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Client') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.clients.store') }}" method="POST" class="row g-3">
                        @csrf
                        
                        <div class="col-md-6">
                            <label for="name" class="form-label">Client Name</label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Acme Corp">
                        </div>

                        <div class="col-md-6">
                            <label for="email_domain" class="form-label">Email Domain (Optional)</label>
                            <input type="text" name="email_domain" id="email_domain" class="form-control" placeholder="e.g. acme.com">
                            <div class="form-text">Used for auto-classification by sender email domain.</div>
                        </div>

                        <div class="col-12">
                            <label for="identifier_keywords" class="form-label">Identification Keywords (Optional)</label>
                            <textarea name="identifier_keywords" id="identifier_keywords" class="form-control" rows="2" placeholder="e.g. Acme, ACME-PROD, Project-X"></textarea>
                            <div class="form-text">Comma-separated list. If any of these words appear in the email <strong>Subject</strong> or <strong>Body</strong>, the alert will be automatically assigned to this client.</div>
                        </div>

                        <div class="col-12">
                            <label for="sla_policy_id" class="form-label">SLA Policy</label>
                            <select name="sla_policy_id" id="sla_policy_id" class="form-select">
                                <option value="">-- No Specific Policy (Use System Default) --</option>
                                @foreach($slaPolicies as $policy)
                                    <option value="{{ $policy->id }}">
                                        {{ $policy->name }} ({{ $policy->response_time_minutes }}m / {{ $policy->resolution_time_minutes }}m)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 text-end">
                            <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Client</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
