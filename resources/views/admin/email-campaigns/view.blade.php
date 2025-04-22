@extends('layouts.admin')

@section('title', 'View Email Campaign')

@push('styles')
<style>
    .campaign-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    
    .campaign-status {
        font-size: 0.9rem;
        padding: 5px 10px;
        border-radius: 15px;
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
    
    .campaign-details {
        margin-bottom: 30px;
        padding: 20px;
        background: rgba(30, 30, 60, 0.4);
        border-radius: 8px;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .campaign-detail-row {
        display: flex;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
        padding-bottom: 15px;
    }
    
    .campaign-detail-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }
    
    .detail-label {
        width: 150px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .detail-value {
        flex: 1;
    }
    
    .email-preview {
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        background: rgba(10, 10, 30, 0.4);
        max-height: 300px;
        overflow-y: auto;
    }
    
    .preview-header {
        background: rgba(5, 5, 20, 0.5);
        padding: 15px;
        margin: -20px -20px 20px -20px;
        border-radius: 8px 8px 0 0;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .preview-subject {
        font-size: 1.2rem;
        color: #d8b5ff;
        margin-bottom: 5px;
    }
    
    .preview-metadata {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.6);
    }
    
    .recipient-card {
        margin-bottom: 30px;
    }
    
    .recipient-search {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
    }
    
    .recipient-search input {
        flex: 1;
        background: rgba(10, 10, 30, 0.4);
        border: 1px solid rgba(138, 43, 226, 0.3);
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
    }
    
    .recipients-table th {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 5px;
    }
    
    .page-item {
        background: rgba(30, 30, 60, 0.4);
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 5px;
    }
    
    .page-item.active {
        background: rgba(138, 43, 226, 0.4);
    }
    
    .page-link {
        color: #d8b5ff;
        padding: 8px 12px;
        display: block;
        text-decoration: none;
    }
</style>
@endpush

@section('content')
<h1 class="admin-page-title">Campaign Details</h1>

<div class="campaign-header">
    <h2 class="admin-card-title mb-0">{{ $campaign->name }}</h2>
    <div>
        @if($campaign->status === 'draft')
            <a href="{{ route('admin.email-campaigns.edit', $campaign) }}" class="btn-admin-primary me-2">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.email-campaigns.send-confirmation', $campaign) }}" class="btn-admin-primary">
                <i class="fas fa-paper-plane"></i> Send
            </a>
        @endif
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn-admin-secondary ms-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <!-- Campaign Details Card -->
        <div class="admin-card">
            <h3 class="admin-card-title">Campaign Information</h3>
            
            <div class="campaign-details">
                <div class="campaign-detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="campaign-status status-{{ $campaign->status }}">
                            {{ ucfirst($campaign->status) }}
                        </span>
                    </div>
                </div>
                
                <div class="campaign-detail-row">
                    <div class="detail-label">Subject:</div>
                    <div class="detail-value">{{ $campaign->subject }}</div>
                </div>
                
                <div class="campaign-detail-row">
                    <div class="detail-label">Created on:</div>
                    <div class="detail-value">{{ $campaign->created_at->format('M d, Y h:i A') }}</div>
                </div>
                
                <div class="campaign-detail-row">
                    <div class="detail-label">Sent on:</div>
                    <div class="detail-value">{{ $campaign->formatted_sent_date }}</div>
                </div>
                
                <div class="campaign-detail-row">
                    <div class="detail-label">Segment:</div>
                    <div class="detail-value">{{ $campaign->segment_name }}</div>
                </div>
                
                <div class="campaign-detail-row">
                    <div class="detail-label">Recipients:</div>
                    <div class="detail-value">{{ $campaign->total_recipients ?: 'Not sent yet' }}</div>
                </div>
            </div>
            
            <!-- Email Content Preview -->
            <h3 class="admin-card-title mt-4">Email Preview</h3>
            <div class="email-preview">
                <div class="preview-header">
                    <div class="preview-subject">{{ $campaign->subject }}</div>
                    <div class="preview-metadata">
                        From: Master Magical Key &lt;noreply@mastermagicalkey.com&gt;<br>
                        To: [Recipient]
                    </div>
                </div>
                
                <div>
                    {!! $campaign->content !!}
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <!-- Recipients Card -->
        <div class="admin-card recipient-card">
            <h3 class="admin-card-title">Recipients</h3>
            
            @if($campaign->status === 'sent')
                <div class="recipient-search">
                    <input type="text" id="recipientSearch" placeholder="Search by name or email..." class="form-control">
                    <button class="btn-admin-secondary" id="searchBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="admin-table recipients-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recipients as $recipient)
                                <tr>
                                    <td>{{ $recipient->user->name ?? 'Unknown User' }}</td>
                                    <td>{{ $recipient->email }}</td>
                                    <td>
                                        @if($recipient->sent)
                                            <span class="status-badge status-sent">Sent</span>
                                        @else
                                            <span class="status-badge">Failed</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(is_string($recipient->sent_at))
                                            {{ $recipient->sent_at ? date('M d, Y h:i A', strtotime($recipient->sent_at)) : 'N/A' }}
                                        @else
                                            {{ $recipient->sent_at ? $recipient->sent_at->format('M d, Y h:i A') : 'N/A' }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-container">
                    {{ $recipients->links() }}
                </div>
                
            @else
                <!-- Campaign not sent yet -->
                <div class="text-center py-5">
                    <i class="fas fa-paper-plane" style="font-size: 3rem; color: rgba(138, 43, 226, 0.5); margin-bottom: 20px;"></i>
                    <p>This campaign has not been sent yet. Recipients will appear here after sending.</p>
                    
                    @if($campaign->status === 'draft')
                        <a href="{{ route('admin.email-campaigns.send-confirmation', $campaign) }}" class="btn-admin-primary mt-3">
                            <i class="fas fa-paper-plane"></i> Send Campaign Now
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('recipientSearch');
        const searchBtn = document.getElementById('searchBtn');
        const recipientRows = document.querySelectorAll('.recipients-table tbody tr');
        
        if (searchInput && searchBtn) {
            searchBtn.addEventListener('click', performSearch);
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        }
        
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase();
            
            recipientRows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });
</script>
@endpush