<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Branding & Settings') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-clock-history me-2 fs-4"></i>
                        <div>
                            <strong>Automation Status:</strong> 
                            @if(isset($settings['last_fetch_at']))
                                Last successful alert fetch was at <strong>{{ \Carbon\Carbon::parse($settings['last_fetch_at'])->diffForHumans() }}</strong> ({{ $settings['last_fetch_at'] }}).
                            @else
                                No alerts have been fetched automatically yet.
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <h5 class="fw-bold border-bottom pb-2">Branding</h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="system_name" class="form-label">System Name</label>
                                    <input type="text" name="system_name" id="system_name" class="form-control" value="{{ $settings['system_name'] ?? config('app.name') }}">
                                    <div class="form-text">Used in page titles and emails.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="logo" class="form-label">Logo Upload</label>
                                    <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                                    <div class="form-text">Replaces the default application logo. Recommended: PNG/SVG, height 50px.</div>
                                    
                                    @if(isset($settings['logo_path']))
                                        <div class="mt-2 text-center p-3 bg-light border rounded">
                                            <p class="text-muted small mb-1">Current Logo:</p>
                                            <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Current Logo" style="max-height: 50px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="fw-bold border-bottom pb-2">Example Footer</h5>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="copyright_text" class="form-label">Copyright Text</label>
                                    <input type="text" name="copyright_text" id="copyright_text" class="form-control" value="{{ $settings['copyright_text'] ?? '© ' . date('Y') . ' Alert Dashboard' }}">
                                    <div class="form-text">Displayed at the bottom of every page.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
