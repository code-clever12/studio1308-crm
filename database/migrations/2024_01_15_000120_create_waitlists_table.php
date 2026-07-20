<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->date('requested_date');
            $table->time('time_preference')->nullable();
            $table->enum('status', ['waiting', 'notified', 'booked', 'expired', 'cancelled'])->default('waiting');
            $table->timestamp('notification_sent_at')->nullable();
            $table->foreignId('converted_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_id', 'requested_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};
