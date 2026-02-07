<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Setting::get('system_name', config('app.name', 'Laravel')) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.scss', 'resources/js/app.js'])

        <style>
            .login-container {
                min-height: 100vh;
                display: flex;
            }
            .login-left {
                flex: 1;
                background: #fff;
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 4rem;
                position: relative;
                z-index: 10;
            }
            .login-right {
                flex: 0.8;
                background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                color: white;
                text-align: center;
                padding: 2rem;
                position: relative;
                overflow: hidden;
            }
            .login-right::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100px;
                height: 100%;
                width: 200px;
                background: #fff;
                border-radius: 100% 0 0 100%;
                transform: scaleY(1.5);
            }
            @media (max-width: 992px) {
                .login-right { display: none; }
                .login-left { flex: 1; padding: 2rem; }
            }
            .welcome-text {
                font-size: 2.5rem;
                font-weight: 700;
                color: #2d3748;
                margin-bottom: 0.5rem;
            }
            .subtitle-text {
                color: #718096;
                margin-bottom: 2rem;
            }
            .company-logo {
                max-height: 50px;
                margin-bottom: 3rem;
            }
            .right-content {
                max-width: 400px;
                z-index: 5;
            }
            .right-content h2 {
                font-size: 1.8rem;
                font-weight: 600;
                margin-bottom: 1rem;
            }
            .right-illustration {
                width: 100%;
                max-width: 350px;
                margin-top: 2rem;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="login-container">
            <!-- Left Side -->
            <div class="login-left">
                <div class="mx-auto w-100" style="max-width: 450px;">
                    <div class="mb-4">
                        <a href="/">
                            @if(\App\Models\Setting::get('logo_path'))
                                <img src="{{ asset('storage/' . \App\Models\Setting::get('logo_path')) }}" alt="Logo" class="company-logo">
                            @else
                                <x-application-logo class="company-logo text-gray-500" />
                            @endif
                        </a>
                    </div>
                    
                    {{ $slot }}

                    <div class="mt-5 text-muted small">
                        {{ \App\Models\Setting::get('copyright_text', '© ' . date('Y') . ' Alert Dashboard') }}
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="login-right">
                <div class="right-content">
                    <h2>It's not about what you make.<br>It's about what you make possible.</h2>
                    <p>Welcome back! Stay connected with your alerts.</p>
                    
                    <!-- SVG Illustration (Minimal technical style) -->
                    <svg class="right-illustration" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="50" y="50" width="300" height="200" rx="20" fill="white" fill-opacity="0.1" stroke="white" stroke-width="2"/>
                        <circle cx="200" cy="120" r="40" stroke="white" stroke-width="2"/>
                        <rect x="140" y="180" width="120" height="10" rx="5" fill="white" fill-opacity="0.5"/>
                        <rect x="160" y="200" width="80" height="10" rx="5" fill="white" fill-opacity="0.5"/>
                        <path d="M100 250 H300" stroke="white" stroke-width="2" stroke-dasharray="5 5"/>
                    </svg>
                </div>
            </div>
        </div>
    </body>
</html>
