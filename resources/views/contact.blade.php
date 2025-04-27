<!-- resources/views/contact.blade.php -->
@extends('layouts.app')

@push('styles')
<style>
    .contact-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .contact-title {
        text-align: center;
        color: #fff;
        font-family: 'Cinzel', serif;
        margin-bottom: 20px;
        font-size: 2.2rem;
        letter-spacing: 2px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .contact-subtitle {
        text-align: center;
        color: #d8b5ff;
        margin-bottom: 40px;
        font-size: 1.2rem;
    }
    
    .contact-card {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        border: 1px solid rgba(138, 43, 226, 0.3);
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .contact-form-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px;
    }
    
    .form-group {
        flex: 1 1 100%;
    }
    
    @media (min-width: 768px) {
        .form-group.half {
            flex: 0 0 calc(50% - 10px);
        }
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #d8b5ff;
        font-weight: 500;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        background: rgba(15, 15, 35, 0.4);
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 5px;
        color: #fff;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        background: rgba(15, 15, 35, 0.6);
        border-color: rgba(138, 43, 226, 0.7);
        outline: none;
        box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
    }
    
    textarea.form-control {
        min-height: 150px;
        resize: vertical;
    }
    
    .btn-submit {
        background: linear-gradient(to right, #4b0082, #9400d3);
        border: none;
        color: white;
        padding: 12px 25px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        margin-top: 10px;
        width: 100%;
    }
    
    .btn-submit:hover {
        background: linear-gradient(to right, #9400d3, #4b0082);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(148, 0, 211, 0.3);
    }
    
    .contact-info {
        margin-top: 30px;
        background: rgba(15, 15, 35, 0.4);
        border-radius: 10px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .contact-info-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.3rem;
        margin-bottom: 15px;
        text-align: center;
    }
    
    .contact-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .contact-info-item i {
        width: 30px;
        color: #d8b5ff;
        font-size: 1.2rem;
        margin-right: 10px;
    }
    
    .social-links {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }
    
    .social-link {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(138, 43, 226, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }
    
    .social-link:hover {
        background: rgba(138, 43, 226, 0.7);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.4);
        color: #fff;
    }
    
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: rgba(40, 167, 69, 0.2);
        border-left: 4px solid #28a745;
        color: #a0ffa0;
    }
    
    .alert-danger {
        background: rgba(220, 53, 69, 0.2);
        border-left: 4px solid #dc3545;
        color: #ffa0a0;
    }
    
    .invalid-feedback {
        color: #ff6b6b;
        font-size: 0.875rem;
        margin-top: 5px;
    }
</style>
@endpush

@section('content')
<div class="contact-container fade-transition">
    <h1 class="contact-title">Contact Us</h1>
    <p class="contact-subtitle">Reach out to us for any inquiries about your mystical journey</p>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <div class="contact-card">
        <form action="{{ route('contact.submit') }}" method="POST" class="contact-form">
            @csrf
            <div class="contact-form-container">
                <div class="form-group half">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" style="width: 95%;" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group half">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="message" class="form-label">Your Message</label>
                    <textarea name="message" id="message" class="form-control @error('message') is-invalid @enderror" rows="5" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="contact-info">
        <h3 class="contact-info-title">Our Information</h3>
        
        <div class="contact-info-item">
            <i class="fas fa-envelope"></i>
            <span>support@peopleofpeony.com</span>
        </div>
        
        <div class="contact-info-item">
            <i class="fas fa-globe"></i>
            <span>www.mastermagicalkey.com</span>
        </div>
    </div>
</div>
@endsection
