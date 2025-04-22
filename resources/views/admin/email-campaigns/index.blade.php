@extends('layouts.admin')

@section('title', 'Email Campaigns')

@push('styles')
<style>
    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-draft {
        background: rgba(255, 193, 7, 0.2);
        color: #ffcc00;
    }
    
    .status-sending {
        background: rgba(0, 123, 255, 0.2);
        color: #0d6efd;
    }
    
    .status-sent {
        background: rgba(40, 167, 69, 0.2);
        color: #a0ffa0;
    }
    
    .campaign-row {
        transition: all 0.2s ease;
    }
    
    .campaign-row:hover {
        background: rgba(138, 43, 226, 0.1) !important;
    }
</style>
@endpush

@section('content')
<h1 class="admin-page-title">Email Campaigns</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">All Campaigns</h2>
        <a href="{{ route('admin.email-campaigns.create') }}" class="btn-admin-primary">
            <i class="fas fa-plus"></i> Create New Campaign
        </a>
    </div>

    @if($campaigns->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-envelope" style="font-size: 3rem; color: rgba(138, 43, 226, 0.5); margin-bottom: 20px;"></i>
            <p>No email campaigns yet. Create your first one!</p>
        </div>
    @else
        <div class="table-responsive" style="margin-top: 20px;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Segment</th>
                        <th>Status</th>
                        <th>Sent Date</th>
                        <th>Recipients</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                        <tr class="campaign-row">
                            <td>{{ $campaign->name }}</td>
                            <td>{{ $campaign->subject }}</td>
                            <td>{{ $campaign->segment_name }}</td>
                            <td>{!! $campaign->formatted_status !!}</td>
                            <td>{{ $campaign->formatted_sent_date }}</td>
                            <td>{{ $campaign->total_recipients ?: '-' }}</td>
                            <td class="actions-cell">
                                @if($campaign->status === 'draft')
                                    <a href="{{ route('admin.email-campaigns.edit', $campaign) }}" class="btn-admin-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.email-campaigns.send-confirmation', $campaign) }}" class="btn-admin-secondary" title="Send">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                @else
                                    <a href="{{ route('admin.email-campaigns.show', $campaign) }}" class="btn-admin-secondary disabled" title="view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection