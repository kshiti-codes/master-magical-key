<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\User;
use App\Services\GmailApiService;
use App\Mail\GmailMarketingEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailCampaignController extends Controller
{
    private $gmailService;
    
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->gmailService = new GmailApiService();
    }

    /**
     * Display a listing of email campaigns
     */
    public function index()
    {
        $campaigns = EmailCampaign::orderBy('created_at', 'desc')->get();
        $gmailConfigured = $this->gmailService->isConfigured();
        
        return view('admin.email-campaigns.index', compact('campaigns', 'gmailConfigured'));
    }

    /**
     * Show the form for creating a new campaign
     */
    public function create()
    {
        // Get segment options for the dropdown
        $segments = $this->getSegmentOptions();
        
        return view('admin.email-campaigns.create', compact('segments'));
    }

    /**
     * Store a newly created campaign
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'segment' => 'nullable|string',
        ]);

        $campaign = EmailCampaign::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'segment_conditions' => $validated['segment'] ?? null,
            'status' => 'draft'
        ]);

        return redirect()->route('admin.email-campaigns.index')
            ->with('success', 'Email campaign created successfully');
    }

    /**
     * Display the specified email campaign
     */
    public function show(EmailCampaign $emailCampaign)
    {
        // Get recipients with pagination if campaign has been sent
        $recipients = collect([]);
        $stats = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0
        ];
        
        if ($emailCampaign->status === 'sent') {
            // Get logs with pagination
            $recipients = DB::table('email_campaign_logs')
                ->where('email_campaign_id', $emailCampaign->id)
                ->join('users', 'email_campaign_logs.user_id', '=', 'users.id')
                ->select('email_campaign_logs.*', 'users.name')
                ->paginate(15);
            
            // Get stats
            $total = DB::table('email_campaign_logs')
                ->where('email_campaign_id', $emailCampaign->id)
                ->count();
            
            $sent = DB::table('email_campaign_logs')
                ->where('email_campaign_id', $emailCampaign->id)
                ->where('sent', true)
                ->count();
            
            $failed = $total - $sent;
            
            $stats = [
                'total' => $total,
                'sent' => $sent,
                'failed' => $failed
            ];
        }
        
        return view('admin.email-campaigns.view', [
            'campaign' => $emailCampaign,
            'recipients' => $recipients,
            'stats' => $stats
        ]);
    }

    /**
     * Show the form for editing a campaign
     */
    public function edit(EmailCampaign $emailCampaign)
    {
        // Can only edit draft campaigns
        if ($emailCampaign->status !== 'draft') {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'Only draft campaigns can be edited');
        }

        // Get segment options for the dropdown
        $segments = $this->getSegmentOptions();
        
        return view('admin.email-campaigns.edit', [
            'campaign' => $emailCampaign,
            'segments' => $segments
        ]);
    }

    /**
     * Update the campaign
     */
    public function update(Request $request, EmailCampaign $emailCampaign)
    {
        // Can only update draft campaigns
        if ($emailCampaign->status !== 'draft') {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'Only draft campaigns can be edited');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'segment' => 'nullable|string',
        ]);

        $emailCampaign->update([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'segment_conditions' => $validated['segment'] ?? null,
        ]);

        return redirect()->route('admin.email-campaigns.index')
            ->with('success', 'Email campaign updated successfully');
    }

    /**
     * Show send confirmation
     */
    public function showSendConfirmation(EmailCampaign $emailCampaign)
    {
        // Can only send draft campaigns
        if ($emailCampaign->status !== 'draft') {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'This campaign has already been sent');
        }

        // Check if Gmail API is configured
        if (!$this->gmailService->isConfigured()) {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'Gmail API is not configured. Please configure it first.');
        }

        // Get recipient count based on segment
        $recipientCount = $this->getRecipientCount($emailCampaign->segment_conditions);

        return view('admin.email-campaigns.send', [
            'campaign' => $emailCampaign,
            'recipientCount' => $recipientCount
        ]);
    }

    /**
     * Send the campaign
     */
    public function send(EmailCampaign $emailCampaign)
    {
        // Can only send draft campaigns
        if ($emailCampaign->status !== 'draft') {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'This campaign has already been sent');
        }

        // Check if Gmail API is configured
        if (!$this->gmailService->isConfigured()) {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'Gmail API is not configured. Please configure it first.');
        }

        // Get recipients based on segment
        $recipients = $this->getRecipients($emailCampaign->segment_conditions);
        $totalRecipients = count($recipients);

        if ($totalRecipients === 0) {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'No recipients found for this campaign');
        }

        // Update campaign status
        $emailCampaign->update([
            'status' => 'sending',
            'total_recipients' => $totalRecipients
        ]);

        $sentCount = 0;
        $failedCount = 0;

        // Send emails using Gmail API
        foreach ($recipients as $recipient) {
            try {
                // Create log entry
                DB::table('email_campaign_logs')->insert([
                    'email_campaign_id' => $emailCampaign->id,
                    'user_id' => $recipient->id,
                    'email' => $recipient->email,
                    'sent' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Create marketing email instance
                $marketingEmail = new GmailMarketingEmail($emailCampaign, $recipient);
                
                // Send via Gmail API
                $success = $marketingEmail->sendViaGmail();
                
                if ($success) {
                    $sentCount++;
                    
                    // Update log
                    DB::table('email_campaign_logs')
                        ->where('email_campaign_id', $emailCampaign->id)
                        ->where('user_id', $recipient->id)
                        ->update([
                            'sent' => true,
                            'sent_at' => now()
                        ]);
                } else {
                    $failedCount++;
                    Log::warning('Failed to send campaign email via Gmail API', [
                        'campaign_id' => $emailCampaign->id,
                        'user_id' => $recipient->id,
                        'email' => $recipient->email
                    ]);
                }
                
                // Add a small delay to avoid rate limits
                usleep(100000); // 0.1 second delay
                
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Exception sending campaign email', [
                    'campaign_id' => $emailCampaign->id,
                    'user_id' => $recipient->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Mark campaign as sent
        $emailCampaign->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);

        $message = "Email campaign sent! {$sentCount} emails sent successfully";
        if ($failedCount > 0) {
            $message .= ", {$failedCount} failed.";
        }

        return redirect()->route('admin.email-campaigns.index')
            ->with('success', $message);
    }

    /**
     * Configure Gmail API
     */
    public function configureGmail()
    {
        $isConfigured = $this->gmailService->isConfigured();
        $authUrl = $isConfigured ? null : $this->gmailService->getAuthUrl();
        
        return view('admin.email-campaigns.gmail-config', [
            'authUrl' => $authUrl,
            'isConfigured' => $isConfigured,
            'gmailConfigured' => $isConfigured // Add this line to fix the error
        ]);
    }

    /**
     * Handle Gmail OAuth callback
     */
    public function handleGmailCallback(Request $request)
    {
        $authCode = $request->input('code');
        
        if (!$authCode) {
            return redirect()->route('admin.email-campaigns.configure-gmail')
                ->with('error', 'Authorization code not received');
        }

        $success = $this->gmailService->exchangeAuthCode($authCode);
        
        if ($success) {
            return redirect()->route('admin.email-campaigns.index')
                ->with('success', 'Gmail API configured successfully!');
        } else {
            return redirect()->route('admin.email-campaigns.configure-gmail')
                ->with('error', 'Failed to configure Gmail API');
        }
    }

    /**
     * Get all available segment options
     */
    private function getSegmentOptions()
    {
        return [
            '' => 'All Users',
            'has_purchases' => 'Users Who Made Purchases',
            'no_purchases' => 'Users With No Purchases',
            'active_subscribers' => 'Active Subscribers',
            'expired_subscribers' => 'Expired Subscribers',
            'lifetime_subscribers' => 'Lifetime Subscribers',
            'non_subscribers' => 'Non-Subscribers',
            'free_content_only' => 'Users With Only Free Content',
            'recent_signup' => 'Recent Signups (Last 7 Days)',
            'chapter_owners' => 'Chapter Owners',
            'spell_owners' => 'Spell Owners',
        ];
    }

    /**
     * Get recipient count based on segment
     */
    private function getRecipientCount($segmentConditions)
    {
        $query = User::query();
        
        if ($segmentConditions) {
            $this->applySegmentConditions($query, $segmentConditions);
        }
        
        return $query->count();
    }

    /**
     * Get recipients based on segment
     */
    private function getRecipients($segmentConditions)
    {
        $query = User::query();
        
        if ($segmentConditions) {
            $this->applySegmentConditions($query, $segmentConditions);
        }
        
        return $query->get();
    }
    
    /**
     * Apply segment conditions to query
     */
    private function applySegmentConditions($query, $segmentConditions)
    {
        switch ($segmentConditions) {
            case 'has_purchases':
                $query->whereHas('purchases', function($q) {
                    $q->where('status', 'completed');
                });
                break;
                
            case 'no_purchases':
                $query->whereDoesntHave('purchases', function($q) {
                    $q->where('status', 'completed');
                });
                break;
                
            case 'active_subscribers':
                $query->whereHas('subscriptions', function($q) {
                    $q->where('status', 'active');
                });
                break;
                
            case 'expired_subscribers':
                $query->whereHas('subscriptions', function($q) {
                    $q->where('status', 'expired');
                });
                break;
                
            case 'lifetime_subscribers':
                $query->whereHas('subscriptions', function($q) {
                    $q->where('status', 'active')
                      ->whereHas('plan', function($planQuery) {
                          $planQuery->where('is_lifetime', true);
                      });
                });
                break;
                
            case 'non_subscribers':
                $query->whereDoesntHave('subscriptions', function($q) {
                    $q->where('status', 'active');
                });
                break;
                
            case 'free_content_only':
                // Users who have only accessed free content
                $query->whereDoesntHave('purchases')
                      ->whereHas('chapters', function($q) {
                          $q->where('is_free', true);
                      });
                break;
                
            case 'recent_signup':
                // Users who signed up in the last 7 days
                $sevenDaysAgo = Carbon::now()->subDays(7);
                $query->where('created_at', '>=', $sevenDaysAgo);
                break;
                
            case 'chapter_owners':
                $query->whereHas('chapters');
                break;
                
            case 'spell_owners':
                $query->whereHas('spells');
                break;
        }
        
        return $query;
    }
}