<?php

namespace App\Services;

use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Generate a PDF invoice for a purchase
     *
     * @param Purchase $purchase
     * @return string Binary PDF content
     */
    public function generateInvoice(Purchase $purchase)
    {
        // Make sure all relations are loaded
        if (!$purchase->relationLoaded('user')) {
            $purchase->load('user');
        }
        
        // Prepare items for the invoice
        $items = $this->prepareInvoiceItems($purchase);
        
        // Generate the PDF
        $pdf = PDF::loadView('invoices.template', [
            'purchase' => $purchase,
            'user' => $purchase->user,
            'items' => $items
        ]);
        
        // Return the PDF as binary content
        return $pdf->output();
    }
    
    /**
     * Prepare invoice items based on the purchase
     *
     * @param Purchase $purchase
     * @return array
     */
    private function prepareInvoiceItems(Purchase $purchase)
    {
        $items = [];
    
        // Case 1: Purchase has related items (cart purchase)
        if (method_exists($purchase, 'items') && $purchase->items()->exists()) {
            // Load items with their chapters if not already loaded
            if (!$purchase->relationLoaded('items')) {
                $purchase->load('items.chapter');
            }
            
            foreach ($purchase->items as $item) {
                if ($item->chapter) {
                    $items[] = [
                        'title' => "Chapter {$item->chapter->id}: {$item->chapter->title}",
                        'quantity' => $item->quantity,
                        'price' => $item->price
                    ];
                }
            }
        }
        return $items;
    }
}