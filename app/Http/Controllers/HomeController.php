<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get published chapters ordered by their order field
        $chapters = Chapter::where('is_published', true)
            ->orderBy('order')
            ->get();
        
        return view('home', compact('chapters'));
    }

    public function about()
    {
        return view('about');
    }
    
    public function contact()
    {
        return view('contact');
    }
    
    public function submitContact(Request $request)
    {
        // Handle contact form submission
        return redirect()->route('contact')->with('success', 'Message sent successfully!');
    }
    
    public function faq()
    {
        return view('faq');
    }
}
