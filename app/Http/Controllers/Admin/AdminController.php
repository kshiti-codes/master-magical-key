<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\Spell;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get sales stats
        $totalSales = Purchase::where('status', 'completed')->sum('amount');
        $monthSales = Purchase::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
            
        // Get content stats
        $chaptersCount = Chapter::count();
        $publishedChaptersCount = Chapter::where('is_published', true)->count();
        $spellsCount = Spell::count();
        $publishedSpellsCount = Spell::where('is_published', true)->count();
        
        // Get user stats
        $usersCount = User::count();
        $newUsers = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        // Get recent purchases
        $recentPurchases = Purchase::with(['user', 'items'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get monthly sales data for chart
        $monthlySales = Purchase::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
            
        // Format data for chart
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthData = $monthlySales->where('month', $i)->first();
            $chartData[] = [
                'month' => date('F', mktime(0, 0, 0, $i, 1)),
                'total' => $monthData ? round($monthData->total, 2) : 0
            ];
        }

        return view('admin.dashboard', compact(
            'totalSales', 
            'monthSales', 
            'chaptersCount', 
            'publishedChaptersCount',
            'spellsCount',
            'publishedSpellsCount',
            'usersCount',
            'newUsers',
            'recentPurchases',
            'chartData'
        ));
    }
}