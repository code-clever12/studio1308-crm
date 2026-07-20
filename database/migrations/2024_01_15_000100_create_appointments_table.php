<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->decimal('service_price', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->decimal('sales_tax_amount', 8, 2)->default(0);
            $table->decimal('deposit_paid', 8, 2)->default(0);
            $table->decimal('deposit_percentage', 5, 2)->default(0);
            $table->decimal('total_amount', 8, 2);
            $table->decimal('remaining_balance', 8, 2)->default(0);
            $table->decimal('cancellation_fee', 8, 2)->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded'])->default('pending');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->decimal('tip_amount', 8, 2)->default(0);
            $table->boolean('no_show_fee_charged')->default(false);
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'appointment_date']);
            $table->index(['appointment_date', 'status']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
