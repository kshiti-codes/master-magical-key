@extends('layouts.admin')

@section('title', 'Video Details')

@section('content')
<h1 class="admin-page-title">Training Video Details</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">{{ $video->title }}</h2>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.videos.edit', $video) }}" class="btn-admin-secondary">
                <i class="fas fa-edit"></i> Edit Video
            </a>
            <a href="{{ route('admin.videos.index') }}" class="btn-admin-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Back to Videos
            </a>
        </div>
    </div>
    
    <div class="video-player-container mb-4">
        <div class="video-player">
            @if($video->video_path && strpos($video->video_path, 'drive.google.com') !== false)
                @php
                    // Convert Google Drive link to direct download link instead of embed
                    $driveId = null;
                    
                    // Extract file ID from different Google Drive URL formats
                    if (preg_match('/\/file\/d\/([^\/\?]+)/', $video->video_path, $matches)) {
                        $driveId = $matches[1];
                    } elseif (preg_match('/id=([^&]+)/', $video->video_path, $matches)) {
                        $driveId = $matches[1];
                    }
                @endphp
                
                <div class="drive-preview-container">
                    <div class="drive-info">
                        <i class="fab fa-google-drive fa-2x"></i>
                        <h4>Google Drive Video</h4>
                        <p>Due to Google Drive security restrictions, videos cannot be embedded directly in the admin panel.</p>
                        <div class="drive-buttons">
                            <a href="{{ $video->video_path }}" target="_blank" class="direct-link-btn">
                                <i class="fas fa-external-link-alt"></i> Open in Google Drive
                            </a>
                            @if($driveId)
                            <a href="https://drive.google.com/uc?export=download&id={{ $driveId }}" target="_blank" class="direct-link-btn">
                                <i class="fas fa-download"></i> Download Video
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($video->video_path && strpos($video->video_path, 'http') === 0)
                <!-- Regular video sources -->
                <video id="videoPlayer" controls class="video-element">
                    <source src="{{ $video->video_path }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            @else
                <div class="video-placeholder">
                    <i class="fas fa-video fa-3x"></i>
                    <p>No valid video URL provided</p>
                </div>
            @endif
        </div>
        
        @if($video->thumbnail_path)
            <div class="thumbnail-preview">
                <h4 class="thumbnail-heading">Thumbnail Preview</h4>
                <img src="{{ $video->thumbnail_path }}" alt="{{ $video->title }}" class="thumbnail-img">
            </div>
        @endif
    </div>
    
    <div class="video-meta mb-4">
        <span class="status-badge {{ $video->is_published ? 'status-published' : 'status-draft' }}">
            {{ $video->is_published ? 'Published' : 'Draft' }}
        </span>
        <span class="meta-item">
            <i class="fas fa-dollar-sign"></i> {{ $video->getFormattedPriceAttribute() }}
        </span>
        <span class="meta-item">
            <i class="fas fa-sort"></i> Order: {{ $video->order_sequence }}
        </span>
    </div>
    
    <div class="video-actions mb-4">
        <a href="{{ $video->video_path }}" target="_blank" class="action-btn action-btn-primary">
            <i class="fas fa-play"></i> Test Video URL
        </a>
        
        <form action="{{ route('admin.videos.toggle-status', $video) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="action-btn">
                <i class="fas {{ $video->is_published ? 'fa-eye-slash' : 'fa-eye' }}"></i> 
                {{ $video->is_published ? 'Unpublish' : 'Publish' }}
            </button>
        </form>
    </div>
    
    <div class="content-section mb-4">
        <h3 class="section-heading">Description</h3>
        <div class="content-box">
            {!! nl2br(e($video->description)) !!}
        </div>
    </div>
    
    <div class="content-section mb-4">
        <h3 class="section-heading">Storage Information</h3>
        <div class="content-box">
            <div class="url-item">
                <div class="url-label">Video URL:</div>
                <div class="url-value">
                    <a href="{{ $video->video_path }}" target="_blank" class="video-url">
                        {{ $video->video_path }}
                    </a>
                </div>
            </div>
            
            @if($video->thumbnail_path)
            <div class="url-item mt-3">
                <div class="url-label">Thumbnail URL:</div>
                <div class="url-value">
                    <a href="{{ $video->thumbnail_path }}" target="_blank" class="video-url">
                        {{ $video->thumbnail_path }}
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <div class="content-section mb-4">
        <h3 class="section-heading">Video Statistics</h3>
        <div class="stats-container">
            <div class="stat-row">
                <div class="stat-label">Purchased by:</div>
                <div class="stat-value">{{ $purchasedCount }} users</div>
            </div>
            <div class="stat-row">
                <div class="stat-label">Total Views:</div>
                <div class="stat-value">{{ $viewCount }}</div>
            </div>
            <div class="stat-row">
                <div class="stat-label">Created:</div>
                <div class="stat-value">{{ $video->created_at->format('M d, Y') }}</div>
            </div>
            <div class="stat-row">
                <div class="stat-label">Last Updated:</div>
                <div class="stat-value">{{ $video->updated_at->format('M d, Y') }}</div>
            </div>
        </div>
    </div>
    
    <div class="content-section danger-section">
        <h3 class="section-heading danger-heading">Danger Zone</h3>
        <p class="danger-text">Be careful with these actions - they cannot be undone.</p>
        
        <form action="{{ route('admin.videos.destroy', $video) }}" method="POST" id="delete-form">
            @csrf
            @method('DELETE')
            <button type="button" class="danger-button" id="delete-btn">
                <i class="fas fa-trash"></i> Delete This Video
            </button>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style src="{{ asset('css/admin/admin-video-styles.css') }}"></style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete confirmation
        const deleteBtn = document.getElementById('delete-btn');
        const deleteForm = document.getElementById('delete-form');
        
        if (deleteBtn && deleteForm) {
            deleteBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this video? This action cannot be undone.')) {
                    deleteForm.submit();
                }
            });
        }
        
        // Video player error handling
        const driveFrame = document.getElementById('driveFrame');
        const videoPlayer = document.getElementById('videoPlayer');
        
        if (driveFrame) {
            // Handle iframe loading error (though Google Drive iframe usually loads even if content is inaccessible)
            driveFrame.addEventListener('error', function() {
                showDirectLinkOption(true);
            });
        }
        
        if (videoPlayer) {
            videoPlayer.addEventListener('error', function() {
                showDirectLinkOption(true);
            });
        }
        
        function showDirectLinkOption(isError = false) {
            // Create a direct link option if not already present
            const videoPlayerContainer = document.querySelector('.video-player');
            
            if (!document.querySelector('.video-direct-link')) {
                const directLinkContainer = document.createElement('div');
                directLinkContainer.className = 'video-direct-link text-center mt-3';
                
                if (isError) {
                    directLinkContainer.innerHTML = `
                        <div class="video-placeholder video-error">
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                            <p>There might be an issue with this Google Drive video.</p>
                            <small>The video might be private or require permission.</small>
                        </div>
                    `;
                }
                
                directLinkContainer.innerHTML += `
                    <div class="mt-3">
                        <a href="{{ $video->video_path }}" target="_blank" class="direct-link-btn">
                            <i class="fas fa-external-link-alt"></i> Open in Google Drive
                        </a>
                    </div>
                `;
                
                videoPlayerContainer.appendChild(directLinkContainer);
            }
        }
        
        // Always add a direct link option for Google Drive videos
        showDirectLinkOption(false);
    });
</script>
@endpush