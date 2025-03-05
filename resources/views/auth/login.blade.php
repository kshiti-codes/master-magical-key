@extends('layouts.auth')

@section('content')
<div class="mystical-card">
    <div class="card-header">Enter The Portal</div>

    <div class="card-body">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Remember Me
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-portal w-100">
                    Journey Inward
                </button>
            </div>

            <div class="text-center mt-3 mb-2">
                @if (Route::has('password.request'))
                    <a class="mystic-link d-inline-block py-2" href="{{ route('password.request') }}">
                        Forgot Your Password?
                    </a>
                @endif
            </div>
            
            <div class="purple-line"></div>
            
            <div class="text-center">
                <p class="text-white-50">New to the journey?</p>
                <a href="{{ route('register') }}" class="mystic-link">Register here</a>
            </div>
        </form>
    </div>
</div>
@endsection