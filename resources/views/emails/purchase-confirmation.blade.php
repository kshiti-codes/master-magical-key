<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Confirmation</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #4b0082;
            margin-bottom: 5px;
        }
        
        .message {
            background-color: #f9f9f9;
            border-left: 4px solid #4b0082;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .purchase-details {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .purchase-details h3 {
            margin-top: 0;
            color: #4b0082;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f0f0f0;
        }
        
        .total {
            font-weight: bold;
            color: #4b0082;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
        }
        
        .button {
            display: inline-block;
            background-color: #4b0082;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Master Magical Key to the Universe</h1>
        <p>Thank you for your purchase!</p>
    </div>
    
    <div class="message">
        <p>Dear {{ $purchase->user->name }},</p>
        <p>Thank you for your purchase. Your mystical journey awaits! Your chapter(s) are now available in your digital book.</p>
    </div>
    
    <div class="purchase-details">
        <h3>Purchase Details</h3>
        <p><strong>Invoice #:</strong> {{ $purchase->invoice_number }}</p>
        <p><strong>Date:</strong> {{ $purchase->created_at->format('F j, Y') }}</p>
        <p><strong>Transaction ID:</strong> {{ $purchase->transaction_id }}</p>
        
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($purchase->invoice_data) && !empty($purchase->invoice_data['items']))
                    @foreach($purchase->invoice_data['items'] as $item)
                        <tr>
                            <td>Chapter {{ $item['chapter_id'] }}: {{ $item['title'] }}</td>
                            <td>${{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>Chapter {{ $purchase->chapter->id }}: {{ $purchase->chapter->title }}</td>
                        <td>${{ number_format($purchase->amount / 1.1, 2) }}</td>
                    </tr>
                @endif
                
                <tr>
                    <td>GST (10%)</td>
                    <td>${{ number_format($purchase->tax ?? ($purchase->amount - $purchase->amount / 1.1), 2) }}</td>
                </tr>
                
                <tr class="total">
                    <td>Total</td>
                    <td>${{ number_format($purchase->amount, 2) }} {{ $purchase->currency }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div style="text-align: center;">
        <a href="{{ route('home') }}" class="button">Go to Digital Book</a>
    </div>
    
    <div class="footer">
        <p>Your invoice is attached to this email. For any questions, please contact our support team.</p>
        <p>&copy; {{ date('Y') }} Master Magical Key to the Universe. All rights reserved.</p>
    </div>
</body>
</html>