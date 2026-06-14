<?php

namespace App\Services;

use App\Models\Document;
use App\Models\SearchLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * SearchService
 *
 * Layanan pencarian menggunakan model Vector Space Model (VSM).
 * Menghitung Cosine Similarity antara vektor query dan vektor dokumen.
 *
 * Rumus Cosine Similarity:
 *   sim(q, d) = (q · d) / (||q|| × ||d||)
 *
 * Di mana:
 *   q · d  = dot product vektor query dan dokumen
 *   ||q||  = magnitude (panjang) vektor query
 *   ||d||  = magnitude (panjang) vektor dokumen
 */
class SearchService
{
    public function __construct(
        private PreprocessingService $preprocessingService,
        private TfIdfService $tfIdfService
    ) {}

    /**
     * Lakukan pencarian berdasarkan query string.
     *
     * @param string $query Query pencarian dari pengguna
     * @param int $topN Jumlah hasil teratas yang dikembalikan
     * @return array Hasil pencarian terurut berdasarkan relevansi
     */
    public function search(string $query, int $topN = 20): array
    {
        $startTime = microtime(true);

        // ================================================
        // TAHAP 1: Preprocessing Query
        // ================================================
        $preprocessResult = $this->preprocessingService->preprocess($query);
        $queryTokens = $preprocessResult['stemmed'];

        if (empty($queryTokens)) {
            return [
                'results'        => [],
                'query'          => $query,
                'query_tokens'   => [],
                'result_count'   => 0,
                'execution_time' => 0,
            ];
        }

        // ================================================
        // TAHAP 2: Hitung Vektor TF-IDF Query
        // ================================================
        $queryVector = $this->tfIdfService->computeQueryVector($queryTokens);

        if (empty($queryVector)) {
            return [
                'results'        => [],
                'query'          => $query,
                'query_tokens'   => $queryTokens,
                'result_count'   => 0,
                'execution_time' => 0,
            ];
        }

        // ================================================
        // TAHAP 3: Ambil semua vektor dokumen
        // ================================================
        $documentVectors = $this->tfIdfService->getAllDocumentVectors();

        if (empty($documentVectors)) {
            return [
                'results'        => [],
                'query'          => $query,
                'query_tokens'   => $queryTokens,
                'result_count'   => 0,
                'execution_time' => 0,
            ];
        }

        // ================================================
        // TAHAP 4: Hitung Cosine Similarity untuk setiap dokumen
        // ================================================
        $scores = [];
        foreach ($documentVectors as $docId => $docVector) {
            $similarity = $this->cosineSimilarity($queryVector, $docVector);
            if ($similarity > 0) {
                $scores[$docId] = $similarity;
            }
        }

        // ================================================
        // TAHAP 5: Ranking - urutkan berdasarkan skor tertinggi
        // ================================================
        arsort($scores);
        $topScores = array_slice($scores, 0, $topN, true);

        // ================================================
        // TAHAP 6: Ambil data dokumen dari database
        // ================================================
        $results = [];
        if (!empty($topScores)) {
            $docIds = array_keys($topScores);
            $documents = Document::whereIn('id', $docIds)->get()->keyBy('id');

            foreach ($topScores as $docId => $score) {
                if ($documents->has($docId)) {
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
        }

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        // ================================================
        // TAHAP 7: Log pencarian ke database
        // ================================================
        SearchLog::create([
            'query'          => $query,
            'result_count'   => count($results),
            'execution_time' => $executionTime,
            'user_id'        => Auth::id(),
        ]);

        return [
            'results'           => $results,
            'query'             => $query,
            'query_tokens'      => $queryTokens,
            'query_vector'      => $queryVector,
            'result_count'      => count($results),
            'execution_time'    => $executionTime,
            'preprocess_steps'  => $preprocessResult,
        ];
    }

    /**
     * Hitung Cosine Similarity antara dua vektor.
     *
     * sim(q,d) = (q · d) / (||q|| × ||d||)
     *
     * @param array $vectorA Vektor pertama [term => weight]
     * @param array $vectorB Vektor kedua [term => weight]
     * @return float Nilai similarity antara 0 dan 1
     */
    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (empty($vectorA) || empty($vectorB)) return 0.0;

        // Hitung dot product: Σ(a_i × b_i) untuk term yang ada di keduanya
        $dotProduct = 0.0;
        foreach ($vectorA as $term => $weightA) {
            if (isset($vectorB[$term])) {
                $dotProduct += $weightA * $vectorB[$term];
            }
        }

        if ($dotProduct === 0.0) return 0.0;

        // Hitung magnitude vektor A: √Σ(a_i²)
        $magnitudeA = sqrt(array_sum(array_map(fn($w) => $w * $w, $vectorA)));

        // Hitung magnitude vektor B: √Σ(b_i²)
        $magnitudeB = sqrt(array_sum(array_map(fn($w) => $w * $w, $vectorB)));

        if ($magnitudeA == 0 || $magnitudeB == 0) return 0.0;

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Cari term query yang cocok dalam dokumen.
     *
     * @param array $queryTokens Token query setelah preprocessing
     * @param Document $document Dokumen yang dicari
     * @return array Daftar term yang cocok
     */
    private function getMatchedTerms(array $queryTokens, Document $document): array
    {
        $docTokens = $document->preprocessed_tokens ?? [];
        return array_values(array_intersect($queryTokens, $docTokens));
    }

    /**
     * Tambahkan tag HTML highlight pada kata kunci dalam teks.
     *
     * @param string $text Teks yang akan di-highlight
     * @param array $keywords Daftar kata kunci
     * @return string Teks dengan highlight
     */
    public function highlightKeywords(string $text, array $keywords): string
    {
        if (empty($keywords)) return $text;

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