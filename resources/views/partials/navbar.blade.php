<!-- resources/views/partials/navbar.blade.php -->
<div class="hamburger-menu-container">
    <button class="hamburger-button" onclick="toggleMenu()" type="button" aria-label="Menu">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>
</div>

<div class="menu-overlay" id="menuOverlay" onclick="if(event.target === this) closeMenu()">
    <div class="menu-content">
        <button class="close-menu" onclick="closeMenu()" type="button">×</button>
        <div class="menu-links">
            <a href="{{ route('home') }}" class="menu-link">Home</a>
            <a href="{{ route('about') }}" class="menu-link">About</a>
            <a href="{{ route('chapters.index') }}" class="menu-link">Chapters</a>
            <a href="{{ route('spells.index') }}" class="menu-link">Spells</a>
            <a href="{{ route('videos.index') }}" class="menu-link">Training Video</a>
            <a href="{{ route('subscriptions.index') }}" class="menu-link">Subscriptions</a>
            <a href="{{ route('contact') }}" class="menu-link">Contact</a>
            <a href="{{ route('faq') }}" class="menu-link">FAQ</a>
            @guest
                <a href="{{ route('login') }}" class="menu-link">Login</a>
                <a href="{{ route('register') }}" class="menu-link">Register</a>
            @else
                <a href="{{ route('logout') }}" class="menu-link" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @endguest
        </div>
    </div>
</div>

<script>
    // Directly add menu functions to window object
    function toggleMenu() {
        document.getElementById('menuOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMenu() {
        document.getElementById('menuOverlay').classList.remove('active');
        document.body.style.overflow = '';
    }
</script>