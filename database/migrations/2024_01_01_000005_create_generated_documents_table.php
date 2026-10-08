<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_token')->unique();
            $table->foreignId('template_id')->constrained();
            $table->json('form_data');
            $table->enum('status', ['draft', 'paid', 'cancelled'])->default('draft');
            $table->string('pdf_path')->nullable();
            $table->string('word_path')->nullable();
            $table->string('preview_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
    }
};
