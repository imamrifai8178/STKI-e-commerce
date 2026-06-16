<?php

namespace App\Services;

use App\Models\Evaluation;

class EvaluationService
{
    public function __construct(
        private SearchService $searchService
    ) {}

    // =========================================================
    // EVALUATE SINGLE QUERY
    // =========================================================
    public function evaluate(string $query, array $relevantDocIds): array
    {
        $searchResult = $this->searchService->search($query, 20);

        // SAFE retrieval
        $retrievedDocIds = array_values(array_filter(array_map(
            fn($r) => $r['document']->id ?? null,
            $searchResult['results'] ?? []
        )));

        // DEBUG (WAJIB kalau masih 0)
        // dd($relevantDocIds, $retrievedDocIds, array_intersect($relevantDocIds, $retrievedDocIds));

        $precision = $this->precision($relevantDocIds, $retrievedDocIds);
        $recall    = $this->recall($relevantDocIds, $retrievedDocIds);
        $f1        = $this->f1Score($precision, $recall);
        $map       = $this->averagePrecision($relevantDocIds, $retrievedDocIds);
        $ndcg      = $this->ndcg($relevantDocIds, $retrievedDocIds);

        Evaluation::updateOrCreate(
            ['query' => $query],
            [
                'relevant_documents'  => $relevantDocIds,
                'retrieved_documents' => $retrievedDocIds,
                'precision_score'     => $precision,
                'recall_score'        => $recall,
                'f1_score'            => $f1,
                'map_score'           => $map,
                'ndcg_score'          => $ndcg,
            ]
        );

        return [
            'query' => $query,
            'precision' => round($precision, 4),
            'recall' => round($recall, 4),
            'f1_score' => round($f1, 4),
            'map' => round($map, 4),
            'ndcg' => round($ndcg, 4),
            'relevant_doc_ids' => $relevantDocIds,
            'retrieved_doc_ids' => $retrievedDocIds,
        ];
    }

    // =========================================================
    // BATCH EVALUATION
    // =========================================================
    public function runBatchEvaluation(array $testCases): array
    {
        $results = [];

        $totals = [
            'precision' => 0,
            'recall'    => 0,
            'f1'        => 0,
            'map'       => 0,
            'ndcg'      => 0,
        ];

        foreach ($testCases as $case) {
            $result = $this->evaluate($case['query'], $case['relevant_doc_ids']);
            $results[] = $result;

            $totals['precision'] += $result['precision'];
            $totals['recall']    += $result['recall'];
            $totals['f1']        += $result['f1_score'];
            $totals['map']       += $result['map'];
            $totals['ndcg']      += $result['ndcg'];
        }

        $n = max(count($testCases), 1);

        return [
            'results' => $results,

            'map' => $totals['map'] / $n,
            'average_ndcg' => $totals['ndcg'] / $n,
            'average_precision' => $totals['precision'] / $n,
            'average_recall' => $totals['recall'] / $n,
            'average_f1' => $totals['f1'] / $n,
        ];
    }

    // =========================================================
    // PRECISION
    // =========================================================
    public function precision(array $relevant, array $retrieved): float
    {
        if (empty($retrieved)) return 0.0;
        $tp = count(array_intersect($relevant, $retrieved));
        return $tp / count($retrieved);
    }

    // =========================================================
    // RECALL
    // =========================================================
    public function recall(array $relevant, array $retrieved): float
    {
        if (empty($relevant)) return 0.0;
        $tp = count(array_intersect($relevant, $retrieved));
        return $tp / count($relevant);
    }

    // =========================================================
    // F1 SCORE
    // =========================================================
    public function f1Score(float $p, float $r): float
    {
        if ($p + $r == 0) return 0.0;
        return 2 * ($p * $r) / ($p + $r);
    }

    // =========================================================
    // AVERAGE PRECISION (MAP)
    // =========================================================
    public function averagePrecision(array $relevant, array $retrieved): float
    {
        if (empty($relevant)) return 0.0;

        $hits = 0;
        $sum  = 0.0;

        foreach ($retrieved as $i => $docId) {
            if (in_array($docId, $relevant)) {
                $hits++;
                $sum += $hits / ($i + 1);
            }
        }

        return $hits > 0 ? $sum / count($relevant) : 0.0;
    }

    // =========================================================
    // NDCG
    // =========================================================
    public function ndcg(array $relevant, array $retrieved, int $k = 10): float
    {
        $retrieved = array_slice($retrieved, 0, $k);

        $dcg = 0.0;
        foreach ($retrieved as $i => $docId) {
            $rel = in_array($docId, $relevant) ? 1 : 0;
            $dcg += $rel / log($i + 2, 2);
        }

        $idcg = 0.0;
        $ideal = array_fill(0, min(count($relevant), $k), 1);

        foreach ($ideal as $i => $rel) {
            $idcg += $rel / log($i + 2, 2);
        }

        return $idcg == 0 ? 0.0 : $dcg / $idcg;
    }
}