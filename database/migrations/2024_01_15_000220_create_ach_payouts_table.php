<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ach_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            $table->enum('status', ['pending', 'in_transit', 'completed', 'failed'])->default('pending');
            $table->string('stripe_payout_id')->nullable();
            $table->date('payout_date')->nullable();
            $table->date('expected_arrival_date')->nullable();
            $table->text('failure_reason')->nullable();
            $table->decimal('commission_amount', 8, 2)->default(0);
            $table->decimal('tips_amount', 8, 2)->default(0);
            $table->decimal('adjustments_amount', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ach_payouts');
    }
};
