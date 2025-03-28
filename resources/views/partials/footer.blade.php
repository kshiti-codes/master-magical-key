<!-- In resources/views/partials/footer.blade.php -->
@push('styles')
    <link href="{{ asset('css/components/footer.css') }}" rel="stylesheet">
@endpush

<footer class="mystical-footer">
    <div class="footer-copyright">
        &copy; {{ date('Y') }} Master Magical Key to the Universe
    </div>
    <div class="social-icons-container">
        <a href="https://www.facebook.com/share/1BWkN71JGK/" class="social-icon"><i class="fab fa-facebook-f"></i></a>
        <!-- <a href="https://www.instagram.com/pop_withlove_/" class="social-icon"><i class="fab fa-twitter"></i></a> -->
        <a href="https://www.instagram.com/pop_withlove_/" class="social-icon"><i class="fab fa-instagram"></i></a>
    </div>
</footer>