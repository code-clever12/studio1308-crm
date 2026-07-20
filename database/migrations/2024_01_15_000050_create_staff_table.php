<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(20);
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->date('hire_date')->nullable();
            // Encrypted (App\Models\Staff casts these as 'encrypted'); stored as text for ciphertext length.
            $table->text('bank_account_routing_number')->nullable();
            $table->text('bank_account_number')->nullable();
            $table->string('bank_account_holder_name')->nullable();
            $table->string('stripe_connect_account_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
