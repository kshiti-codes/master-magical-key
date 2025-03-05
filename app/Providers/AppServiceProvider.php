<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('mysticalTransitions', function ($options = '{}') {
            return "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof MysticalTransitions !== 'undefined') {
                        MysticalTransitions.init($options);
                    } else {
                        console.error('MysticalTransitions not loaded. Make sure to include the JS file.');
                    }
                });
            </script>";
        });
    }
}
