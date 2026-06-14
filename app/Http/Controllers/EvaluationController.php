<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Services\EvaluationService;
use Illuminate\Http\Request;

/**
 * EvaluationController
 * Mengelola evaluasi sistem IR dengan metrik Precision, Recall, MAP, NDCG.
 */
class EvaluationController extends Controller
{
    public function __construct(private EvaluationService $evaluationService) {}

    public function index()
    {
        $evaluations = Evaluation::orderByDesc('created_at')->get();

        // Rata-rata semua metrik
        $averages = [
            'precision'    => round($evaluations->avg('precision_score'), 4),
            'recall'       => round($evaluations->avg('recall_score'), 4),
            'f1'           => round($evaluations->avg('f1_score'), 4),
            'map'          => round($evaluations->avg('map_score'), 4),
            'ndcg'         => round($evaluations->avg('ndcg_score'), 4),
            'p_at_5'       => round($evaluations->avg('precision_at_5'), 4),
            'p_at_10'      => round($evaluations->avg('precision_at_10'), 4),
        ];

        return view('evaluation.index', compact('evaluations', 'averages'));
    }

    /**
     * Jalankan evaluasi batch menggunakan 10 query pengujian yang telah ditentukan.
     */
    public function runEvaluation()
    {
        // Ambil ground truth dari seeder
        $testCases = $this->getTestCases();

        if (empty($testCases)) {
            return redirect()->back()->with('error', 'Tidak ada test case. Pastikan database sudah di-seed.');
        }

        $batchResult = $this->evaluationService->runBatchEvaluation($testCases);

        return redirect()->route('evaluation.index')
            ->with('success', "Evaluasi selesai! MAP: {$batchResult['map']}, NDCG: {$batchResult['average_ndcg']}");
    }

    /**
     * Detail hasil evaluasi satu query.
     */
    public function show(Evaluation $evaluation)
    {
        return view('evaluation.show', compact('evaluation'));
    }

    /**
     * 10 Query pengujian dengan ground truth relevance.
     * Ground truth ditetapkan berdasarkan ID dokumen yang relevan di seeder.
     */
    private function getTestCases(): array
    {
        return [
            ['query' => 'pemilu presiden 2024',           'relevant_doc_ids' => [1, 2, 3, 4, 5]],
            ['query' => 'ekonomi inflasi rupiah',          'relevant_doc_ids' => [6, 7, 8, 9, 10]],
            ['query' => 'teknologi kecerdasan buatan AI',  'relevant_doc_ids' => [11, 12, 13, 14, 15]],
            ['query' => 'covid kesehatan pandemi',         'relevant_doc_ids' => [16, 17, 18, 19, 20]],
            ['query' => 'banjir bencana alam cuaca',       'relevant_doc_ids' => [21, 22, 23, 24, 25]],
            ['query' => 'pendidikan sekolah universitas',  'relevant_doc_ids' => [26, 27, 28, 29, 30]],
            ['query' => 'sepak bola timnas indonesia',     'relevant_doc_ids' => [31, 32, 33, 34, 35]],
            ['query' => 'korupsi hukum pengadilan',        'relevant_doc_ids' => [36, 37, 38, 39, 40]],
            ['query' => 'energi listrik bbm bensin',       'relevant_doc_ids' => [41, 42, 43, 44, 45]],
            ['query' => 'lingkungan hidup hutan deforestasi', 'relevant_doc_ids' => [46, 47, 48, 49, 50]],
        ];
    }
}