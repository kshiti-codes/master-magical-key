<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * View invoice
     */
    public function view(Purchase $purchase)
    {
        // Check if user owns this purchase
        if (Auth::id() !== $purchase->user_id) {
            abort(403, 'Unauthorized');
        }
        
        // Check if invoice exists
        if (empty($purchase->invoice_data)) {
            return back()->with('error', 'Invoice is not available');
        }
        
        // Return PDF
        return response($purchase->invoice_data)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="Invoice-' . $purchase->invoice_number . '.pdf"');
    }
    
    /**
     * Download invoice
     */
    public function download(Purchase $purchase)
    {
        // Check if user owns this purchase
        if (Auth::id() !== $purchase->user_id) {
            abort(403, 'Unauthorized');
        }
        
        // Check if invoice exists
        if (empty($purchase->invoice_data)) {
            return back()->with('error', 'Invoice is not available');
        }
        
        // Return PDF for download
        return response($purchase->invoice_data)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="Invoice-' . $purchase->invoice_number . '.pdf"');
    }
}