<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ach_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->unique()->constrained('staff')->cascadeOnDelete();
            // Encrypted (App\Models\ACHBankAccount casts these as 'encrypted').
            $table->text('bank_account_routing_number');
            $table->text('bank_account_number');
            $table->string('bank_account_holder_name');
            $table->string('bank_name')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'failed'])->default('pending');
            $table->string('stripe_bank_account_token')->nullable();
            $table->string('last_4_digits', 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ach_bank_accounts');
    }
};
