<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Dashboard - Setup Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .setup-card {
            width: 100%;
            max-width: 600px;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .setup-header {
            background: linear-gradient(90deg, #4f46e5 0%, #3b82f6 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem 1rem 0 0;
            text-align: center;
        }
        .setup-body {
            padding: 2rem;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 0.5rem;
        }
        .step {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #e2e8f0;
        }
        .step.active {
            background-color: #4f46e5;
            width: 30px;
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #4f46e5;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 0.5rem;
        }
        .btn-primary:hover {
            background-color: #4338ca;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card setup-card mx-auto">
            <div class="setup-header">
                <i class="bi bi-shield-check fs-1 mb-2"></i>
                <h1 class="h3 mb-0">Alert Dashboard</h1>
                <p class="mb-0 text-white-50">Installation Wizard</p>
            </div>
            <div class="setup-body">
                @if(session('error'))
                    <div class="alert alert-danger mb-4">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
