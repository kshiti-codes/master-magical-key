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
        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('session_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration')->comment('Duration in minutes');
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('AUD');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coach_session_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained()->onDelete('cascade');
            $table->foreignId('session_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Ensure each coach can only have a session type once
            $table->unique(['coach_id', 'session_type_id']);
        });

        Schema::create('coach_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['available', 'booked', 'unavailable'])->default('available');
            $table->timestamps();
            
            // Ensure we don't have duplicate availability entries
            $table->unique(['coach_id', 'date', 'start_time']);
        });

        Schema::create('booked_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('coach_id')->constrained()->onDelete('cascade');
            $table->foreignId('session_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('coach_availability_id')->constrained()->onDelete('cascade');
            $table->dateTime('session_time');
            $table->integer('duration')->comment('Duration in minutes');
            $table->string('meeting_link')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount_paid', 8, 2);
            $table->timestamps();
        });

        Schema::create('booking_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('coach_availability_id')->constrained()->onDelete('cascade');
            $table->timestamp('expires_at');
            $table->timestamps();
            
            // Ensure one availability can only be locked once
            $table->unique('coach_availability_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaches');
        Schema::dropIfExists('session_types');
        Schema::dropIfExists('coach_session_types');
        Schema::dropIfExists('coach_availabilities');
        Schema::dropIfExists('booked_sessions');
        Schema::dropIfExists('booking_locks');
    }
};
