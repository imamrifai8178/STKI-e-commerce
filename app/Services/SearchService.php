<?php

namespace App\Services;

use App\Models\Document;
use App\Models\SearchLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SearchService
{
    public function __construct(
        private PreprocessingService $preprocessingService,
        private TfIdfService $tfIdfService
    ) {}

    public function search(string $query, int $topN = 20): array
    {
        $startTime = microtime(true);

        // =====================================
        // 1. PREPROCESS QUERY
        // =====================================
        $preprocessResult = $this->preprocessingService->preprocess($query);
        $queryTokens = $preprocessResult['stemmed'];

        if (empty($queryTokens)) {
            return $this->emptyResult($query, $preprocessResult);
        }

        // =====================================
        // 2. QUERY VECTOR
        // =====================================
        $queryVector = $this->tfIdfService->computeQueryVector($queryTokens);

        // DEBUG (aktifkan kalau perlu)
        //dd('QUERY VECTOR', $queryVector);

        if (empty($queryVector)) {
            return $this->emptyResult($query, $preprocessResult, $queryTokens);
        }

        // =====================================
        // 3. DOCUMENT VECTOR
        // =====================================
        $documentVectors = $this->tfIdfService->getAllDocumentVectors();

        // DEBUG (aktifkan kalau perlu)
        //dd('DOCUMENT VECTORS', $documentVectors);

        if (empty($documentVectors)) {
            return $this->emptyResult($query, $preprocessResult, $queryTokens, $queryVector);
        }

        // =====================================
        // 4. COSINE SIMILARITY
        // =====================================
        $scores = [];

        foreach ($documentVectors as $docId => $docVector) {

            $similarity = $this->cosineSimilarity($queryVector, $docVector);

            // 🔥 DEBUG per dokumen (kalau mau lihat detail)
            // dump([
            //     'doc_id' => $docId,
            //     'similarity' => $similarity,
            // ]);

            if ($similarity > 0) {
                $scores[$docId] = $similarity;
            }
        }

        // =====================================
        // 5. RANKING
        // =====================================
        arsort($scores);
        $topScores = array_slice($scores, 0, $topN, true);

        // =====================================
        // 6. GET DOCUMENTS
        // =====================================
        $results = [];

        if (!empty($topScores)) {
            $documents = Document::whereIn('id', array_keys($topScores))
                ->get()
                ->keyBy('id');

            foreach ($topScores as $docId => $score) {

                if (!isset($documents[$docId])) continue;

                $doc = $documents[$docId];

                $results[] = [
                    'document'         => $doc,
                    'score'            => round($score, 4),
                    'score_percent'    => round($score * 100, 2),
                    'matched_terms'    => $this->getMatchedTerms($queryTokens, $doc),
                    'highlighted_text' => $this->highlightKeywords(
                        Str::limit($doc->content, 300),
                        $queryTokens
                    ),
                ];
            }
        }

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        // =====================================
        // 7. LOG SEARCH
        // =====================================
        SearchLog::create([
            'query'          => $query,
            'result_count'   => count($results),
            'execution_time' => $executionTime,
            'user_id'        => Auth::id(),
        ]);

        return [
            'results'          => $results,
            'query'            => $query,
            'query_tokens'     => $queryTokens,
            'query_vector'     => $queryVector,
            'result_count'     => count($results),
            'execution_time'   => $executionTime,
            'preprocess_steps' => $preprocessResult,
        ];
    }

    // =====================================
    // EMPTY RESULT HELPER
    // =====================================
    private function emptyResult($query, $preprocess = [], $tokens = [], $vector = [])
    {
        return [
            'results'          => [],
            'query'            => $query,
            'query_tokens'     => $tokens,
            'query_vector'     => $vector,
            'result_count'     => 0,
            'execution_time'   => 0,
            'preprocess_steps' => $preprocess,
        ];
    }

    // =====================================
    // COSINE SIMILARITY
    // =====================================
    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (empty($vectorA) || empty($vectorB)) return 0.0;

        $dotProduct = 0.0;

        foreach ($vectorA as $term => $weightA) {
            if (isset($vectorB[$term])) {
                $dotProduct += $weightA * $vectorB[$term];
            }
        }

        if ($dotProduct == 0) return 0.0;

        $magnitudeA = sqrt(array_sum(array_map(fn($w) => $w * $w, $vectorA)));
        $magnitudeB = sqrt(array_sum(array_map(fn($w) => $w * $w, $vectorB)));

        if ($magnitudeA == 0 || $magnitudeB == 0) return 0.0;

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    // =====================================
    // MATCHED TERMS
    // =====================================
    private function getMatchedTerms(array $queryTokens, Document $document): array
    {
        $docTokens = $document->preprocessed_tokens ?? [];
        return array_values(array_intersect($queryTokens, $docTokens));
    }

    // =====================================
    // HIGHLIGHT
    // =====================================
    public function highlightKeywords(string $text, array $keywords): string
    {
        foreach ($keywords as $keyword) {
            if (strlen($keyword) < 2) continue;

            $text = preg_replace(
                '/\b(' . preg_quote($keyword, '/') . ')\b/iu',
                '<mark class="bg-warning fw-bold">$1</mark>',
                $text
            );
        }

        return $text;
    }
}