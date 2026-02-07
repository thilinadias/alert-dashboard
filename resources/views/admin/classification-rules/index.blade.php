<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Classification Rules') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h5 mb-0">Manage Rules</h3>
                        <a href="{{ route('admin.classification-rules.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add New Rule
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" width="60">Priority</th>
                                    <th scope="col">Keyword</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Target Severity</th>
                                    <th scope="col">Target Client</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-rules">
                                @forelse ($rules as $rule)
                                    <tr data-id="{{ $rule->id }}">
                                        <td class="text-center fw-bold text-muted">{{ $rule->priority }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $rule->keyword }}</span></td>
                                        <td>
                                            @if($rule->rule_type == 'subject')
                                                <span class="badge bg-info">Subject</span>
                                            @elseif($rule->rule_type == 'body')
                                                <span class="badge bg-secondary">Body</span>
                                            @elseif($rule->rule_type == 'sender')
                                                <span class="badge bg-warning text-dark">Sender</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rule->target_severity == 'critical')
                                                <span class="badge bg-danger">Critical</span>
                                            @elseif($rule->target_severity == 'warning')
                                                <span class="badge bg-warning text-dark">Warning</span>
                                            @elseif($rule->target_severity == 'info')
                                                <span class="badge bg-info">Info</span>
                                            @else
                                                <span class="badge bg-secondary">Default</span>
                                            @endif
                                        </td>
                                        <td>{{ $rule->targetClient ? $rule->targetClient->name : '-' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.classification-rules.edit', $rule) }}" class="btn btn-sm btn-outline-primary me-1">
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.classification-rules.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No classification rules found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
