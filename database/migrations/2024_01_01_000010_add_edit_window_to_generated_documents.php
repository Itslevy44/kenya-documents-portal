<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generated_documents', function (Blueprint $table) {
            $table->integer('edit_count')->default(0);
            $table->timestamp('edit_window_expires_at')->nullable();
            $table->timestamp('payment_completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('generated_documents', function (Blueprint $table) {
            $table->dropColumn(['edit_count', 'edit_window_expires_at', 'payment_completed_at']);
        });
    }
};
