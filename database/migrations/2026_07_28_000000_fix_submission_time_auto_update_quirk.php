<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * MySQL silently attached "ON UPDATE CURRENT_TIMESTAMP" to submission_time
 * even though the original migration never requested it — a well-known
 * legacy MySQL behavior for TIMESTAMP columns (implicit default/auto-update
 * applies whenever explicit_defaults_for_timestamp is off on the server).
 * Confirmed via `SHOW CREATE TABLE form_submissions`, and reproduced
 * locally: any update() call (edit, status change) was silently
 * overwriting the lead's original submission time.
 *
 * A bare `MODIFY submission_time TIMESTAMP NOT NULL` does NOT clear this —
 * tested directly and MySQL just reapplies the same implicit default/
 * on-update behavior to any TIMESTAMP column with no explicit default.
 * The only reliable fix is dropping TIMESTAMP entirely in favor of
 * DATETIME, which has no such legacy auto-update behavior on any MySQL
 * version/config. SQLite has no such quirk either way — this migration
 * only touches MySQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE form_submissions MODIFY submission_time DATETIME NOT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE form_submissions MODIFY submission_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        }
    }
};
