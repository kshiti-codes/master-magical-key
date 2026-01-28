<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('AUD');
            $table->enum('type', [
                'digital_download', 
                'course', 
                'session', 
                'subscription',
                'video',
                'other'
            ])->default('digital_download');
            $table->string('pdf_file_path')->nullable();
            $table->string('audio_file_path')->nullable();
            $table->longText('popup_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('sku')->nullable()->unique();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->timestamps();
            
            // Add indexes for commonly queried fields
            $table->index('is_active');
            $table->index('type');
            $table->index('slug');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('cart_id')->constrained()->onDelete('cascade');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('purchase_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('cart_id')->constrained()->onDelete('cascade');
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });

    }
};