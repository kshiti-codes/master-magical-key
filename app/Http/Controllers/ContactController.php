<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactFormSubmission;
use App\Services\GmailApiService;

class ContactController extends Controller
{
    protected $gmailService;
    
    public function __construct()
    {
        // Initialize Gmail service safely
        try {
            $this->gmailService = new GmailApiService();
        } catch (\Exception $e) {
            Log::warning('Gmail service initialization failed in ContactController', [
                'error' => $e->getMessage()
            ]);
            $this->gmailService = null;
        }
    }
    
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
            $success = false;
            
            // Try Gmail API first if available
            if ($this->gmailService && $this->gmailService->isConfigured()) {
                $success = $this->sendViaGmailApi($validated);
                
                if ($success) {
                    Log::info('Contact form sent via Gmail API', [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'subject' => $validated['subject']
                    ]);
                }
            }
            
            // Fallback to regular Mail (SMTP) if Gmail API failed or unavailable
            if (!$success) {
                try {
                    Mail::to(config('mail.from.address', 'support@mastermagicalkey.com'))
                        ->send(new ContactFormSubmission($validated));
                    
                    $success = true;
                    
                    Log::info('Contact form sent via SMTP fallback', [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'subject' => $validated['subject']
                    ]);
                } catch (\Exception $e) {
                    Log::error('Contact form SMTP fallback failed', [
                        'error' => $e->getMessage(),
                        'name' => $validated['name'],
                        'email' => $validated['email']
                    ]);
                }
            }
            
            if ($success) {
                // Redirect with success message
                return redirect()->route('contact')->with('success', 'Thank you for your message! We will get back to you soon.');
            } else {
                throw new \Exception('All email sending methods failed');
            }
            
        } catch (\Exception $e) {
            // Log error
            Log::error('Contact form submission failed completely', [
                'error' => $e->getMessage(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'gmail_available' => $this->gmailService ? $this->gmailService->isAvailable() : false,
                'gmail_configured' => $this->gmailService ? $this->gmailService->isConfigured() : false
            ]);
            
            // Redirect with error message
            return redirect()->route('contact')
                ->withInput()
                ->with('error', 'There was a problem sending your message. Please try again later or contact us directly at ' . config('mail.from.address', 'support@mastermagicalkey.com'));
        }
    }
    
    /**
     * Send contact form via Gmail API
     */
    private function sendViaGmailApi($data)
    {
        try {
            $toEmail = config('mail.from.address', 'support@mastermagicalkey.com');
            $subject = 'New Contact Form Submission: ' . $data['subject'];
            
            // Create HTML email content
            $htmlContent = $this->createContactEmailHtml($data);
            
            // Create text version
            $textContent = $this->createContactEmailText($data);
            
            // Send via Gmail API
            $success = $this->gmailService->sendEmail(
                $toEmail,
                $subject,
                $htmlContent,
                $textContent,
                $data['email'], // From the user's email
                $data['name']   // From the user's name
            );
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error('Gmail API contact form send failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }
    
    /**
     * Create HTML email content for contact form
     */
    private function createContactEmailHtml($data)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Contact Form Submission</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4b0082; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #4b0082; }
                .message-box { background: white; padding: 15px; border-left: 4px solid #4b0082; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>New Contact Form Submission</h1>
                </div>
                <div class="content">
                    <div class="field">
                        <div class="label">From:</div>
                        <div>' . htmlspecialchars($data['name']) . ' (' . htmlspecialchars($data['email']) . ')</div>
                    </div>
                    
                    <div class="field">
                        <div class="label">Subject:</div>
                        <div>' . htmlspecialchars($data['subject']) . '</div>
                    </div>
                    
                    <div class="field">
                        <div class="label">Message:</div>
                        <div class="message-box">' . nl2br(htmlspecialchars($data['message'])) . '</div>
                    </div>
                    
                    <div class="field">
                        <div class="label">Submitted:</div>
                        <div>' . now()->format('F j, Y \a\t g:i A T') . '</div>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Create text email content for contact form
     */
    private function createContactEmailText($data)
    {
        return "NEW CONTACT FORM SUBMISSION\n\n" .
               "From: " . $data['name'] . " (" . $data['email'] . ")\n" .
               "Subject: " . $data['subject'] . "\n" .
               "Submitted: " . now()->format('F j, Y \a\t g:i A T') . "\n\n" .
               "Message:\n" . $data['message'] . "\n\n" .
               "---\n" .
               "This message was sent via the contact form on Master Magical Key website.";
    }
}