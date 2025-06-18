<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GmailApiService
{
    private $client;
    private $gmail;
    
    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Master Magical Key Email Service');
        $this->client->setScopes([Gmail::GMAIL_SEND]);
        $this->client->setAuthConfig(storage_path('app/google-credentials.json'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        
        // Load existing token if available
        $tokenPath = storage_path('app/gmail-token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }
        
        // If there is no previous token or it's expired
        if ($this->client->isAccessTokenExpired()) {
            // Refresh the token if possible
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
            }
        }
        
        $this->gmail = new Gmail($this->client);
    }
    
    /**
     * Send an email using Gmail API
     *
     * @param string $to
     * @param string $subject
     * @param string $htmlBody
     * @param string $textBody
     * @param string $fromEmail
     * @param string $fromName
     * @return bool
     */
    public function sendEmail($to, $subject, $htmlBody, $textBody = null, $fromEmail = null, $fromName = null)
    {
        try {
            $fromEmail = $fromEmail ?: config('mail.from.address');
            $fromName = $fromName ?: config('mail.from.name');
            
            // Create the email message
            $rawMessage = $this->createRawMessage($to, $subject, $htmlBody, $textBody, $fromEmail, $fromName);
            
            // Create Gmail message object
            $message = new Message();
            $message->setRaw($rawMessage);
            
            // Send the message
            $result = $this->gmail->users_messages->send('me', $message);
            
            Log::info('Email sent via Gmail API', [
                'to' => $to,
                'subject' => $subject,
                'message_id' => $result->getId()
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Gmail API send failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Create a raw email message for Gmail API
     */
    private function createRawMessage($to, $subject, $htmlBody, $textBody, $fromEmail, $fromName)
    {
        $boundary = uniqid(rand(), true);
        
        $headers = [
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'To: ' . $to,
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"'
        ];
        
        $message = implode("\r\n", $headers) . "\r\n\r\n";
        
        // Add text version if provided
        if ($textBody) {
            $message .= '--' . $boundary . "\r\n";
            $message .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
            $message .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
            $message .= quoted_printable_encode($textBody) . "\r\n\r\n";
        }
        
        // Add HTML version
        $message .= '--' . $boundary . "\r\n";
        $message .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
        $message .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $message .= quoted_printable_encode($htmlBody) . "\r\n\r\n";
        
        $message .= '--' . $boundary . '--';
        
        return base64url_encode($message);
    }
    
    /**
     * Check if Gmail API is properly configured
     */
    public function isConfigured()
    {
        try {
            $credentialsPath = storage_path('app/google-credentials.json');
            $tokenPath = storage_path('app/gmail-token.json');
            
            return file_exists($credentialsPath) && file_exists($tokenPath) && !$this->client->isAccessTokenExpired();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get authorization URL for initial setup
     */
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function exchangeAuthCode($authCode)
    {
        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
            
            if (array_key_exists('error', $accessToken)) {
                throw new \Exception($accessToken['error_description']);
            }
            
            // Save the token
            $tokenPath = storage_path('app/gmail-token.json');
            file_put_contents($tokenPath, json_encode($accessToken));
            
            return true;
        } catch (\Exception $e) {
            Log::error('Gmail auth code exchange failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

/**
 * Helper function for base64url encoding
 */
function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}