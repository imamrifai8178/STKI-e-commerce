<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel dokumen untuk menyimpan artikel berita Indonesia.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->string('category')->nullable();
            $table->string('author')->nullable();
            $table->string('source')->nullable();
            $table->date('published_at')->nullable();
            // Flag apakah dokumen sudah diproses untuk indexing
            $table->boolean('is_indexed')->default(false);
            // Simpan hasil preprocessing untuk ditampilkan di UI
            $table->json('preprocessed_tokens')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};