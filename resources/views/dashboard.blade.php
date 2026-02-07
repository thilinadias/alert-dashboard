<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div>
        <div class="container-fluid">
            
            <!-- Welcome Banner -->
            <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(90deg, #4f46e5 0%, #3b82f6 100%); color: white;">
                <div class="card-body p-4 d-md-flex justify-content-between align-items-center text-center text-md-start">
                    <div>
                        <h1 class="h3 fw-bold mb-1">Welcome back, {{ Auth::user()->name }}!</h1>
                        <p class="mb-0 text-white-50">Here's what's happening in your queue today.</p>
                    </div>
                    <div class="mt-3 mt-md-0 text-md-end d-flex align-items-center gap-4">
                        @hasanyrole('admin|manager')
                            <div class="d-flex flex-column align-items-end">
                                <div class="form-check form-switch m-0 text-start" title="Auto-refresh dashboards">
                                    <input class="form-check-input" type="checkbox" id="livePollingToggle">
                                    <label class="form-check-label small fw-bold text-white-50" for="livePollingToggle">Live Sync</label>
                                </div>
                                <div id="pollingCountdown" class="text-white x-small fw-bold" style="font-size: 0.6rem; display: none;">Sync in: <span id="syncSeconds">10</span>s</div>
                            </div>
                            <select id="pollingInterval" class="form-select form-select-sm bg-transparent text-white border-white-50" style="width: auto; font-size: 0.7rem; height: 28px; padding: 0 0.5rem 0 0.25rem;">
                                <option value="10" class="text-dark">10s</option>
                                <option value="30" class="text-dark">30s</option>
                                <option value="60" class="text-dark">1m</option>
                                <option value="120" class="text-dark">2m</option>
                                <option value="300" class="text-dark">5m</option>
                            </select>
                        @else
                            <div id="pollingCountdown" class="text-white x-small fw-bold align-self-center border border-white-50 rounded px-2 py-1" style="font-size: 0.7rem;">
                                Auto-sync in: <span id="syncSeconds">10</span>s
                            </div>
                            <input type="hidden" id="forceLivePolling" value="1">
                        @endhasanyrole
                        <div class="vr h-100 bg-white-50"></div>
                        <div>
                            <div class="h4 font-monospace mb-0" id="current-time">--:--</div>
                            <div class="small text-white-50">{{ now()->format('l, F j, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Assigned -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('alerts.mine') }}" class="text-decoration-none">
                        <div class="card shadow-sm border-0 border-start border-4 border-primary h-100 transition-transform hover-scale">
                            <div class="card-body d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                                    <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                </div>
                                <div>
                                    <div class="text-muted small fw-medium">Assigned to Me</div>
                                    <div class="h3 fw-bold mb-0 text-dark">{{ $assignedToMe }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Resolved -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('alerts.index', ['status' => 'closed', 'date' => 'today']) }}" class="text-decoration-none">
                        <div class="card shadow-sm border-0 border-start border-4 border-success h-100 transition-transform hover-scale">
                            <div class="card-body d-flex align-items-center">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                                    <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <div class="text-muted small fw-medium">Resolved Today</div>
                                    <div class="h3 fw-bold mb-0 text-dark">{{ $resolvedToday }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- SLA Breaches -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('alerts.index', ['sla' => 'overdue']) }}" class="text-decoration-none">
                        <div class="card shadow-sm border-0 border-start border-4 border-danger h-100 transition-transform hover-scale">
                            <div class="card-body d-flex align-items-center">
                                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger">
                                    <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <div class="text-muted small fw-medium">SLA Breaches</div>
                                    <div class="h3 fw-bold mb-0 text-dark">{{ $slaBreaches }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Avg Time -->
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-info h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info">
                                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">Avg Resolution</div>
                                <div class="h3 fw-bold mb-0 text-dark">{{ $avgResolutionTime }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .hover-scale { transition: transform 0.2s; cursor: pointer; }
                .hover-scale:hover { transform: translateY(-5px); }
            </style>

            <!-- Charts & Lists Row -->
            <div class="row g-4 mb-4">
                <!-- Charts Section -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title fw-bold mb-0">System Overview</h5>
                                <span class="badge bg-light text-secondary border">Live Data</span>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <h6 class="text-center small fw-bold text-muted mb-2">My Weekly Resolutions</h6>
                                    <div style="height: 200px;">
                                        <canvas id="weeklyChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <h6 class="text-center small fw-bold text-muted mb-2">Open Alerts Severity</h6>
                                    <div style="height: 200px;">
                                        <canvas id="severityChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Urgent Alerts List -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title fw-bold mb-0">My Urgent Alerts</h5>
                                <a href="{{ route('alerts.mine') }}" class="text-decoration-none small">View All &rarr;</a>
                            </div>
                            
                            @if($urgentAlerts->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($urgentAlerts as $alert)
                                        <a href="{{ route('alerts.mine') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 py-2 border-bottom-0">
                                            <div class="d-flex align-items-center text-truncate">
                                                <span class="d-inline-block rounded-circle me-3 {{ $alert->severity == 'critical' ? 'bg-danger' : ($alert->severity == 'warning' ? 'bg-warning' : 'bg-info') }}" style="width: 8px; height: 8px;"></span>
                                                <div class="text-truncate" style="max-width: 250px;">
                                                    <div class="fw-medium text-dark text-truncate">{{ $alert->subject }}</div>
                                                    <div class="small text-muted">{{ $alert->timeUntilSla() }} left</div>
                                                </div>
                                            </div>
                                            <span class="badge rounded-pill bg-light text-dark border">{{ $alert->severity }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <p class="mb-1">No open alerts assigned to you.</p>
                                    <small>Great job keeping the queue clean!</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- TTA Leaderboard & Recent Activity -->
            <div class="row g-4 mb-4">
                <!-- Response Time Leaderboard -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Avg. Time to Pickup (Last 7 Days)</h5>
                            
                            @if($ttaLeaderboard->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($ttaLeaderboard as $stat)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom-0">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary fw-bold d-flex justify-content-center align-items-center me-3" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ substr($stat['name'], 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-medium text-dark">{{ $stat['name'] }}</div>
                                                    <div class="small text-muted">{{ $stat['count'] }} alerts picked</div>
                                                </div>
                                            </div>
                                            <span class="badge rounded-pill {{ $stat['avg_tta'] > 60 ? 'bg-danger' : ($stat['avg_tta'] > 15 ? 'bg-warning text-dark' : 'bg-success') }}">
                                                {{ $stat['avg_tta'] }} mins
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <p class="mb-0">No pickup data available yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                             <h5 class="card-title fw-bold mb-4">Recent Activity</h5>
                             <div class="position-relative ps-4 border-start">
                                 @forelse($recentActivity as $history)
                                     <div class="mb-4 position-relative">
                                         <span class="position-absolute top-0 start-0 translate-middle p-1 bg-primary border border-white rounded-circle" style="left: -1.5rem !important;"></span>
                                         <h6 class="mb-1 fw-bold text-dark">
                                             {{ ucfirst($history->action) }} 
                                             <span class="fw-normal text-muted ms-1">on alert #{{ $history->alert_id }}</span>
                                         </h6>
                                         <p class="mb-0 small text-muted">{{ $history->created_at->diffForHumans() }}</p>
                                     </div>
                                 @empty
                                     <div class="text-muted">No recent activity found.</div>
                                 @endforelse
                             </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Additional Styles for Timeline alignment since Bootstrap doesn't have -left-3 utility -->
    <style>
        .translate-middle { transform: translate(-50%, -50%) !important; }
    </style>

    <script>
        // Clock
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Weekly Chart
            const ctxWeekly = document.getElementById('weeklyChart').getContext('2d');
            new Chart(ctxWeekly, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($barChartData->keys()) !!},
                    datasets: [{
                        label: 'Resolved Alerts',
                        data: {!! json_encode($barChartData->values()) !!},
                        backgroundColor: '#0d6efd',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { display: false } 
                    }
                }
            });

            // Severity Chart
            const ctxSeverity = document.getElementById('severityChart').getContext('2d');
            new Chart(ctxSeverity, {
                type: 'doughnut',
                data: {
                    labels: ['Critical', 'Warning', 'Info', 'Default'],
                    datasets: [{
                        data: [
                            {{ $pieChartData['critical'] }},
                            {{ $pieChartData['warning'] }},
                            {{ $pieChartData['info'] }},
                            {{ $pieChartData['default'] }}
                        ],
                        backgroundColor: ['#dc3545', '#fd7e14', '#0d6efd', '#6c757d'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }
                }
            });
        });
    </script>
</x-app-layout>
