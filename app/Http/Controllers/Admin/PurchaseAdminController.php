<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Chapter;
use App\Models\Spell;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseAdminController extends Controller
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
     * Display a listing of all purchases.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $status = $request->input('status');
        $dateRange = $request->input('date_range');
        $search = $request->input('search');
        
        // Base query
        $query = Purchase::with(['user', 'items'])
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Paginate results
        $purchases = $query->paginate(15);
        
        // Get totals for summary
        $totalRevenue = Purchase::where('status', 'completed')->sum('amount');
        $monthlyRevenue = Purchase::where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');
            
        $totalPurchases = Purchase::where('status', 'completed')->count();
        
        return view('admin.purchases.index', compact(
            'purchases', 
            'totalRevenue', 
            'monthlyRevenue', 
            'totalPurchases'
        ));
    }

    /**
     * Display the specified purchase details.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show(Purchase $purchase)
    {
        // Load relationships
        $purchase->load(['user', 'items']);
        
        return view('admin.purchases.show', compact('purchase'));
    }

    /**
     * Get filtered purchase data for AJAX requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        // Use the same filtering logic as in your index method
        $status = $request->input('status');
        $dateRange = $request->input('date_range');
        $search = $request->input('search');
        
        // Base query
        $query = Purchase::with(['user', 'items'])
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('transaction_id', 'like', "%{$search}%")
                ->orWhereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }
        
        // Paginate results
        $purchases = $query->paginate(15);
        
        // Get totals for summary
        $totalRevenue = Purchase::where('status', 'completed')->sum('amount');
        $monthlyRevenue = Purchase::where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');
            
        $totalPurchases = Purchase::where('status', 'completed')->count();
        $avgOrderValue = $totalPurchases > 0 ? $totalRevenue / $totalPurchases : 0;
        
        // Render only the table part to HTML
        $html = view('admin.purchases.partials.purchases_table', compact('purchases'))->render();
        
        // Debug the stats object
        \Log::info('Stats object', [
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'totalPurchases' => $totalPurchases,
            'avgOrderValue' => $avgOrderValue
        ]);
        
        // Return JSON response with the exact structure expected by JavaScript
        return response()->json([
            'html' => $html,
            'stats' => [
                'totalRevenue' => (float)$totalRevenue,
                'monthlyRevenue' => (float)$monthlyRevenue,
                'totalPurchases' => (int)$totalPurchases,
                'avgOrderValue' => (float)$avgOrderValue
            ]
        ]);
    }

    /**
     * Generate sales report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function salesReport(Request $request)
    {
        // Get report parameters
        $period = $request->input('period', 'monthly');
        $year = $request->input('year', Carbon::now()->year);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // Define date formats based on period
        $groupFormat = '%Y-%m';
        $labelFormat = 'M Y';
        
        if ($period === 'daily') {
            $groupFormat = '%Y-%m-%d';
            $labelFormat = 'M d, Y';
            
            // Default to last 30 days if no dates specified
            if (!$startDate) {
                $startDate = Carbon::now()->subDays(30)->toDateString();
                $endDate = Carbon::now()->toDateString();
            }
        } elseif ($period === 'weekly') {
            $groupFormat = '%Y-%u'; // ISO week numbers
            $labelFormat = 'Week %W, %Y';
            
            // Default to last 12 weeks if no dates specified
            if (!$startDate) {
                $startDate = Carbon::now()->subWeeks(12)->startOfWeek()->toDateString();
                $endDate = Carbon::now()->toDateString();
            }
        } elseif ($period === 'monthly') {
            // Default to current year if no dates specified
            if (!$startDate) {
                $startDate = Carbon::createFromDate($year, 1, 1)->toDateString();
                $endDate = Carbon::createFromDate($year, 12, 31)->toDateString();
            }
        } elseif ($period === 'yearly') {
            $groupFormat = '%Y';
            $labelFormat = 'Y';
            
            // Default to last 5 years if no dates specified
            if (!$startDate) {
                $startDate = Carbon::now()->subYears(5)->startOfYear()->toDateString();
                $endDate = Carbon::now()->endOfYear()->toDateString();
            }
        }
        
        // Get sales data
        $salesData = $this->getSalesData($period, $startDate, $endDate, $groupFormat, $labelFormat);
        
        // Get top selling chapters
        $topChapters = $this->getTopSellingChapters($startDate, $endDate);
        
        // Get top selling spells
        $topSpells = $this->getTopSellingSpells($startDate, $endDate);
        
        // Customer metrics
        $newCustomers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $returningCustomers = Purchase::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        return view('admin.reports.sales', compact(
            'salesData',
            'period',
            'year',
            'startDate',
            'endDate',
            'topChapters',
            'topSpells',
            'newCustomers',
            'returningCustomers'
        ));
    }

    /**
     * Get user purchase analysis.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function userAnalysis(Request $request)
    {
        // Time period for analysis
        $period = $request->input('period', '30days');
        
        // Determine date range based on period
        $endDate = Carbon::now();
        
        if ($period === '30days') {
            $startDate = Carbon::now()->subDays(30);
        } elseif ($period === '90days') {
            $startDate = Carbon::now()->subDays(90);
        } elseif ($period === '6months') {
            $startDate = Carbon::now()->subMonths(6);
        } elseif ($period === '12months') {
            $startDate = Carbon::now()->subMonths(12);
        } else {
            $startDate = Carbon::now()->subDays(30); // Default
        }
        
        // New user registrations by day/week
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // User purchase behavior
        $userPurchases = Purchase::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('user_id, COUNT(*) as purchase_count, SUM(amount) as total_spent')
            ->groupBy('user_id')
            ->orderBy('total_spent', 'desc')
            ->limit(100)
            ->get();
        
        // Calculate average metrics
        $avgOrderValue = Purchase::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('amount');
        
        // Calculate purchase frequency
        $purchaseFrequency = DB::table(function($query) use ($startDate, $endDate) {
            $query->from('purchases')
                ->select('user_id', DB::raw('COUNT(*) as purchase_count'))
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('user_id');
        }, 'purchase_counts')->avg('purchase_count');
        
        // Calculate time between purchases (for users with multiple purchases)
        $repeatPurchaseDelay = DB::table(function($query) {
            $query->from('purchases as p1')
                ->join('purchases as p2', function($join) {
                    $join->on('p1.user_id', '=', 'p2.user_id')
                         ->whereRaw('p2.created_at > p1.created_at');
                })
                ->where('p1.status', 'completed')
                ->where('p2.status', 'completed')
                ->selectRaw('p1.id, MIN(DATEDIFF(p2.created_at, p1.created_at)) as days_between')
                ->groupBy('p1.id');
        }, 'purchase_delays')->avg('days_between');
        
        return view('admin.reports.user_analysis', compact(
            'newUsers',
            'userPurchases',
            'avgOrderValue',
            'purchaseFrequency',
            'repeatPurchaseDelay',
            'period'
        ));
    }

    /**
     * Export purchase data to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        try {
            // Get filter parameters
            $status = $request->input('status');
            $dateRange = $request->input('date_range');
            $search = $request->input('search');
            
            // Base query without eager loading
            $query = Purchase::orderBy('created_at', 'desc');
            
            // Apply filters
            if ($status) {
                $query->where('status', $status);
            }
            
            // Apply date range filter
            if ($dateRange) {
                $dates = explode(' - ', $dateRange);
                if (count($dates) == 2) {
                    $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }
            
            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            }
            
            // Execute the query to get all purchases
            $purchases = $query->get();
            
            // Log the count
            \Log::info('Exporting purchases: ' . $purchases->count());
            
            // Create a temp file
            $tempFilePath = storage_path('app/temp_exports');
            if (!file_exists($tempFilePath)) {
                mkdir($tempFilePath, 0755, true);
            }
            
            $tempFile = $tempFilePath . '/purchases_export_' . date('YmdHis') . '.csv';
            $handle = fopen($tempFile, 'w');
            
            // Add UTF-8 BOM to fix Excel encoding issues
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add CSV header
            fputcsv($handle, [
                'Invoice Number',
                'Date',
                'Customer Name',
                'Customer Email',
                'Transaction ID',
                'Items',
                'Subtotal',
                'Tax',
                'Total',
                'Status'
            ]);
            
            // Add purchase data
            foreach ($purchases as $purchase) {
                // Get related data manually instead of using eager loading
                $user = \App\Models\User::find($purchase->user_id);
                $purchaseItems = \App\Models\PurchaseItem::where('purchase_id', $purchase->id)->get();
                
                // Format items
                $items = [];
                
                if ($purchaseItems && $purchaseItems->count() > 0) {
                    foreach ($purchaseItems as $item) {
                        $itemDesc = '';
                        
                        if ($item->item_type === 'chapter') {
                            $chapter = \App\Models\Chapter::find($item->chapter_id);
                            if ($chapter) {
                                $itemDesc = "Chapter {$chapter->id}: {$chapter->title} ($" . number_format($item->price, 2) . ")";
                            } else {
                                $itemDesc = "Chapter: Unknown ($" . number_format($item->price, 2) . ")";
                            }
                        } elseif ($item->item_type === 'spell') {
                            $spell = \App\Models\Spell::find($item->spell_id);
                            if ($spell) {
                                $itemDesc = "Spell: {$spell->title} ($" . number_format($item->price, 2) . ")";
                            } else {
                                $itemDesc = "Spell: Unknown ($" . number_format($item->price, 2) . ")";
                            }
                        } else {
                            $itemDesc = "Item: Unknown ($" . number_format($item->price ?? 0, 2) . ")";
                        }
                        
                        $items[] = $itemDesc;
                    }
                }
                
                $itemsText = !empty($items) ? implode('; ', $items) : 'No items';
                
                // Create the row
                $row = [
                    $purchase->invoice_number ?? 'N/A',
                    $purchase->created_at ? $purchase->created_at->format('Y-m-d H:i:s') : 'N/A',
                    $user ? $user->name : 'Unknown',
                    $user ? $user->email : 'Unknown',
                    $purchase->transaction_id ?? 'N/A',
                    $itemsText,
                    '$' . number_format($purchase->subtotal ?? 0, 2),
                    '$' . number_format($purchase->tax ?? 0, 2),
                    '$' . number_format($purchase->amount ?? 0, 2),
                    ucfirst($purchase->status ?? 'unknown')
                ];
                
                // Write the row
                fputcsv($handle, $row);
            }
            
            // Close the file
            fclose($handle);
            
            // Set the download filename
            $downloadFilename = 'purchases_export_' . date('Y-m-d_His') . '.csv';
            
            // Return the file as a download response
            return response()->download($tempFile, $downloadFilename, [
                'Content-Type' => 'text/csv; charset=UTF-8'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('CSV export failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Redirect back with error message
            return back()->with('error', 'Failed to export data: ' . $e->getMessage());
        }
    }

    /**
     * Get sales data for reports.
     *
     * @param  string  $period
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  string  $groupFormat
     * @param  string  $labelFormat
     * @return array
     */
    private function getSalesData($period, $startDate, $endDate, $groupFormat, $labelFormat)
    {
        // Convert string dates to Carbon instances
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();
        
        // Get raw sales data
        $salesQuery = Purchase::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        if ($period === 'weekly') {
            // For weekly, we need a custom approach
            $rawData = $salesQuery->get()
                ->groupBy(function($date) {
                    // Group by year and week number
                    return Carbon::parse($date->created_at)->format('Y-W');
                })
                ->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total' => $group->sum('amount'),
                        'date' => Carbon::parse($group->first()->created_at)->startOfWeek()
                    ];
                });
                
            // Format for chart
            $labels = [];
            $salesCount = [];
            $salesAmount = [];
            
            foreach ($rawData as $weekData) {
                $labels[] = 'Week ' . $weekData['date']->format('W, Y');
                $salesCount[] = $weekData['count'];
                $salesAmount[] = $weekData['total'];
            }
        } else {
            // For daily, monthly, yearly
            $rawData = $salesQuery
                ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as date_group, 
                           COUNT(*) as count, 
                           SUM(amount) as total")
                ->groupBy('date_group')
                ->orderBy('date_group')
                ->get();
            
            // Format for chart
            $labels = [];
            $salesCount = [];
            $salesAmount = [];
            
            foreach ($rawData as $data) {
                if ($period === 'daily') {
                    $date = Carbon::createFromFormat('Y-m-d', $data->date_group);
                } elseif ($period === 'monthly') {
                    $parts = explode('-', $data->date_group);
                    $date = Carbon::createFromDate($parts[0], $parts[1], 1);
                } elseif ($period === 'yearly') {
                    $date = Carbon::createFromDate($data->date_group, 1, 1);
                }
                
                $labels[] = $date->format($labelFormat);
                $salesCount[] = $data->count;
                $salesAmount[] = $data->total;
            }
        }
        
        // Fill in gaps in data with zeros
        $completeLabels = [];
        $completeSalesCount = [];
        $completeSalesAmount = [];
        
        // Generate complete date range based on period
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            if ($period === 'daily') {
                $label = $currentDate->format($labelFormat);
                $currentDate->addDay();
            } elseif ($period === 'weekly') {
                $label = 'Week ' . $currentDate->format('W, Y');
                $currentDate->addWeek();
            } elseif ($period === 'monthly') {
                $label = $currentDate->format($labelFormat);
                $currentDate->addMonth();
            } elseif ($period === 'yearly') {
                $label = $currentDate->format($labelFormat);
                $currentDate->addYear();
            }
            
            $completeLabels[] = $label;
            
            $index = array_search($label, $labels);
            if ($index !== false) {
                $completeSalesCount[] = $salesCount[$index];
                $completeSalesAmount[] = $salesAmount[$index];
            } else {
                $completeSalesCount[] = 0;
                $completeSalesAmount[] = 0;
            }
        }
        
        return [
            'labels' => $completeLabels,
            'salesCount' => $completeSalesCount,
            'salesAmount' => $completeSalesAmount,
            'totalCount' => array_sum($completeSalesCount),
            'totalAmount' => array_sum($completeSalesAmount)
        ];
    }

    /**
     * Get top selling chapters.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getTopSellingChapters($startDate, $endDate)
    {
        return DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('chapters', 'purchase_items.chapter_id', '=', 'chapters.id')
            ->where('purchases.status', 'completed')
            ->where('purchase_items.item_type', 'chapter')
            ->whereBetween('purchases.created_at', [$startDate, $endDate])
            ->selectRaw('chapters.id, chapters.title, COUNT(*) as sales_count, SUM(purchase_items.price) as sales_amount')
            ->groupBy('chapters.id', 'chapters.title')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get top selling spells.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getTopSellingSpells($startDate, $endDate)
    {
        return DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('spells', 'purchase_items.spell_id', '=', 'spells.id')
            ->where('purchases.status', 'completed')
            ->where('purchase_items.item_type', 'spell')
            ->whereBetween('purchases.created_at', [$startDate, $endDate])
            ->selectRaw('spells.id, spells.title, COUNT(*) as sales_count, SUM(purchase_items.price) as sales_amount')
            ->groupBy('spells.id', 'spells.title')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get sales report data for AJAX requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesReportData(Request $request)
    {
        // Get report parameters
        $period = $request->input('period', 'monthly');
        $year = $request->input('year', Carbon::now()->year);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // Define date formats based on period
        $groupFormat = '%Y-%m';
        $labelFormat = 'M Y';
        
        if ($period === 'daily') {
            $groupFormat = '%Y-%m-%d';
            $labelFormat = 'M d, Y';
            
            // Default to last 30 days if no dates specified
            if (!$startDate) {
                $startDate = Carbon::now()->subDays(30)->toDateString();
                $endDate = Carbon::now()->toDateString();
            }
        } elseif ($period === 'weekly') {
            $groupFormat = '%Y-%u'; // ISO week numbers
            $labelFormat = 'Week %W, %Y';
            
            // Default to last 12 weeks if no dates specified
            if (!$startDate) {
                $startDate = Carbon::now()->subWeeks(12)->startOfWeek()->toDateString();
                $endDate = Carbon::now()->toDateString();
            }
        } elseif ($period === 'monthly') {
            $startDate = Carbon::createFromDate($year, 1, 1)->toDateString();
            $endDate = Carbon::createFromDate($year, 12, 31)->toDateString();
        } elseif ($period === 'yearly') {
            $groupFormat = '%Y';
            $labelFormat = 'Y';
            
            // Default to last 5 years if no dates specified
            if (!$startDate) {
                $startDate = Carbon::now()->subYears(5)->startOfYear()->toDateString();
                $endDate = Carbon::now()->endOfYear()->toDateString();
            }
        }
        
        // Get sales data
        $salesData = $this->getSalesData($period, $startDate, $endDate, $groupFormat, $labelFormat);
        
        // Get top selling chapters
        $topChapters = $this->getTopSellingChapters($startDate, $endDate);
        
        // Get top selling spells
        $topSpells = $this->getTopSellingSpells($startDate, $endDate);
        
        // Customer metrics
        $newCustomers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $returningCustomers = Purchase::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        // Return JSON response
        return response()->json([
            'chartData' => [
                'labels' => $salesData['labels'],
                'salesAmount' => $salesData['salesAmount'],
                'salesCount' => $salesData['salesCount']
            ],
            'stats' => [
                'totalAmount' => $salesData['totalAmount'],
                'totalCount' => $salesData['totalCount'],
                'newCustomers' => $newCustomers,
                'returningCustomers' => $returningCustomers
            ],
            'topChapters' => $topChapters,
            'topSpells' => $topSpells,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Get user analysis data for AJAX requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userAnalysisData(Request $request)
    {
        // Time period for analysis
        $period = $request->input('period', '30days');
        
        // Determine date range based on period
        $endDate = Carbon::now();
        
        if ($period === '30days') {
            $startDate = Carbon::now()->subDays(30);
        } elseif ($period === '90days') {
            $startDate = Carbon::now()->subDays(90);
        } elseif ($period === '6months') {
            $startDate = Carbon::now()->subMonths(6);
        } elseif ($period === '12months') {
            $startDate = Carbon::now()->subMonths(12);
        } else {
            $startDate = Carbon::now()->subDays(30); // Default
        }
        
        // New user registrations by day/week
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // User purchase behavior
        $userPurchases = Purchase::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('user_id, COUNT(*) as purchase_count, SUM(amount) as total_spent')
            ->groupBy('user_id')
            ->orderBy('total_spent', 'desc')
            ->limit(100)
            ->get();
        
        // Calculate average metrics
        $avgOrderValue = Purchase::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('amount');
        
        // Calculate purchase frequency
        $purchaseFrequency = DB::table(function($query) use ($startDate, $endDate) {
            $query->from('purchases')
                ->select('user_id', DB::raw('COUNT(*) as purchase_count'))
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('user_id');
        }, 'purchase_counts')->avg('purchase_count');
        
        // Calculate time between purchases (for users with multiple purchases)
        $repeatPurchaseDelay = DB::table(function($query) {
            $query->from('purchases as p1')
                ->join('purchases as p2', function($join) {
                    $join->on('p1.user_id', '=', 'p2.user_id')
                        ->whereRaw('p2.created_at > p1.created_at');
                })
                ->where('p1.status', 'completed')
                ->where('p2.status', 'completed')
                ->selectRaw('p1.id, MIN(DATEDIFF(p2.created_at, p1.created_at)) as days_between')
                ->groupBy('p1.id');
        }, 'purchase_delays')->avg('days_between');
        
        // Prepare top customers data
        $topCustomers = [];
        foreach ($userPurchases->take(10) as $purchase) {
            $user = User::find($purchase->user_id);
            if (!$user) continue;
            
            $topCustomers[] = [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->format('M d, Y'),
                'purchase_count' => $purchase->purchase_count,
                'total_spent' => $purchase->total_spent
            ];
        }
        
        // Calculate frequency distribution
        $onetime = $userPurchases->where('purchase_count', 1)->count();
        $occasional = $userPurchases->where('purchase_count', '>=', 2)
            ->where('purchase_count', '<=', 3)->count();
        $frequent = $userPurchases->where('purchase_count', '>=', 4)
            ->where('purchase_count', '<=', 6)->count();
        $loyal = $userPurchases->where('purchase_count', '>', 6)->count();
        
        $total = $onetime + $occasional + $frequent + $loyal;
        $onetimePercent = $total > 0 ? ($onetime / $total) * 100 : 0;
        $occasionalPercent = $total > 0 ? ($occasional / $total) * 100 : 0;
        $frequentPercent = $total > 0 ? ($frequent / $total) * 100 : 0;
        $loyalPercent = $total > 0 ? ($loyal / $total) * 100 : 0;
        
        // Calculate spending distribution
        $low = $userPurchases->where('total_spent', '<', 50)->count();
        $medium = $userPurchases->where('total_spent', '>=', 50)
            ->where('total_spent', '<', 100)->count();
        $high = $userPurchases->where('total_spent', '>=', 100)
            ->where('total_spent', '<', 200)->count();
        $vip = $userPurchases->where('total_spent', '>=', 200)->count();
        
        $totalSpenders = $low + $medium + $high + $vip;
        $lowPercent = $totalSpenders > 0 ? ($low / $totalSpenders) * 100 : 0;
        $mediumPercent = $totalSpenders > 0 ? ($medium / $totalSpenders) * 100 : 0;
        $highPercent = $totalSpenders > 0 ? ($high / $totalSpenders) * 100 : 0;
        $vipPercent = $totalSpenders > 0 ? ($vip / $totalSpenders) * 100 : 0;
        
        // Return JSON response
        return response()->json([
            'metrics' => [
                'avgOrderValue' => $avgOrderValue ?? 0,
                'purchaseFrequency' => $purchaseFrequency ?? 0,
                'repeatPurchaseDelay' => $repeatPurchaseDelay
            ],
            'newUsers' => $newUsers,
            'segments' => [
                'frequency' => [
                    'onetime' => $onetime,
                    'occasional' => $occasional,
                    'frequent' => $frequent,
                    'loyal' => $loyal,
                    'onetimePercent' => $onetimePercent,
                    'occasionalPercent' => $occasionalPercent,
                    'frequentPercent' => $frequentPercent,
                    'loyalPercent' => $loyalPercent
                ],
                'spending' => [
                    'low' => $low,
                    'medium' => $medium,
                    'high' => $high,
                    'vip' => $vip,
                    'lowPercent' => $lowPercent,
                    'mediumPercent' => $mediumPercent,
                    'highPercent' => $highPercent,
                    'vipPercent' => $vipPercent
                ]
            ],
            'topCustomers' => $topCustomers,
            'period' => $period
        ]);
    }
}