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
        Schema::table('chapters', function (Blueprint $table) {
            $table->string('audio_path')->nullable()->after('is_published');
            $table->string('audio_format')->nullable()->after('audio_path');
            $table->integer('audio_duration')->nullable()->after('audio_format');
            $table->boolean('has_audio')->default(false)->after('audio_duration');
            $table->json('audio_timestamps')->nullable()->after('has_audio');
        });

        // Remove chapter_id from purchases table
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['chapter_id']);
            $table->dropColumn('chapter_id');
        });

        // Make chapter_id nullable in purchase_items table
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable()->change();
        });

        // Make chapter_id nullable in cart_items table
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn([
                'audio_path',
                'audio_format',
                'audio_duration',
                'has_audio',
                'audio_timestamps'
            ]);
        });

        // Add chapter_id back to purchases table
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
        });

        // Make chapter_id required again in purchase_items table
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable(false)->change();
        });

        // Make chapter_id required again in cart_items table
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable(false)->change();
        });
    }
};
