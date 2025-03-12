<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use App\Models\Cart;
use App\Models\CartItem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/ContentFormatter.php');
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

        Relation::morphMap([
            'cart' => Cart::class,
            'cart_item' => CartItem::class,
        ]);

        \DB::listen(function ($query) {
            \Log::info(
                $query->sql,
                [
                    'bindings' => $query->bindings,
                    'time' => $query->time
                ]
            );
        });
    }
}
