<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Session Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(to right, #4b0082, #9400d3);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        
        .content {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        
        .session-details {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #4b0082;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(to right, #4b0082, #9400d3);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .info-box {
            background-color: #e0f7fa;
            border-left: 4px solid #00bcd4;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            text-align: center;
            color: #666;
        }
        
        .important-notes {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 25px;
        }
        
        .important-notes h3 {
            margin-top: 0;
            color: #4b0082;
        }
        
        .important-notes ul {
            padding-left: 20px;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Booking Confirmation</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $bookedSession->user->name }},</p>
        
        <p>Thank you for booking a session with us! Your booking is confirmed and is now awaiting approval from your coach. Here are the details of your upcoming session:</p>
        
        <div class="session-details">
            <p><strong>Session Type:</strong> {{ $bookedSession->sessionType->name }}</p>
            <p><strong>Coach:</strong> {{ $bookedSession->coach->name }}</p>
            <p><strong>Date & Time:</strong> {{ $bookedSession->formatted_session_time }}</p>
            <p><strong>Duration:</strong> {{ $bookedSession->duration }} minutes</p>
            <p><strong>Amount Paid:</strong> ${{ number_format($bookedSession->amount_paid, 2) }} {{ $bookedSession->sessionType->currency }}</p>
            <p><strong>Status:</strong> Pending Coach Confirmation</p>
        </div>
        
        <div class="info-box">
            <p><strong>About Your Meeting Link:</strong> Your coach will create a meeting link for your session. You can view this link by visiting your session details page once it becomes available (typically 24-48 hours before your session).</p>
            <p>You'll be able to join the session 10 minutes before the scheduled start time.</p>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ route('sessions.show', $bookedSession->id) }}" class="button">View Session Details</a>
        </div>
        
        <div class="important-notes">
            <h3>Important Notes</h3>
            <ul>
                <li>Please ensure your camera and microphone are working before joining the session.</li>
                <li>Find a quiet place with good internet connection for your session.</li>
                <li>If you need to reschedule, please contact us as soon as possible.</li>
                <li>Client-initiated cancellations are not eligible for refunds.</li>
            </ul>
        </div>
        
        <div class="footer">
            <p>If you have any questions about your session, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} Master Magical Key to the Universe. All rights reserved.</p>
        </div>
    </div>
</body>
</html>