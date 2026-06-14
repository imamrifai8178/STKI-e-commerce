<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Evaluation: menyimpan hasil evaluasi sistem IR.
 * Menggunakan ground truth untuk menghitung metrik: P, R, F1, MAP, NDCG.
 */
class Evaluation extends Model
{
    protected $fillable = [
        'query',
        'relevant_documents',
        'retrieved_documents',
        'precision_score',
        'recall_score',
        'f1_score',
        'map_score',
        'ndcg_score',
        'precision_at_5',
        'precision_at_10',
    ];

    protected $casts = [
        'relevant_documents'  => 'array',
        'retrieved_documents' => 'array',
        'precision_score'     => 'float',
        'recall_score'        => 'float',
        'f1_score'            => 'float',
        'map_score'           => 'float',
        'ndcg_score'          => 'float',
        'precision_at_5'      => 'float',
        'precision_at_10'     => 'float',
    ];
}