<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\SearchService;
use Illuminate\Http\Request;

/**
 * SearchController
 * Mengelola proses pencarian dan menampilkan hasil ranking.
 */
class SearchController extends Controller
{
    public function __construct(private SearchService $searchService) {}

    /**
     * Halaman utama search engine.
     */
    public function index()
    {
        return view('search.index');
    }

    /**
     * Proses query dan tampilkan hasil pencarian.
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:200',
        ]);

        $query  = $request->input('q');
        $result = $this->searchService->search($query, 20);

        return view('search.results', [
            'query'          => $query,
            'results'        => $result['results'],
            'result_count'   => $result['result_count'],
            'execution_time' => $result['execution_time'],
            'query_tokens'   => $result['query_tokens'],
            'query_vector'   => $result['query_vector'] ?? [],
            'preprocess_steps' => $result['preprocess_steps'],
        ]);
    }

    /**
     * Detail dokumen dari hasil pencarian.
     */
    public function detail(Request $request, Document $document)
    {
        $query   = $request->input('q', '');
        $tokens  = [];
        $score   = 0;

        // Jika ada query, hitung ulang skor untuk highlight
        if (!empty($query)) {
            $results = $this->searchService->search($query, 100);
            foreach ($results['results'] as $r) {
                if ($r['document']->id === $document->id) {
                    $score  = $r['score'];
                    $tokens = $r['matched_terms'];
                    break;
                }
            }
        }

        // Highlight konten
        $highlightedContent = $this->searchService->highlightKeywords(
            $document->content, $tokens
        );

        return view('search.detail', compact(
            'document', 'query', 'score', 'tokens', 'highlightedContent'
        ));
    }
}