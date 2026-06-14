<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel inverted index: menyimpan bobot TF-IDF setiap term pada setiap dokumen.
     * Ini adalah inti dari Vector Space Model.
     */
    public function up(): void
    {
        Schema::create('document_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            // TF mentah: jumlah kemunculan term dalam dokumen
            $table->integer('tf_raw')->default(0);
            // TF ternormalisasi: tf_raw / total_terms
            $table->float('tf')->default(0);
            // IDF: log(N / df) + 1
            $table->float('idf')->default(0);
            // Bobot akhir TF-IDF
            $table->float('tfidf')->default(0);
            // Satu entri per pasangan (dokumen, term)
            $table->unique(['document_id', 'term_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_terms');
    }
};