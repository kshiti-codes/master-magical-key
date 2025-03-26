<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $purchase->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #4b0082;
            padding-bottom: 20px;
        }
        .invoice-header h1 {
            color: #4b0082;
            margin: 0;
            font-size: 28px;
        }
        .invoice-header .subtitle {
            font-size: 16px;
            color: #666;
            margin-top: 5px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .invoice-info-block {
            width: 48%;
        }
        .invoice-info h2 {
            font-size: 16px;
            color: #4b0082;
            margin-top: 0;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .invoice-table th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #ddd;
        }
        .invoice-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .invoice-table .text-right {
            text-align: right;
        }
        .invoice-table .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .invoice-table .total-row td {
            border-top: 2px solid #ddd;
        }
        .invoice-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .thank-you {
            margin-top: 30px;
            text-align: center;
            font-size: 18px;
            color: #4b0082;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="invoice-header">
            <h1>MASTER MAGICAL KEY</h1>
            <div class="subtitle">TO THE UNIVERSE</div>
            <div style="margin-top: 15px;">INVOICE</div>
        </div>
        
        <div class="invoice-info">
            <div class="invoice-info-block">
                <h2>Billed To:</h2>
                <p>
                    {{ $user->name }}<br>
                    {{ $user->email }}
                </p>
            </div>
            
            <div class="invoice-info-block">
                <h2>Invoice Details:</h2>
                <p>
                    <strong>Invoice Number:</strong> {{ $purchase->invoice_number }}<br>
                    <strong>Date:</strong> {{ $purchase->created_at->format('F j, Y') }}<br>
                    <strong>Transaction ID:</strong> {{ $purchase->transaction_id }}<br>
                    <strong>Status:</strong> {{ ucfirst($purchase->status) }}
                </p>
            </div>
        </div>
        
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td class="text-right">${{ number_format($item['price'], 2) }}</td>
                        <td class="text-right">${{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
                
                <tr>
                    <td colspan="3" class="text-right">Subtotal:</td>
                    <td class="text-right">${{ number_format($purchase->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">GST ({{ $purchase->tax_rate ?? 10 }}%):</td>
                    <td class="text-right">${{ number_format($purchase->tax, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">${{ number_format($purchase->amount, 2) }} {{ $purchase->currency }}</td>
                </tr>
            </tbody>
        </table>
        
        <div class="thank-you">
            Thank you for your purchase!
        </div>
        
        <div class="invoice-footer">
            <p>
                All items in this invoice are digital products.<br>
                For any questions regarding this invoice, please contact support@peopleofpeony.com
            </p>
            <p>© {{ date('Y') }} People of Peony PTY Ltd (ABN 35629544921). All rights reserved.</p>
        </div>
    </div>
</body>
</html>