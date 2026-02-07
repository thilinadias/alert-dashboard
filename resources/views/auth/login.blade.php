<x-guest-layout>
    <div class="mb-5">
        <h1 class="welcome-text">Welcome Back!</h1>
        <p class="subtitle-text">Please Log in to your account.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label text-muted small">{{ __('Email Address') }}</label>
            <input id="email" class="form-control form-control-lg border-secondary-subtle" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="tuhelrana@gmail.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label text-muted small">{{ __('Password') }}</label>
            <input id="password" class="form-control form-control-lg border-secondary-subtle"
                            type="password"
                            name="password"
                            required placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Remember Me -->
            <div class="form-check">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                <label for="remember_me" class="form-check-label text-muted small">{{ __('Remember me') }}</label>
            </div>

            @if (Route::has('password.request'))
                <a class="text-sm text-danger text-decoration-none small fw-bold" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
            <button type="submit" class="btn btn-primary btn-lg px-5 py-2 shadow-sm" style="background-color: #1a5c5c; border: none;">
                {{ __('Login') }}
            </button>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg px-4 py-2" style="border-color: #1a5c5c; color: #1a5c5c;">
                    {{ __('Create account') }}
                </a>
            @endif
        </div>
    </form>
</x-guest-layout>
