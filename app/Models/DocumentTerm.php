<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model DocumentTerm: pivot table antara Document dan Term.
 * Menyimpan bobot TF-IDF setiap term dalam setiap dokumen.
 * Ini adalah representasi dari inverted index.
 */
class DocumentTerm extends Model
{
    protected $fillable = [
        'document_id',
        'term_id',
        'tf_raw',   // Frekuensi kemunculan mentah
        'tf',       // TF ternormalisasi
        'idf',      // Inverse Document Frequency
        'tfidf',    // Bobot akhir TF-IDF
    ];

    protected $casts = [
        'tf_raw' => 'integer',
        'tf'     => 'float',
        'idf'    => 'float',
        'tfidf'  => 'float',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}