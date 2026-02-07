<div class="row">
    <div class="col-md-8">
        <h5 class="mb-3">Description</h5>
        <div class="bg-white p-3 border rounded mb-3 text-break">
            {!! nl2br(e($alert->description)) !!}
        </div>

        @if($alert->resolution_notes)
            <h5 class="mb-3">Resolution Notes</h5>
            <div class="bg-success-subtle p-3 border border-success rounded mb-3 text-break">
                {!! nl2br(e($alert->resolution_notes)) !!}
                @if($alert->closed_at)
                    <div class="small text-muted mt-2 border-top pt-2">
                        Closed by {{ $alert->closedBy->name ?? 'Unknown' }} on {{ $alert->closed_at->format('M d H:i') }}
                    </div>
                @endif
            </div>
        @endif
        
        <h6 class="mb-2">Alert History</h6>
        <ul class="list-group list-group-flush small">
            @foreach($alert->alertHistories()->latest()->get() as $history)
                <li class="list-group-item bg-transparent px-0">
                    <span class="text-muted">{{ $history->created_at->format('M d H:i') }}</span> - 
                    <strong>{{ ucfirst($history->action) }}</strong>: 
                    {{ $history->details }}
                    @if($history->user)
                        <span class="text-muted">by {{ $history->user->name }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                Alert Details
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>Ticket #:</span>
                    <strong>{{ $alert->ticket_number }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Device:</span>
                    <strong>{{ $alert->device ?? 'N/A' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Created:</span>
                    <span>{{ $alert->created_at->format('Y-m-d H:i:s') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>SLA Deadline:</span>
                    <span class="{{ $alert->isOverdue() ? 'text-danger fw-bold' : '' }}">
                        {{ $alert->getSlaDeadline()->format('Y-m-d H:i:s') }}
                    </span>
                </li>
            </ul>
            <div class="card-body">
                @if($alert->status == 'closed')
                    <div class="alert alert-secondary text-center">
                        This alert is closed.
                    </div>
                    <button class="btn btn-outline-secondary w-100 btn-reopen-alert" data-alert-id="{{ $alert->id }}">Reopen Alert</button>
                @elseif($alert->status == 'resolved')
                    <div class="alert alert-success text-center">
                        Resolved. Waiting to Close.
                    </div>
                    @if($alert->locked_by == Auth::id() || Auth::user()->hasRole('admin'))
                        <button class="btn btn-success w-100 mb-2 btn-close-alert" data-alert-id="{{ $alert->id }}">Close Alert</button>
                    @endif
                    <button class="btn btn-outline-warning w-100 btn-reopen-alert" data-alert-id="{{ $alert->id }}">Reopen Alert</button>
                @elseif(!$alert->locked_by)
                    <button class="btn btn-primary w-100 mb-2 btn-take-alert" data-alert-id="{{ $alert->id }}">Take Alert</button>
                @elseif($alert->locked_by == Auth::id())
                    <div id="action-buttons-{{ $alert->id }}">
                        <button class="btn btn-success w-100 mb-2" onclick="document.getElementById('resolution-form-{{ $alert->id }}').classList.remove('d-none'); document.getElementById('action-buttons-{{ $alert->id }}').classList.add('d-none');">Resolve Alert</button>
                        <button class="btn btn-outline-danger w-100 mb-2 btn-release-alert" data-alert-id="{{ $alert->id }}">Release Alert</button>
                    </div>

                    <!-- Resolution Form -->
                    <div id="resolution-form-{{ $alert->id }}" class="d-none">
                        <div class="mb-2">
                            <label class="form-label small">Ticket # (Optional)</label>
                            <input type="text" class="form-control form-control-sm" id="ticket-number-{{ $alert->id }}" value="{{ $alert->ticket_number }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Resolution Notes <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-sm" id="resolution-notes-{{ $alert->id }}" rows="3" placeholder="Describe the fix..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm w-100 btn-resolve-submit" data-alert-id="{{ $alert->id }}">Submit</button>
                            <button class="btn btn-outline-secondary btn-sm w-100" onclick="document.getElementById('resolution-form-{{ $alert->id }}').classList.add('d-none'); document.getElementById('action-buttons-{{ $alert->id }}').classList.remove('d-none');">Cancel</button>
                        </div>
                    </div>
                @else
                    <div class="alert alert-secondary text-center small">
                        Locked by {{ $alert->lockedBy->name }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
