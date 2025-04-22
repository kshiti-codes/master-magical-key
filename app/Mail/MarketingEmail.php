<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $campaign;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailCampaign $campaign, User $user)
    {
        $this->campaign = $campaign;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Process the content to fix image URLs and prepare for email
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
        
        return $content;
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
}