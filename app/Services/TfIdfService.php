<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTerm;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

/**
 * TfIdfService
 *
 * Layanan untuk membangun inverted index dan menghitung bobot TF-IDF.
 *
 * Rumus yang digunakan:
 *   TF(t,d)  = count(t,d) / total_terms(d)   [normalized TF]
 *   IDF(t)   = log(N / df(t)) + 1             [smooth IDF]
 *   TF-IDF   = TF(t,d) × IDF(t)
 */
class TfIdfService
{
    public function __construct(
        private PreprocessingService $preprocessingService
    ) {}

    /**
     * Bangun index untuk SEMUA dokumen dalam database.
     * Proses: preprocessing → hitung TF → hitung IDF → simpan ke DB.
     *
     * @return array Statistik proses indexing
     */
    public function buildIndex(): array
    {
        $startTime = microtime(true);

        // Ambil semua dokumen
        $documents = Document::all();
        $N = $documents->count(); // Total dokumen (untuk IDF)

        if ($N === 0) {
            return ['success' => false, 'message' => 'Tidak ada dokumen untuk diindeks.'];
        }

        // Bersihkan index lama
        DB::table('document_terms')->delete();
        DB::table('terms')->delete();

        // ================================================
        // TAHAP 1: Hitung Term Frequency per dokumen
        // ================================================
        $termFrequencies = [];    // [doc_id => [term => count]]
        $documentFrequency = []; // [term => df_count]
        $documentTokenCounts = []; // [doc_id => total_tokens]

        foreach ($documents as $doc) {
            // Preprocess dokumen
            $text = $doc->title . ' ' . $doc->content;
            $tokens = $this->preprocessingService->getTokens($text);

            if (empty($tokens)) continue;

            $totalTokens = count($tokens);
            $documentTokenCounts[$doc->id] = $totalTokens;

            // Hitung frekuensi setiap term dalam dokumen ini
            $tf = array_count_values($tokens);
            $termFrequencies[$doc->id] = $tf;

            // Hitung document frequency (DF)
            foreach (array_unique($tokens) as $term) {
                if (!empty($term)) {
                    $documentFrequency[$term] = ($documentFrequency[$term] ?? 0) + 1;
                }
            }

            // Simpan token hasil preprocessing
            $doc->update(['preprocessed_tokens' => $tokens]);
        }

        // ================================================
        // TAHAP 2: Simpan semua term unik ke tabel terms
        // ================================================
        $termIds = [];
        foreach ($documentFrequency as $term => $df) {
            if (empty($term) || strlen($term) < 2) continue;

            // Hitung IDF: log(N/df) + 1
            $idf = log($N / $df) + 1;

            $termRecord = Term::updateOrCreate(
                ['term' => $term],
                ['document_frequency' => $df, 'idf' => $idf]
            );
            $termIds[$term] = $termRecord->id;
        }

        // ================================================
        // TAHAP 3: Hitung dan simpan TF-IDF per dokumen
        // ================================================
        $insertData = [];
        $now = now();

        foreach ($termFrequencies as $docId => $tf) {
            $totalTokens = $documentTokenCounts[$docId];

            foreach ($tf as $term => $rawCount) {
                if (!isset($termIds[$term])) continue;

                $termId = $termIds[$term];
                $df     = $documentFrequency[$term];

                // TF ternormalisasi
                $tfNorm = $rawCount / $totalTokens;

                // IDF: log(N / df) + 1
                $idf = log($N / $df) + 1;

                // Bobot TF-IDF
                $tfidf = $tfNorm * $idf;

                $insertData[] = [
                    'document_id' => $docId,
                    'term_id'     => $termId,
                    'tf_raw'      => $rawCount,
                    'tf'          => round($tfNorm, 6),
                    'idf'         => round($idf, 6),
                    'tfidf'       => round($tfidf, 6),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        }

        // Insert batch untuk performa lebih baik
        $chunks = array_chunk($insertData, 500);
        foreach ($chunks as $chunk) {
            DB::table('document_terms')->insert($chunk);
        }

        // Tandai semua dokumen sebagai sudah diindeks
        Document::query()->update(['is_indexed' => true]);

        $elapsed = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'success'         => true,
            'documents'       => $N,
            'unique_terms'    => count($termIds),
            'total_entries'   => count($insertData),
            'execution_time'  => $elapsed,
        ];
    }

    /**
     * Hitung vektor TF-IDF untuk sebuah query.
     *
     * @param array $queryTokens Token hasil preprocessing query
     * @return array [term => tfidf_weight]
     */
    public function computeQueryVector(array $queryTokens): array
    {
        if (empty($queryTokens)) return [];

        $N = Document::count();
        if ($N === 0) return [];

        $queryVector = [];
        $tf = array_count_values($queryTokens);
        $totalTokens = count($queryTokens);

        foreach ($tf as $term => $count) {
            // Cari term di database
            $termRecord = Term::where('term', $term)->first();

            if (!$termRecord) continue;

            // TF ternormalisasi untuk query
            $tfNorm = $count / $totalTokens;

            // Gunakan IDF yang sudah dihitung dari korpus
            $idf = $termRecord->idf;

            $queryVector[$term] = $tfNorm * $idf;
        }

        return $queryVector;
    }

    /**
     * Dapatkan vektor TF-IDF sebuah dokumen dari database.
     *
     * @param int $documentId ID dokumen
     * @return array [term => tfidf_weight]
     */
    public function getDocumentVector(int $documentId): array
    {
        $entries = DocumentTerm::where('document_id', $documentId)
            ->with('term')
            ->get();

        $vector = [];
        foreach ($entries as $entry) {
            $vector[$entry->term->term] = $entry->tfidf;
        }

        return $vector;
    }

    /**
     * Dapatkan semua vektor dokumen sekaligus (lebih efisien).
     *
     * @return array [doc_id => [term => tfidf]]
     */
    public function getAllDocumentVectors(): array
    {
        $entries = DB::table('document_terms')
            ->join('terms', 'document_terms.term_id', '=', 'terms.id')
            ->select('document_terms.document_id', 'terms.term', 'document_terms.tfidf')
            ->get();

        $vectors = [];
        foreach ($entries as $entry) {
            $vectors[$entry->document_id][$entry->term] = $entry->tfidf;
        }

        return $vectors;
    }
}