<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') - {{ config('app.name', 'Master Magical Key') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;700&family=Rajdhani:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    
    <script src="{{ asset('js/stars.js') }}" defer></script>
    
    <!-- Styles -->
    <link href="{{ asset('css/admin/admin.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-logo">
            <h1>MASTER MAGICAL KEY</h1>
        </div>
        
        <ul class="admin-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                </a>
            </li>
            
            <div class="admin-menu-divider"></div>
            <div class="admin-menu-category">Content Management</div>
            
            <li>
                <a href="{{ route('admin.chapters.index') }}" class="{{ request()->routeIs('admin.chapters*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i> Chapters
                </a>
            </li>
            <li>
                <a href="{{ route('admin.spells.index') }}" class="{{ request()->routeIs('admin.spells*') ? 'active' : '' }}">
                    <i class="fas fa-magic"></i> Spells
                </a>
            </li>
            
            <div class="admin-menu-divider"></div>
            <div class="admin-menu-category">User Management</div>
            
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            
            <div class="admin-menu-divider"></div>
            <div class="admin-menu-category">Financials</div>
            
            <li>
                <a href="{{ route('admin.purchases.index') }}" class="{{ request()->routeIs('admin.purchases*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i> Purchases
                </a>
            </li>
            <li>
                <a href="{{ route('admin.subscriptions.index') }}" class="{{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> Subscription Plans
                </a>
            </li>
            
            <div class="admin-menu-divider"></div>
            <div class="admin-menu-category">Reports</div>
            
            <li>
                <a href="{{ route('admin.reports.sales') }}" class="{{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Sales Report
                </a>
            </li>
            <li>
                <a href="{{ route('admin.subscriptions.analytics') }}" class="{{ request()->routeIs('admin.subscriptions.analytics') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Subscription Analytics
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.user_analysis') }}" class="{{ request()->routeIs('admin.reports.user_analysis') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> User Analytics
                </a>
            </li>

            <div class="admin-menu-divider"></div>
            <div class="admin-menu-category">Marketing</div>

            <li>
                <a href="{{ route('admin.email-campaigns.index') }}" class="{{ request()->routeIs('admin.email-campaigns*') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i> Email Campaigns
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="admin-content">
        <!-- Top Navbar -->
        <div class="admin-navbar">
            <button class="navbar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="navbar-right">
                <a href="{{ route('home') }}" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
        
        <!-- Flash Messages -->
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
        
        @if(session('warning'))
            <div class="admin-alert admin-alert-warning">
                <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            </div>
        @endif
        
        @if(session('info'))
            <div class="admin-alert admin-alert-info">
                <i class="fas fa-info-circle"></i> {{ session('info') }}
            </div>
        @endif
        
        <!-- Page Content -->
        @yield('content')
    </div>

    <!-- Scripts -->
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.body.classList.toggle('sidebar-open');
                });
            }
            
            // Close alerts with animation
            const alerts = document.querySelectorAll('.admin-alert');
            alerts.forEach(alert => {
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '&times;';
                closeBtn.className = 'close-alert';
                closeBtn.style.position = 'absolute';
                closeBtn.style.right = '10px';
                closeBtn.style.top = '10px';
                closeBtn.style.background = 'none';
                closeBtn.style.border = 'none';
                closeBtn.style.color = 'inherit';
                closeBtn.style.fontSize = '1.3rem';
                closeBtn.style.cursor = 'pointer';
                
                alert.style.position = 'relative';
                alert.appendChild(closeBtn);
                
                closeBtn.addEventListener('click', function() {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    alert.style.transition = 'all 0.3s ease';
                    
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>