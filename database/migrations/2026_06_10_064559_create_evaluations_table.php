<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel evaluasi sistem: menyimpan hasil pengujian dengan ground truth.
     */
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            // Array ID dokumen yang dianggap relevan (ground truth)
            $table->json('relevant_documents');
            // Array ID dokumen yang dikembalikan sistem
            $table->json('retrieved_documents');
            // Metrik evaluasi IR
            $table->float('precision_score')->default(0);
            $table->float('recall_score')->default(0);
            $table->float('f1_score')->default(0);
            $table->float('map_score')->default(0);
            $table->float('ndcg_score')->default(0);
            $table->float('precision_at_5')->default(0);
            $table->float('precision_at_10')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};