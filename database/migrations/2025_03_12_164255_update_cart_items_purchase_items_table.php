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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('item_type')->default('chapter'); // 'chapter' or 'spell'
            $table->foreignId('spell_id')->nullable(); // Will be NULL for chapter items
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('item_type')->default('chapter'); // 'chapter' or 'spell'
            $table->foreignId('spell_id')->nullable(); // Will be NULL for chapter items
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('item_type');
            $table->dropColumn('spell_id');
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('item_type');
            $table->dropColumn('spell_id');
        });
    }
};