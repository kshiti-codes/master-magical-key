@extends('layouts.auth')

@section('content')
<div class="mystical-card">
    <div class="card-header">Recover Your Portal Access</div>

    <div class="card-body">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-portal">
                    Send Reset Link
                </button>
            </div>
            
            <div class="purple-line"></div>
            
            <div class="text-center">
                <a href="{{ route('login') }}" class="mystic-link">
                    <i class="fas fa-arrow-left mr-2"></i> Return to Portal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection