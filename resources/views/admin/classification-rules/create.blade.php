<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Classification Rule') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h5 mb-0">New Rule Details</h3>
                        <a href="{{ route('admin.classification-rules.index') }}" class="btn btn-outline-secondary btn-sm">
                            &larr; Back to List
                        </a>
                    </div>

                    <form action="{{ route('admin.classification-rules.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="keyword" class="form-label">Keyword <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('keyword') is-invalid @enderror" id="keyword" name="keyword" value="{{ old('keyword') }}" required placeholder="e.g., 'Server Down', 'Disk Space', 'alert@vendor.com'">
                            @error('keyword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">The text to search for in the email.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rule_type" class="form-label">Match In <span class="text-danger">*</span></label>
                                <select class="form-select @error('rule_type') is-invalid @enderror" id="rule_type" name="rule_type" required>
                                    <option value="subject" {{ old('rule_type') == 'subject' ? 'selected' : '' }}>Subject Line</option>
                                    <option value="body" {{ old('rule_type') == 'body' ? 'selected' : '' }}>Email Body</option>
                                    <option value="sender" {{ old('rule_type') == 'sender' ? 'selected' : '' }}>Sender Email Address</option>
                                </select>
                                @error('rule_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority" value="{{ old('priority', 10) }}" required min="1">
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Lower numbers run first.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_severity" class="form-label">Assign Severity <span class="text-danger">*</span></label>
                                <select class="form-select @error('target_severity') is-invalid @enderror" id="target_severity" name="target_severity" required>
                                    <option value="critical" {{ old('target_severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                                    <option value="warning" {{ old('target_severity') == 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="info" {{ old('target_severity') == 'info' ? 'selected' : '' }}>Info</option>
                                    <option value="default" {{ old('target_severity') == 'default' ? 'selected' : '' }}>Default</option>
                                </select>
                                @error('target_severity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="target_client_id" class="form-label">Assign Client (Optional)</label>
                                <select class="form-select @error('target_client_id') is-invalid @enderror" id="target_client_id" name="target_client_id">
                                    <option value="">-- No Client Assignment --</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('target_client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('target_client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('admin.classification-rules.index') }}" class="btn btn-light me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Rule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
