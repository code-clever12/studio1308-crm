<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('service')->nullable()->after('payload');
        });

        // Backfill from the existing payload JSON so leads submitted before
        // this column existed (e.g. the hero lead form's "Interested In..."
        // field) are still filterable by service, not just new submissions.
        DB::table('form_submissions')->select('id', 'payload')->orderBy('id')->each(function (object $row): void {
            $service = json_decode((string) $row->payload, true)['service'] ?? null;

            if ($service !== null) {
                DB::table('form_submissions')->where('id', $row->id)->update(['service' => $service]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn('service');
        });
    }
};
