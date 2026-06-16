<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTerm;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

class TfIdfService
{
    public function __construct(
        private PreprocessingService $preprocessingService
    ) {}

    /**
     * =========================
     * BUILD INDEX TF-IDF
     * =========================
     */
    public function buildIndex(): array
    {
        $startTime = microtime(true);

        $documents = Document::all();
        $N = $documents->count();

        if ($N === 0) {
            return ['success' => false, 'message' => 'Tidak ada dokumen'];
        }

        DB::table('document_terms')->delete();
        DB::table('terms')->delete();

        $termFrequencies = [];
        $documentFrequency = [];
        $documentTokenCounts = [];

        foreach ($documents as $doc) {

            $text = $doc->title . ' ' . $doc->content;
            $result = $this->preprocessingService->preprocess($text);

            $tokens = $result['final_tokens'] ?? [];

            if (empty($tokens)) continue;

            $documentTokenCounts[$doc->id] = count($tokens);

            $tf = array_count_values($tokens);
            $termFrequencies[$doc->id] = $tf;

            foreach (array_unique($tokens) as $term) {
                $documentFrequency[$term] = ($documentFrequency[$term] ?? 0) + 1;
            }

            $doc->update([
                'preprocessed_tokens' => $tokens
            ]);
        }

        if (empty($termFrequencies)) {
            return ['success' => false, 'message' => 'Token kosong'];
        }

        $termIds = [];

        foreach ($documentFrequency as $term => $df) {

            $idf = log($N / $df) + 1;

            $termRecord = Term::create([
                'term' => $term,
                'document_frequency' => $df,
                'idf' => $idf
            ]);

            $termIds[$term] = $termRecord->id;
        }

        $insertData = [];
        $now = now();

        foreach ($termFrequencies as $docId => $tf) {

            $totalTokens = $documentTokenCounts[$docId];

            foreach ($tf as $term => $count) {

                if (!isset($termIds[$term])) continue;

                $idf = log($N / $documentFrequency[$term]) + 1;
                $tfNorm = $count / $totalTokens;
                $tfidf = $tfNorm * $idf;

                $insertData[] = [
                    'document_id' => $docId,
                    'term_id' => $termIds[$term],
                    'tf_raw' => $count,
                    'tf' => $tfNorm,
                    'idf' => $idf,
                    'tfidf' => $tfidf,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($insertData, 500) as $chunk) {
            DB::table('document_terms')->insert($chunk);
        }

        Document::query()->update(['is_indexed' => true]);

        return [
            'success' => true,
            'documents' => $N,
            'unique_terms' => count($termIds),
            'execution_time' => round((microtime(true) - $startTime) * 1000, 2),
        ];
    }

    /**
     * =========================
     * QUERY VECTOR (FIXED)
     * =========================
     */
    public function computeQueryVector(array $queryTokens): array
    {
        if (empty($queryTokens)) return [];

        $tf = array_count_values($queryTokens);
        $total = count($queryTokens);

        $vector = [];

        foreach ($tf as $term => $count) {

            /**
             * 🔥 FIX UTAMA:
             * pakai exact match dulu (lebih stabil)
             */
            $termRecord = Term::where('term', $term)->first();

            /**
             * fallback ringan kalau tidak ketemu
             */
            if (!$termRecord) {
                $termRecord = Term::where('term', 'like', $term . '%')->first();
            }

            if (!$termRecord) continue;

            $tfNorm = $count / $total;
            $idf = $termRecord->idf;

            $vector[$term] = $tfNorm * $idf;
        }

        return $vector;
    }

    /**
     * =========================
     * DOCUMENT VECTOR
     * =========================
     */
    public function getDocumentVector(int $documentId): array
    {
        $entries = DocumentTerm::where('document_id', $documentId)
            ->with('term')
            ->get();

        $vector = [];

        foreach ($entries as $e) {
            $vector[$e->term->term] = $e->tfidf;
        }

        return $vector;
    }

    /**
     * =========================
     * ALL DOCUMENT VECTORS
     * =========================
     */
    public function getAllDocumentVectors(): array
    {
        $rows = DB::table('document_terms')
            ->join('terms', 'document_terms.term_id', '=', 'terms.id')
            ->select('document_terms.document_id', 'terms.term', 'document_terms.tfidf')
            ->get();

        $vectors = [];

        foreach ($rows as $r) {
            $vectors[$r->document_id][$r->term] = $r->tfidf;
        }

        return $vectors;
    }
}