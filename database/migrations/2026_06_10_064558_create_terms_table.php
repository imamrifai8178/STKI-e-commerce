<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel vocabulary/kosakata unik dari seluruh korpus dokumen.
     */
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('term')->unique();
            // Document Frequency: berapa banyak dokumen yang mengandung term ini
            $table->integer('document_frequency')->default(0);
            // IDF = log(N/df) + 1
            $table->float('idf')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};