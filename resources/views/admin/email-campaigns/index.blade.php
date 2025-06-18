@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Email Campaigns</div>

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
        <h2 class="admin-card-title">Marketing Campaigns</h2>
        <div class="d-flex gap-2">
            @if(!$gmailConfigured)
                <a href="{{ route('admin.email-campaigns.configure-gmail') }}" class="btn-admin-secondary">
                    <i class="fas fa-cog"></i> Configure Gmail API
                </a>
            @endif
            <a href="{{ route('admin.email-campaigns.create') }}" class="btn-admin-primary">
                <i class="fas fa-plus"></i> Create Campaign
            </a>
        </div>
    </div>

    @if(!$gmailConfigured)
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Gmail API not configured.</strong> 
            <a href="{{ route('admin.email-campaigns.configure-gmail') }}">Configure it now</a> 
            for better email deliverability and higher sending limits.
        </div>
    @else
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle"></i>
            Gmail API is configured and ready for sending campaigns.
        </div>
    @endif

    @if($campaigns->isEmpty())
        <div class="empty-state text-center py-5">
            <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
            <h4>No Email Campaigns Yet</h4>
            <p class="text-muted">Create your first email campaign to start reaching your audience.</p>
            <a href="{{ route('admin.email-campaigns.create') }}" class="btn-admin-primary">
                <i class="fas fa-plus"></i> Create Your First Campaign
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Campaign Name</th>
                        <th>Subject</th>
                        <th>Segment</th>
                        <th>Recipients</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $campaign->name }}</div>
                            </td>
                            <td>{{ Str::limit($campaign->subject, 30) }}</td>
                            <td>
                                <span class="badge bg-info">{{ $campaign->segment_name }}</span>
                            </td>
                            <td>
                                @if($campaign->total_recipients)
                                    {{ number_format($campaign->total_recipients) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{!! $campaign->formatted_status !!}</td>
                            <td>{{ $campaign->created_at->format('M d, Y') }}</td>
                            <td class="actions-cell">
                                <a href="{{ route('admin.email-campaigns.show', $campaign) }}" 
                                   class="btn-admin-secondary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($campaign->status === 'draft')
                                    <a href="{{ route('admin.email-campaigns.edit', $campaign) }}" 
                                       class="btn-admin-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($gmailConfigured)
                                        <a href="{{ route('admin.email-campaigns.send-confirmation', $campaign) }}" 
                                           class="btn-admin-primary" title="Send">
                                            <i class="fas fa-paper-plane"></i>
                                        </a>
                                    @else
                                        <span class="btn-admin-secondary" 
                                              title="Configure Gmail API to send campaigns" disabled>
                                            <i class="fas fa-paper-plane"></i>
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if($gmailConfigured)
    <div class="admin-card mt-4">
        <h3 class="admin-card-title">Email Sending Status</h3>
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                    <div class="stat-value">{{ $campaigns->where('status', 'sent')->count() }}</div>
                    <div class="stat-label">Sent Campaigns</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-value">{{ $campaigns->sum('total_recipients') }}</div>
                    <div class="stat-label">Total Recipients</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-draft2digital"></i></div>
                    <div class="stat-value">{{ $campaigns->where('status', 'draft')->count() }}</div>
                    <div class="stat-label">Draft Campaigns</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value text-success">Gmail API</div>
                    <div class="stat-label">Ready to Send</div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .empty-state {
        background: rgba(15, 15, 35, 0.4);
        border-radius: 10px;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }

    .stat-card {
        background: rgba(15, 15, 35, 0.4);
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        border: 1px solid rgba(138, 43, 226, 0.3);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(138, 43, 226, 0.5);
    }

    .stat-icon {
        font-size: 2rem;
        color: #d8b5ff;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: white;
        margin-bottom: 5px;
    }

    .stat-label {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
    }

    .actions-cell {
        white-space: nowrap;
    }

    .actions-cell .btn-admin-secondary,
    .actions-cell .btn-admin-primary {
        margin-right: 5px;
        padding: 5px 10px;
    }
</style>
@endpush