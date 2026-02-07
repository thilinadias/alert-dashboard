<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-gray-100">
    <div class="container-fluid px-4">
        <!-- Logo -->
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            @if(\App\Models\Setting::get('logo_path'))
                <img src="{{ asset('storage/' . \App\Models\Setting::get('logo_path')) }}" alt="Logo" class="d-inline-block align-text-top" style="height: 36px;">
            @else
                <x-application-logo class="d-inline-block align-text-top" style="height: 36px;" />
            @endif
        </a>

        <!-- Hamburger -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-nav-link>
                <x-nav-link :href="route('alerts.critical')" :active="request()->routeIs('alerts.critical')">
                    {{ __('Critical Alerts') }}
                </x-nav-link>
                <x-nav-link :href="route('alerts.default')" :active="request()->routeIs('alerts.default')">
                    {{ __('Default Alerts') }}
                </x-nav-link>
                <x-nav-link :href="route('alerts.index')" :active="request()->routeIs('alerts.index')">
                    {{ __('All Alerts') }}
                </x-nav-link>
                <div class="border-start ms-2 ps-2"></div>
                <x-nav-link :href="route('alerts.mine')" :active="request()->routeIs('alerts.mine')">
                    {{ __('My Alerts') }}
                </x-nav-link>
                @if(auth()->user()->can('view_reports') || auth()->user()->can('manage_users') || auth()->user()->can('manage_settings'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __('Administration') }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                            @can('view_reports')
                                <li><a class="dropdown-item" href="{{ route('admin.reports.index') }}">{{ __('Reports') }}</a></li>
                            @endcan
                            
                            @can('manage_users')
                                <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">{{ __('Users') }}</a></li>
                            @endcan

                            @can('manage_settings')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}">{{ __('Settings') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.sla-policies.index') }}">{{ __('SLA Policies') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.clients.index') }}">{{ __('Clients') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.classification-rules.index') }}">{{ __('Classification Rules') }}</a></li>
                            @endcan
                        </ul>
                    </li>
                @endif
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <!-- Settings Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <!-- Authentication -->
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
