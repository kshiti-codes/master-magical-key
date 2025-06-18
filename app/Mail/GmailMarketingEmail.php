<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\User;
use App\Services\GmailApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GmailMarketingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $campaign;
    public $user;
    private $gmailService;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailCampaign $campaign, User $user)
    {
        $this->campaign = $campaign;
        $this->user = $user;
        $this->gmailService = new GmailApiService();
    }

    /**
     * Send the email using Gmail API
     */
    public function sendViaGmail()
    {
        try {
            // Process the content
            $htmlContent = $this->processContentForEmail($this->campaign->content);
            $textContent = $this->convertHtmlToText($htmlContent);
            
            // Send via Gmail API
            $success = $this->gmailService->sendEmail(
                $this->user->email,
                $this->campaign->subject,
                $htmlContent,
                $textContent,
                config('mail.from.address'),
                config('mail.from.name')
            );
            
            if ($success) {
                Log::info('Marketing email sent successfully via Gmail API', [
                    'campaign_id' => $this->campaign->id,
                    'user_id' => $this->user->id,
                    'user_email' => $this->user->email
                ]);
            }
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error('Failed to send marketing email via Gmail API', [
                'campaign_id' => $this->campaign->id,
                'user_id' => $this->user->id,
                'user_email' => $this->user->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get the message envelope (for fallback SMTP).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    /**
     * Get the message content definition (for fallback SMTP).
     */
    public function content(): Content
    {
        $processedContent = $this->processContentForEmail($this->campaign->content);
        
        return new Content(
            html: 'emails.marketing',
            with: [
                'content' => $processedContent,
                'userName' => $this->user->name,
            ],
        );
    }
    
    /**
     * Process content to replace variables with user data and fix image URLs
     */
    private function parseContent($content)
    {
        $replacements = [
            '{{name}}' => $this->user->name,
            '{{email}}' => $this->user->email,
            '{{first_name}}' => explode(' ', $this->user->name)[0] ?? $this->user->name,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
    
    /**
     * Process HTML content to make it email-friendly
     */
    private function processContentForEmail($content)
    {
        // First replace user variables
        $content = $this->parseContent($content);
        
        // Fix image URLs - convert relative to absolute
        $appUrl = config('app.url');
        $content = preg_replace('/<img src=["\']\/([^"\']+)["\']/', '<img src="' . $appUrl . '/$1"', $content);
        
        // Fix image URLs that don't have a leading slash
        $content = preg_replace('/<img src=["\']((?!http|https|data:)[^"\'\/][^"\']+)["\']/', '<img src="' . $appUrl . '/$1"', $content);
        
        // Convert buttons to email-friendly buttons
        $content = preg_replace_callback('/<button[^>]*>(.*?)<\/button>/', function($matches) {
            $buttonText = strip_tags($matches[1]);
            return $this->createEmailButton($buttonText, '#');
        }, $content);
        
        // Convert anchor tags with button classes to email-friendly buttons
        $content = preg_replace_callback('/<a[^>]*class=["\'].*?btn.*?["\'][^>]*>(.*?)<\/a>/', function($matches) {
            // Extract href
            preg_match('/href=["\']([^"\']+)["\']/', $matches[0], $hrefMatch);
            $href = isset($hrefMatch[1]) ? $hrefMatch[1] : '#';
            
            // Extract text
            $buttonText = strip_tags($matches[1]);
            
            return $this->createEmailButton($buttonText, $href);
        }, $content);
        
        // Wrap content in email template
        return $this->wrapInEmailTemplate($content);
    }

    /**
     * Create an email-friendly button
     */
    private function createEmailButton($text, $url)
    {
        return '
        <div style="margin-top: 15px; margin-bottom: 15px; text-align: center;">
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . $url . '" style="height:40px;v-text-anchor:middle;width:200px;" arcsize="10%" stroke="f" fillcolor="#4b0082">
                <w:anchorlock/>
                <center>
            <![endif]-->
            <a href="' . $url . '" 
            style="background-color:#4b0082;background-image:linear-gradient(to right, #4b0082, #9400d3);border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:14px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">' . $text . '</a>
            <!--[if mso]>
                </center>
            </v:roundrect>
            <![endif]-->
        </div>';
    }
    
    /**
     * Wrap content in a responsive email template
     */
    private function wrapInEmailTemplate($content)
    {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $this->campaign->subject . '</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px;
                    background-color: #f4f4f4;
                }
                .email-container {
                    background-color: #ffffff;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .header {
                    text-align: center;
                    border-bottom: 2px solid #4b0082;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .header h1 {
                    color: #4b0082;
                    margin: 0;
                    font-family: "Cinzel", serif;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    color: #666;
                    font-size: 12px;
                }
                a { color: #4b0082; }
                img { max-width: 100%; height: auto; }
                @media (max-width: 600px) {
                    body { padding: 10px; }
                    .email-container { padding: 20px; }
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h1>MASTER MAGICAL KEY</h1>
                </div>
                
                <div class="content">
                    <p>Hello ' . $this->user->name . ',</p>
                    ' . $content . '
                </div>
                
                <div class="footer">
                    <p>Best regards,<br>The Master Magical Key Team</p>
                    <p>
                        <a href="' . config('app.url') . '">Visit our website</a> | 
                        <a href="' . config('app.url') . '/unsubscribe">Unsubscribe</a>
                    </p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Convert HTML content to plain text for text version
     */
    private function convertHtmlToText($html)
    {
        // Strip HTML tags and decode entities
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        
        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
}