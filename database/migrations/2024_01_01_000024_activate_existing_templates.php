<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * BUG-02: Templates were seeded with is_active = false due to the migration
     * default being false. Activate all templates that are currently inactive
     * so they appear on the site immediately.
     */
    public function up(): void
    {
        DB::table('templates')->where('is_active', false)->update(['is_active' => true]);
    }

    public function down(): void
    {
        // Non-reversible data migration — do not deactivate templates on rollback
    }
};
