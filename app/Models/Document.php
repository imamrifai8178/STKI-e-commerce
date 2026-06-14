<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'author',
        'source',
        'published_at',
        'is_indexed',
        'preprocessed_tokens',
    ];

    protected $casts = [
        'published_at' => 'date',
        'is_indexed' => 'boolean',
        'preprocessed_tokens' => 'array',
    ];

    /**
     * Relasi ke tabel pivot document_terms (inverted index).
     */
    public function documentTerms()
    {
        return $this->hasMany(DocumentTerm::class);
    }

    /**
     * Relasi many-to-many ke terms melalui document_terms.
     */
    public function terms()
    {
        return $this->belongsToMany(Term::class, 'document_terms')
                    ->withPivot(['tf_raw', 'tf', 'idf', 'tfidf'])
                    ->withTimestamps();
    }

    /**
     * Accessor: ringkasan teks (200 karakter pertama).
     */
    public function getSummaryAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 200);
    }

    /**
     * Scope: hanya dokumen yang sudah diindeks.
     */
    public function scopeIndexed(Builder $query): Builder
    {
        return $query->where('is_indexed', true);
    }
}