<?php

namespace App\Services;

use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

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
        try {
            // Make sure all relations are loaded
            if (!$purchase->relationLoaded('user')) {
                $purchase->load('user');
            }
            
            if (!$purchase->relationLoaded('items')) {
                $purchase->load(['items']);
            }
            
            // Prepare items for the invoice
            $items = $this->prepareInvoiceItems($purchase);
            
            // Generate the PDF
            $pdf = PDF::loadView('invoices.template', [
                'purchase' => $purchase,
                'user' => $purchase->user,
                'items' => $items
            ]);
            
            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');
            
            // Return the PDF as binary content
            $pdfContent = $pdf->output();
            
            Log::info('Invoice generated successfully', [
                'invoice_number' => $purchase->invoice_number,
                'user_id' => $purchase->user_id,
                'pdf_size' => strlen($pdfContent)
            ]);
            
            return $pdfContent;
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice', [
                'invoice_number' => $purchase->invoice_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
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
    
        // Process purchased items
        if ($purchase->items->isNotEmpty()) {
            foreach ($purchase->items as $item) {
                $itemData = [
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity
                ];
                
                if ($item->item_type === 'chapter' && $item->chapter) {
                    $itemData['title'] = "Chapter {$item->chapter->id}: {$item->chapter->title}";
                    $itemData['type'] = 'chapter';
                } elseif ($item->item_type === 'spell' && $item->spell) {
                    $itemData['title'] = "Spell: {$item->spell->title}";
                    $itemData['type'] = 'spell';
                } elseif ($item->item_type === 'subscription') {
                    $itemData['title'] = $item->description ?? 'Subscription Plan';
                } elseif ($item->item_type === 'video' && $item->video) {
                    $itemData['title'] = "Training Video: {$item->video->title}";
                } else {
                    $itemData['title'] = $item->description ?? 'Unknown Item';
                }
                
                $items[] = $itemData;
            }
        }
        
        return $items;
    }
}