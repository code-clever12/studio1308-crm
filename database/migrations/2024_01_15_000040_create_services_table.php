<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('consent_form_id')->nullable()->constrained('consent_forms')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('buffer_time_minutes')->default(15);
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->boolean('requires_consent_form')->default(false);
            $table->boolean('is_taxable')->default(true);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
