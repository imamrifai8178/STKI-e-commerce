<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Term: merepresentasikan satu kata/token unik dalam vocabulary.
 */
class Term extends Model
{
    protected $fillable = ['term', 'document_frequency', 'idf'];

    /**
     * Relasi ke dokumen-dokumen yang mengandung term ini.
     */
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'document_terms')
                    ->withPivot(['tf_raw', 'tf', 'idf', 'tfidf'])
                    ->withTimestamps();
    }

    public function documentTerms()
    {
        return $this->hasMany(DocumentTerm::class);
    }
}