<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Form Submission</title>
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
        
        .message-details {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #4b0082;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            text-align: center;
            color: #666;
        }
        
        .field-label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #4b0082;
        }
        
        .field-value {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Master Magical Key to the Universe</h1>
        <div>New Contact Form Submission</div>
    </div>
    
    <div class="content">
        <p>You have received a new message from the contact form on your website.</p>
        
        <div class="message-details">
            <div class="field-label">Name:</div>
            <div class="field-value">{{ $data['name'] }}</div>
            
            <div class="field-label">Email:</div>
            <div class="field-value">{{ $data['email'] }}</div>
            
            <div class="field-label">Subject:</div>
            <div class="field-value">{{ $data['subject'] }}</div>
            
            <div class="field-label">Message:</div>
            <div class="field-value">{{ $data['message'] }}</div>
        </div>
        
        <p>To respond to this inquiry, you can reply directly to this email.</p>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Master Magical Key to the Universe. All rights reserved.</p>
            <p>This is an automated email sent from your website's contact form.</p>
        </div>
    </div>
</body>
</html>