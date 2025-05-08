<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Session Booking</title>
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
        
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            text-align: center;
            color: #666;
        }
        
        .alert {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Session Booking</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $bookedSession->coach->name }},</p>
        
        <p>You have a new session booking that requires your confirmation. Please review the session details below:</p>
        
        <div class="session-details">
            <p><strong>Client:</strong> {{ $bookedSession->user->name }}</p>
            <p><strong>Session Type:</strong> {{ $bookedSession->sessionType->name }}</p>
            <p><strong>Date & Time:</strong> {{ $bookedSession->formatted_session_time }}</p>
            <p><strong>Duration:</strong> {{ $bookedSession->duration }} minutes</p>
            <p><strong>Amount Paid:</strong> ${{ number_format($bookedSession->amount_paid, 2) }} {{ $bookedSession->sessionType->currency }}</p>
        </div>
        
        <div class="alert">
            <p><strong>Action Required:</strong> Please log in to your dashboard to confirm this session. After confirmation, you'll need to provide a meeting link for this session.</p>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ route('admin.booked-sessions.show', $bookedSession->id) }}" class="button">View Session Details</a>
        </div>
        
        <div class="footer">
            <p>If you have any questions, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} Master Magical Key to the Universe. All rights reserved.</p>
        </div>
    </div>
</body>
</html>