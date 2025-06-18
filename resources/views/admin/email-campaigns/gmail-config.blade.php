@extends('layouts.admin')

@section('title', 'Gmail API Configuration')

@section('content')
<div class="admin-page-title">Gmail API Configuration</div>

@if(session('success'))
    <div class="admin-alert admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="admin-alert admin-alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title">Gmail API Setup</h2>
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Campaigns
        </a>
    </div>

    @if($isConfigured)
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Gmail API is properly configured and ready to send emails.
        </div>
        
        <div class="mt-4">
            <h3>Test Email Sending</h3>
            <p>You can now send email campaigns using Gmail API for better deliverability.</p>
        </div>
    @elseif($authUrl)
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Gmail API is properly configured and ready to send emails.
        </div>
        
        <div class="mt-4">
            <h3>Test Email Sending</h3>
            <p>You can now send email campaigns using Gmail API for better deliverability.</p>
        </div>
    @elseif(!$authUrl)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Gmail API is not configured. Follow the steps below to set it up.
        </div>

        <div class="setup-steps">
            <h3>Setup Instructions</h3>
            
            <div class="step-card mb-4">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Create Google Cloud Project</h4>
                    <p>Go to the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a> and create a new project or select an existing one.</p>
                </div>
            </div>

            <div class="step-card mb-4">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Enable Gmail API</h4>
                    <p>In your Google Cloud project, go to "APIs & Services" > "Library" and enable the Gmail API.</p>
                </div>
            </div>

            <div class="step-card mb-4">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Create OAuth 2.0 Credentials</h4>
                    <p>Go to "APIs & Services" > "Credentials" and create OAuth 2.0 credentials for a web application.</p>
                    <p><strong>Authorized redirect URI:</strong> <code>{{ route('admin.email-campaigns.gmail-callback') }}</code></p>
                </div>
            </div>

            <div class="step-card mb-4">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Download Credentials</h4>
                    <p>Download the JSON credentials file and save it as <code>google-credentials.json</code> in your <code>storage/app/</code> directory.</p>
                </div>
            </div>

            <div class="step-card mb-4">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h4>Install Google Client Library</h4>
                    <p>Run the following command in your project root:</p>
                    <code class="d-block bg-dark text-light p-2 rounded">composer require google/apiclient</code>
                </div>
            </div>

            <div class="step-card mb-4">
                <div class="step-number">6</div>
                <div class="step-content">
                    <h4>Authorize Application</h4>
                    <p>Click the button below to authorize the application to send emails on your behalf:</p>
                    <a href="{{ $authUrl }}" class="btn-admin-primary mt-2" target="_blank">
                        <i class="fas fa-key"></i> Authorize Gmail Access
                    </a>
                    <p class="mt-2 text-muted small">This will open Google's authorization page. After granting permission, you'll be redirected back here.</p>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-4">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> Make sure to use the same Google account that you want to send emails from. The "From" address in your emails will be this Google account's email address.
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .setup-steps {
        margin-top: 20px;
    }

    .step-card {
        display: flex;
        align-items: flex-start;
        background: rgba(15, 15, 35, 0.4);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }

    .step-number {
        background: linear-gradient(135deg, #8a2be2, #9400d3);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .step-content h4 {
        color: #d8b5ff;
        margin-bottom: 10px;
        font-family: 'Cinzel', serif;
    }

    .step-content p {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 10px;
    }

    .step-content a {
        color: #d8b5ff;
        text-decoration: none;
    }

    .step-content a:hover {
        color: #ffffff;
        text-decoration: underline;
    }

    .step-content code {
        background: rgba(0, 0, 0, 0.3);
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }
</style>
@endpush