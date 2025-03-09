<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $purchase->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .header h1 {
            color: #4b0082;
            font-size: 28px;
            margin: 0 0 5px 0;
        }
        
        .header p {
            color: #666;
            margin: 0;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        
        .invoice-to {
            width: 50%;
        }
        
        .invoice-info {
            width: 45%;
            text-align: right;
        }
        
        .invoice-to h2, .invoice-info h2 {
            font-size: 18px;
            color: #4b0082;
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        th {
            background-color: #f8f8f8;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
        }
        
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-section {
            margin-top: 30px;
            margin-left: auto;
            width: 300px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        
        .total-row.final {
            font-weight: bold;
            font-size: 18px;
            padding-top: 10px;
            margin-top: 10px;
            border-top: 2px solid #ddd;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .mystical-accent {
            border-left: 4px solid #4b0082;
            padding-left: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <h1>Master Magical Key to the Universe</h1>
            <p>Invoice #{{ $purchase->invoice_number }}</p>
        </div>
        
        <div class="invoice-details">
            <div class="invoice-to">
                <h2>Billed To:</h2>
                <p>{{ $user->name }}<br>
                {{ $user->email }}</p>
            </div>
            
            <div class="invoice-info">
                <h2>Invoice Information:</h2>
                <p><strong>Date:</strong> {{ $purchase->created_at->format('F j, Y') }}<br>
                <strong>Transaction ID:</strong> {{ $purchase->transaction_id }}</p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>${{ number_format($item['price'], 2) }}</td>
                        <td class="text-right">${{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="total-section">
            <div class="total-row">
                <div>Subtotal:</div>
                <div>${{ number_format($purchase->subtotal, 2) }}</div>
            </div>
            <div class="total-row">
                <div>GST (10%):</div>
                <div>${{ number_format($purchase->tax, 2) }}</div>
            </div>
            <div class="total-row final">
                <div>Total:</div>
                <div>${{ number_format($purchase->amount, 2) }} {{ $purchase->currency }}</div>
            </div>
        </div>
        
        <div class="footer">
            <p class="mystical-accent">Thank you for your purchase. Your mystical journey awaits!</p>
            <p>For support, please contact: support@mastermasticalkey.com</p>
            <p>&copy; {{ date('Y') }} Master Magical Key to the Universe. All rights reserved.</p>
        </div>
    </div>
</body>
</html>