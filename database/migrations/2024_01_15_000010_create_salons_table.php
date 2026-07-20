<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state', 2);
            $table->string('zip_code', 10);
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('timezone')->default('America/New_York');
            $table->time('opens_at');
            $table->time('closes_at');
            $table->text('cancellation_policy')->nullable();
            $table->decimal('deposit_percentage', 5, 2)->default(25);
            $table->decimal('no_show_fee', 8, 2)->default(25);
            $table->boolean('enable_tips')->default(true);
            $table->decimal('sales_tax_rate', 5, 2)->nullable();
            $table->string('acct_stripe_connect_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salons');
    }
};
