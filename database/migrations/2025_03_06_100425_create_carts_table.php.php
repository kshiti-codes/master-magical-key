<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });

        // Update purchases table to include invoice data
        Schema::table('purchases', function (Blueprint $table) {
            $table->after('status', function (Blueprint $table) {
                $table->string('invoice_number')->nullable();
                $table->decimal('subtotal', 10, 2)->nullable();
                $table->decimal('tax', 10, 2)->nullable();
                $table->decimal('tax_rate', 5, 2)->default(10.00); // 10% GST
                $table->text('invoice_data')->nullable(); // JSON data
                $table->timestamp('emailed_at')->nullable();
            });
        });

        // Create purchase_items table for multiple chapters
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('purchase_items');
        
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number',
                'subtotal',
                'tax',
                'tax_rate',
                'invoice_data',
                'emailed_at'
            ]);
        });
    }
};