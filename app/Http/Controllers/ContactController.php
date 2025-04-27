<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormSubmission;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Show the contact form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('contact');
    }
    
    /**
     * Process the contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        // Validate form input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        try {
            // Send email
            Mail::to('support@mastermagicalkey.com')->send(new ContactFormSubmission($validated));
            
            // Log successful submission
            Log::info('Contact form submitted', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject']
            ]);
            
            // Redirect with success message
            return redirect()->route('contact')->with('success', 'Thank you for your message! We will get back to you soon.');
            
        } catch (\Exception $e) {
            // Log error
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'name' => $validated['name'],
                'email' => $validated['email']
            ]);
            
            // Redirect with error message
            return redirect()->route('contact')
                ->withInput()
                ->with('error', 'There was a problem sending your message. Please try again later.');
        }
    }
}
