<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $dashboardTitle ?? __('Alert Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-4 border">
                <div class="p-4 text-gray-900">
                    <form method="GET" action="{{ route('alerts.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Subject, description..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Severity</label>
                            <select name="severity" class="form-select">
                                <option value="">All Severities</option>
                                <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                                <option value="warning" {{ request('severity') == 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="info" {{ request('severity') == 'info' ? 'selected' : '' }}>Info</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-3 justify-content-end align-items-center">
                                @hasanyrole('admin|manager')
                                    <div class="form-check form-switch m-0 d-flex flex-column align-items-end" title="Auto-refresh dashboards">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-check-input" type="checkbox" id="livePollingToggle">
                                            <label class="form-check-label small fw-bold text-muted" for="livePollingToggle">Live Polling</label>
                                        </div>
                                        <div id="pollingCountdown" class="text-success x-small fw-bold" style="font-size: 0.65rem; margin-top: -2px; display: none;">Next sync: <span id="syncSeconds">10</span>s</div>
                                    </div>
                                    <select id="pollingInterval" class="form-select form-select-sm" style="width: auto; font-size: 0.7rem; height: 28px; padding: 0 0.5rem;">
                                        <option value="10">10s</option>
                                        <option value="30">30s</option>
                                        <option value="60">1m</option>
                                        <option value="120">2m</option>
                                        <option value="300">5m</option>
                                    </select>
                                @else
                                    <div id="pollingCountdown" class="text-success x-small fw-bold align-self-center me-2" style="font-size: 0.75rem;">
                                        Auto-syncing in: <span id="syncSeconds">10</span>s
                                    </div>
                                    <input type="hidden" id="forceLivePolling" value="1">
                                @endhasanyrole
                                <div class="vr h-25 my-auto"></div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary px-3 btn-sm">
                                        <i class="bi bi-funnel"></i> Filter
                                    </button>
                                    <a href="{{ route('alerts.export', request()->all()) }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2" title="Export current results to CSV">
                                        <i class="bi bi-download"></i> Export
                                    </a>
                                    <form action="{{ route('alerts.sync') }}" method="POST" id="manualSyncForm" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm d-flex align-items-center gap-2" title="Sync alerts from email now">
                                            <i class="bi bi-arrow-repeat"></i> Sync Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Alerts Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="select-all-alerts">
                                    </th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Severity</th>
                                    <th scope="col">Subject</th>
                                    <th scope="col">Client</th>
                                    <th scope="col">Owner</th>
                                    <th scope="col">Sla Time</th>
                                    <th scope="col">Created</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alerts as $alert)
                                    @php
                                        $slaStatus = $alert->getSlaStatus();
                                        $rowClass = 'alert-row-default';
                                        if ($slaStatus === 'critical') $rowClass = 'alert-row-overdue';
                                        elseif ($slaStatus === 'warning') $rowClass = 'alert-row-warning';
                                        elseif ($alert->severity === 'critical') $rowClass = 'alert-row-critical';
                                    @endphp
                                    <tr class="alert-row {{ $rowClass }} cursor-pointer" data-alert-id="{{ $alert->id }}">
                                        <td class="text-center" onclick="event.stopPropagation();">
                                            @if($alert->status !== 'closed')
                                                <input type="checkbox" class="form-check-input alert-checkbox" value="{{ $alert->id }}">
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $alert->status == 'new' ? 'primary' : ($alert->status == 'open' ? 'warning text-dark' : 'success') }}">
                                                {{ ucfirst($alert->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $alert->severity == 'critical' ? 'danger' : ($alert->severity == 'warning' ? 'warning text-dark' : 'info') }}">
                                                {{ ucfirst($alert->severity) }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($alert->subject, 50) }}</td>
                                        <td>{{ $alert->client ? $alert->client->name : 'Unknown' }}</td>
                                        <td>
                                            @if($alert->lockedBy)
                                                <span class="badge bg-secondary" title="{{ $alert->lockedBy->name }}">
                                                    {{ Str::limit($alert->lockedBy->name, 10) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($alert->status != 'closed' && $alert->status != 'resolved')
                                                <small class="{{ $slaStatus == 'critical' ? 'text-danger fw-bold' : '' }}">
                                                    {{ $alert->timeUntilSla() }}
                                                    {{ $alert->isOverdue() ? 'overdue' : 'left' }}
                                                </small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td><small class="text-muted">{{ $alert->created_at->format('M d H:i') }}</small></td>
                                        <td class="alert-actions">
                                            @if(!$alert->locked_by)
                                                <button class="btn btn-sm btn-outline-primary btn-take-alert" data-alert-id="{{ $alert->id }}">Take</button>
                                            @elseif($alert->locked_by == Auth::id())
                                                <span class="badge bg-success">Yours</span>
                                            @else
                                                <i class="bi bi-lock-fill text-muted"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No alerts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $alerts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Action Bar -->
    <div id="bulk-action-bar" class="fixed-bottom bg-white border-top shadow-lg p-3 d-none" style="z-index: 1050;">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold" id="selected-count">0</span> alerts selected
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkResolveModal">
                    Bulk Resolve & Close
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Details Modal -->
    <div class="modal fade" id="alertDetailsModal" tabindex="-1" aria-labelledby="alertDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertDetailsModalLabel">Alert Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="alertDetailsContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Resolve Modal -->
    <div class="modal fade" id="bulkResolveModal" tabindex="-1" aria-labelledby="bulkResolveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkResolveModalLabel">Bulk Resolve Alerts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to resolve and close <span id="bulk-resolve-count" class="fw-bold"></span> alerts.</p>
                    <div class="mb-3">
                        <label for="bulk-resolution-notes" class="form-label">Resolution Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="bulk-resolution-notes" rows="3" placeholder="Describe how these alerts were resolved..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="bulk-ticket-number" class="form-label">Ticket # (Optional)</label>
                        <input type="text" class="form-control" id="bulk-ticket-number" placeholder="Enter ticket number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-bulk-resolve-submit">Resolve & Close</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
