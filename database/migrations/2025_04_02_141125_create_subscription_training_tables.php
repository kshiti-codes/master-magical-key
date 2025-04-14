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
        // Create subscription plans table
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('AUD');
            $table->string('billing_interval')->default('month');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_lifetime')->default(false);
            $table->timestamp('available_until')->nullable();
            $table->timestamps();
        });

        // Create user subscriptions table
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans');
            $table->string('status');
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->string('paypal_subscription_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount_paid', 8, 2);
            $table->timestamps();
        });

        // Create training videos table
        Schema::create('training_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('duration')->nullable();
            $table->decimal('price', 8, 2)->default(0.00);
            $table->string('currency', 3)->default('AUD');
            $table->boolean('is_published')->default(false);
            $table->integer('order_sequence')->default(0);
            $table->timestamps();
        });

        // Create pivot table for user-video access
        Schema::create('user_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('training_video_id')->constrained()->onDelete('cascade');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('last_watched_at')->nullable();
            $table->integer('watch_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_videos');
        Schema::dropIfExists('training_videos');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};