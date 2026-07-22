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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->json('payload');
            $table->string('url')->nullable();
            $table->json('utm_parameters')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->enum('status', ['new', 'contacted', 'converted', 'archived'])->default('new');
            $table->timestamp('submission_time');
            $table->timestamps();

            $table->index(['form_id', 'status']);
            $table->index('submission_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
