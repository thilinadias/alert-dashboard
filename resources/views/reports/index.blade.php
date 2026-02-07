<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Operational Reports') }}
        </h2>
    </x-slot>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Download Report Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6 text-gray-900">
                    <h5 class="fw-bold mb-3">Download Custom Reports</h5>
                    <form action="{{ route('admin.reports.download') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select name="report_type" id="report_type" class="form-select" required>
                                <option value="resolved_alerts">Total Resolved Alerts</option>
                                <option value="sla_breached">SLA Breached Alerts</option>
                                <option value="open_alerts">Open Alerts Snapshot</option>
                                <option value="pickup_time">Avg. Time to Pickup</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">From Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">To Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="row g-4 mb-4">
                
                <!-- Volume History -->
                <div class="col-lg-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-100">
                        <div class="p-6 text-gray-900 h-100 d-flex flex-column">
                            <h5 class="fw-bold mb-4">Alert Volume (Last 30 Days)</h5>
                            <div class="flex-grow-1" style="min-height: 300px;">
                                <canvas id="volumeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLA Compliance -->
                <div class="col-lg-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-100">
                        <div class="p-6 text-gray-900 h-100 d-flex flex-column">
                            <h5 class="fw-bold mb-4">SLA Compliance (Closed Alerts)</h5>
                            <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="min-height: 300px;">
                                <canvas id="slaChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Sources -->
                <div class="col-12">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h5 class="fw-bold mb-4">Top Alert Sources (Clients)</h5>
                            <div style="min-height: 300px;">
                                <canvas id="sourcesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Volume Chart
            const ctxVolume = document.getElementById('volumeChart').getContext('2d');
            new Chart(ctxVolume, {
                type: 'line',
                data: {
                    labels: {!! json_encode($lineChartData->keys()) !!},
                    datasets: [{
                        label: 'Alerts Created',
                        data: {!! json_encode($lineChartData->values()) !!},
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // SLA Chart
            const ctxSla = document.getElementById('slaChart').getContext('2d');
            new Chart(ctxSla, {
                type: 'doughnut',
                data: {
                    labels: ['Met', 'Missed'],
                    datasets: [{
                        data: [
                            {{ $pieChartData['Met'] }},
                            {{ $pieChartData['Missed'] }}
                        ],
                        backgroundColor: ['#198754', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            // Sources Chart
            const ctxSources = document.getElementById('sourcesChart').getContext('2d');
            new Chart(ctxSources, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topSources->pluck('name')) !!},
                    datasets: [{
                        label: 'Alert Count',
                        data: {!! json_encode($topSources->pluck('count')) !!},
                        backgroundColor: '#0dcaf0',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y', // Horizontal bar
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
</x-app-layout>
