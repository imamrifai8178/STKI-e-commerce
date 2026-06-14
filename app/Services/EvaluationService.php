<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Evaluation;

/**
 * EvaluationService
 *
 * Layanan evaluasi performa sistem Information Retrieval.
 *
 * Metrik yang diimplementasikan:
 * - Precision: TP / (TP + FP)
 * - Recall: TP / (TP + FN)
 * - F1-Score: 2 × (P × R) / (P + R)
 * - MAP: Mean Average Precision
 * - NDCG: Normalized Discounted Cumulative Gain
 * - Precision@5 dan Precision@10
 */
class EvaluationService
{
    public function __construct(
        private SearchService $searchService
    ) {}

    /**
     * Jalankan evaluasi untuk satu query.
     *
     * @param string $query Query pengujian
     * @param array $relevantDocIds Array ID dokumen yang relevan (ground truth)
     * @return array Semua metrik evaluasi
     */
    public function evaluate(string $query, array $relevantDocIds): array
    {
        // Jalankan pencarian
        $searchResult = $this->searchService->search($query, 20);
        $retrievedDocIds = array_column(
            array_map(fn($r) => ['id' => $r['document']->id], $searchResult['results']),
            'id'
        );

        // Hitung semua metrik
        $precision    = $this->precision($relevantDocIds, $retrievedDocIds);
        $recall       = $this->recall($relevantDocIds, $retrievedDocIds);
        $f1           = $this->f1Score($precision, $recall);
        $map          = $this->averagePrecision($relevantDocIds, $retrievedDocIds);
        $ndcg         = $this->ndcg($relevantDocIds, $retrievedDocIds);
        $precisionAt5 = $this->precisionAtK($relevantDocIds, $retrievedDocIds, 5);
        $precisionAt10 = $this->precisionAtK($relevantDocIds, $retrievedDocIds, 10);

        // Simpan ke database
        $evaluation = Evaluation::updateOrCreate(
            ['query' => $query],
            [
                'relevant_documents'  => $relevantDocIds,
                'retrieved_documents' => $retrievedDocIds,
                'precision_score'     => $precision,
                'recall_score'        => $recall,
                'f1_score'            => $f1,
                'map_score'           => $map,
                'ndcg_score'          => $ndcg,
                'precision_at_5'      => $precisionAt5,
                'precision_at_10'     => $precisionAt10,
            ]
        );

        return [
            'query'              => $query,
            'relevant_count'     => count($relevantDocIds),
            'retrieved_count'    => count($retrievedDocIds),
            'precision'          => round($precision, 4),
            'recall'             => round($recall, 4),
            'f1_score'           => round($f1, 4),
            'map'                => round($map, 4),
            'ndcg'               => round($ndcg, 4),
            'precision_at_5'     => round($precisionAt5, 4),
            'precision_at_10'    => round($precisionAt10, 4),
            'relevant_doc_ids'   => $relevantDocIds,
            'retrieved_doc_ids'  => $retrievedDocIds,
        ];
    }

    /**
     * Jalankan evaluasi batch untuk semua query pengujian.
     *
     * @param array $testCases Array query dan ground truth-nya
     * @return array Hasil evaluasi semua query + rata-rata sistem
     */
    public function runBatchEvaluation(array $testCases): array
    {
        $results = [];
        $totals  = [
            'precision' => 0, 'recall' => 0, 'f1' => 0,
            'map' => 0, 'ndcg' => 0, 'p5' => 0, 'p10' => 0
        ];

        foreach ($testCases as $case) {
            $result = $this->evaluate($case['query'], $case['relevant_doc_ids']);
            $results[] = $result;

            $totals['precision'] += $result['precision'];
            $totals['recall']    += $result['recall'];
            $totals['f1']        += $result['f1_score'];
            $totals['map']       += $result['map'];
            $totals['ndcg']      += $result['ndcg'];
            $totals['p5']        += $result['precision_at_5'];
            $totals['p10']       += $result['precision_at_10'];
        }

        $n = count($testCases);

        return [
            'results'           => $results,
            'average_precision' => $n > 0 ? round($totals['precision'] / $n, 4) : 0,
            'average_recall'    => $n > 0 ? round($totals['recall'] / $n, 4) : 0,
            'average_f1'        => $n > 0 ? round($totals['f1'] / $n, 4) : 0,
            'map'               => $n > 0 ? round($totals['map'] / $n, 4) : 0,
            'average_ndcg'      => $n > 0 ? round($totals['ndcg'] / $n, 4) : 0,
            'avg_precision_at_5'  => $n > 0 ? round($totals['p5'] / $n, 4) : 0,
            'avg_precision_at_10' => $n > 0 ? round($totals['p10'] / $n, 4) : 0,
            'total_queries'     => $n,
        ];
    }

    // ============================================================
    // METRIK EVALUASI
    // ============================================================

    /**
     * Precision = TP / (TP + FP)
     * = |Relevant ∩ Retrieved| / |Retrieved|
     */
    public function precision(array $relevant, array $retrieved): float
    {
        if (empty($retrieved)) return 0.0;
        $tp = count(array_intersect($relevant, $retrieved));
        return $tp / count($retrieved);
    }

    /**
     * Recall = TP / (TP + FN)
     * = |Relevant ∩ Retrieved| / |Relevant|
     */
    public function recall(array $relevant, array $retrieved): float
    {
        if (empty($relevant)) return 0.0;
        $tp = count(array_intersect($relevant, $retrieved));
        return $tp / count($relevant);
    }

    /**
     * F1-Score = 2 × (Precision × Recall) / (Precision + Recall)
     * Harmonic mean dari Precision dan Recall.
     */
    public function f1Score(float $precision, float $recall): float
    {
        if ($precision + $recall == 0) return 0.0;
        return 2 * ($precision * $recall) / ($precision + $recall);
    }

    /**
     * Precision@K = Precision dihitung hanya pada K dokumen pertama.
     *
     * @param int $k Jumlah dokumen teratas yang dievaluasi
     */
    public function precisionAtK(array $relevant, array $retrieved, int $k): float
    {
        $retrievedAtK = array_slice($retrieved, 0, $k);
        if (empty($retrievedAtK)) return 0.0;
        $tp = count(array_intersect($relevant, $retrievedAtK));
        return $tp / $k;
    }

    /**
     * Average Precision (AP) untuk satu query.
     * Digunakan untuk menghitung MAP.
     *
     * AP = (1/R) Σ P(k) × rel(k)
     * Di mana R = jumlah dokumen relevan, rel(k) = 1 jika dokumen ke-k relevan
     */
    public function averagePrecision(array $relevant, array $retrieved): float
    {
        if (empty($relevant) || empty($retrieved)) return 0.0;

        $hits    = 0;
        $sumPrec = 0.0;

        foreach ($retrieved as $rank => $docId) {
            if (in_array($docId, $relevant)) {
                $hits++;
                // Precision pada rank ini
                $sumPrec += $hits / ($rank + 1);
            }
        }

        return $hits > 0 ? $sumPrec / count($relevant) : 0.0;
    }

    /**
     * NDCG (Normalized Discounted Cumulative Gain).
     *
     * DCG  = Σ rel_i / log2(i + 1)    untuk i = 1..k
     * IDCG = DCG ideal (jika semua dokumen relevan di atas)
     * NDCG = DCG / IDCG
     *
     * Menggunakan relevance binary: 1 jika relevan, 0 jika tidak.
     */
    public function ndcg(array $relevant, array $retrieved, int $k = 10): float
    {
        $retrievedAtK = array_slice($retrieved, 0, $k);

        // Hitung DCG aktual
        $dcg = 0.0;
        foreach ($retrievedAtK as $rank => $docId) {
            $rel = in_array($docId, $relevant) ? 1 : 0;
            // Rank dimulai dari 1, bukan 0
            $dcg += $rel / (log($rank + 2, 2));
        }

        // Hitung IDCG (skenario ideal: semua dokumen relevan di posisi atas)
        $idealRels = array_fill(0, min(count($relevant), $k), 1);
        $idcg = 0.0;
        foreach ($idealRels as $rank => $rel) {
            $idcg += $rel / (log($rank + 2) / log(2));
        }

        if ($idcg == 0) return 0.0;

        return $dcg / $idcg;
    }

    /**
     * Mean Average Precision (MAP) untuk sekumpulan query.
     * MAP = (1/|Q|) Σ AP(q)
     */
    public function map(array $evaluationResults): float
    {
        if (empty($evaluationResults)) return 0.0;
        $sumAP = array_sum(array_column($evaluationResults, 'map'));
        return $sumAP / count($evaluationResults);
    }
}