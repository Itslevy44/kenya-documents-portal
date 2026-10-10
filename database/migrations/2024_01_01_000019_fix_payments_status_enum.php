<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First update any 'completed' values to 'paid' so existing data is preserved
        DB::table('payments')->where('status', 'completed')->update(['status' => 'paid']);

        // Alter the enum to include 'paid' and remove 'completed'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        DB::table('payments')->where('status', 'paid')->update(['status' => 'completed']);
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending'");
        }
    }
};
