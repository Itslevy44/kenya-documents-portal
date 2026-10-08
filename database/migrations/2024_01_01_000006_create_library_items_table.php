<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_name');
            $table->string('telegram_file_id');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->string('category')->nullable();
            $table->boolean('is_free')->default(false);
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE library_items ADD FULLTEXT ft_library_title_desc (title, description)');
    }

    public function down(): void
    {
        Schema::dropIfExists('library_items');
    }
};
