<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // H3: phone must be nullable so account deletion can anonymise it
            $table->string('phone')->nullable()->change();

            // H3: generated_document_id must be nullable so user can delete their
            //     documents while preserving payment records for accounting.
            //     Drop existing FK, re-add as nullable with nullOnDelete.
            $table->dropForeign(['generated_document_id']);
            $table->unsignedBigInteger('generated_document_id')->nullable()->change();
            $table->foreign('generated_document_id')
                  ->references('id')
                  ->on('generated_documents')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['generated_document_id']);
            $table->unsignedBigInteger('generated_document_id')->nullable(false)->change();
            $table->foreign('generated_document_id')->references('id')->on('generated_documents');
            $table->string('phone')->nullable(false)->change();
        });
    }
};
