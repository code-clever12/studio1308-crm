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
        Schema::table('users', function (Blueprint $table) {
            // The Stripe Customer object backing this user, created on their
            // first deposit payment. The payment method is saved for future
            // off-session charges (no-show fees) via setup_future_usage.
            $table->string('stripe_customer_id')->nullable()->after('is_active');
            $table->string('stripe_payment_method_id')->nullable()->after('stripe_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_customer_id', 'stripe_payment_method_id']);
        });
    }
};
