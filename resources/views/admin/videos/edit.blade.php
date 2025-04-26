@extends('layouts.admin')

@section('title', 'Edit Training Video')

@section('content')
<h1 class="admin-page-title">Edit Training Video</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">Video Details</h2>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.videos.show', $video) }}" class="btn-admin-secondary">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('admin.videos.index') }}" class="btn-admin-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Back to Videos
            </a>
        </div>
    </div>
    
    @if ($errors->any())
    <div class="admin-alert admin-alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <form action="{{ route('admin.videos.update', $video) }}" method="POST" class="admin-form">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="title">Video Title</label>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $video->title) }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="order_sequence">Display Order</label>
                <input type="number" name="order_sequence" id="order_sequence" class="form-control @error('order_sequence') is-invalid @enderror" value="{{ old('order_sequence', $video->order_sequence) }}" min="0" required>
                @error('order_sequence')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <br><small class="text-muted">Videos will be displayed in ascending order.</small>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $video->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <br><small class="text-muted">Provide a detailed description of the video content.</small>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="video_path">Video URL (Google Cloud Storage)</label>
                <input type="url" name="video_path" id="video_path" class="form-control @error('video_path') is-invalid @enderror" value="{{ old('video_path', $video->video_path) }}" required>
                @error('video_path')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <br><small class="text-muted">Enter the full URL to the video file in Google Cloud Storage.</small>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="thumbnail_path">Thumbnail URL (Optional)</label>
                <input type="url" name="thumbnail_path" id="thumbnail_path" class="form-control @error('thumbnail_path') is-invalid @enderror" value="{{ old('thumbnail_path', $video->thumbnail_path) }}">
                @error('thumbnail_path')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <br><small class="text-muted">Enter the full URL to the thumbnail image in Google Cloud Storage.</small>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="duration">Duration (seconds)</label>
                <input type="number" name="duration" id="duration" class="form-control @error('duration') is-invalid @enderror" value="{{ old('duration', $video->duration) }}" min="0">
                @error('duration')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <br><small class="text-muted">Total duration in seconds (e.g., 300 for a 5-minute video).</small>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="price">Price</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $video->price) }}" min="0" required>
                <select name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" required>
                    <option value="AUD" {{ old('currency', $video->currency) == 'AUD' ? 'selected' : '' }}>AUD</option>
                    <option value="USD" {{ old('currency', $video->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="EUR" {{ old('currency', $video->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                    <option value="GBP" {{ old('currency', $video->currency) == 'GBP' ? 'selected' : '' }}>GBP</option>
                </select>
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <br><small class="text-muted">Set to 0 for free videos.</small>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1" {{ old('is_published', $video->is_published) ? 'checked' : '' }}>
                <label class="form-check-label d-block" for="is_published" style="margin-left: 1.5rem;">Published</label>
                <small class="text-muted" style="margin-left: 1.5rem;">If unchecked, the video will be saved as a draft and won't be visible to users.</small>
            </div>
        </div>
        
        <div class="admin-form-actions">
            <button type="submit" class="btn-admin-primary">Update Video</button>
            <a href="{{ route('admin.videos.index') }}" class="btn-admin-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style src="{{ asset('css/admin/admin-video-styles.css') }}"></style>
@endpush