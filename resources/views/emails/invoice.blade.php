<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Invoice</title>
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
        
        .invoice-details {
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Master Magical Key to the Universe</h1>
    </div>
    
    <div class="content">
        <h2>Thank You for Your Purchase!</h2>
        
        <p>Dear {{ $user->name }},</p>
        
        <p>Thank you for your purchase. Your invoice is attached to this email as a PDF file.</p>
        
        <div class="invoice-details">
            <p><strong>Invoice #:</strong> {{ $purchase->invoice_number }}</p>
            <p><strong>Date:</strong> {{ $purchase->created_at->format('F j, Y') }}</p>
            <p><strong>Amount:</strong> ${{ number_format($purchase->amount, 2) }} {{ $purchase->currency }}</p>
        </div>
        
        <p>Your purchased chapters are now available in your digital book. We hope they bring you wisdom and enlightenment on your journey.</p>
        
        <div style="text-align: center;">
            <a href="{{ route('home') }}" class="button">Access Your Digital Book</a>
        </div>
        
        <div class="footer">
            <p>If you have any questions about your purchase, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} Master Magical Key to the Universe. All rights reserved.</p>
        </div>
    </div>
</body>
</html>