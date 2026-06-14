<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SearchLog;
use App\Models\Term;
use App\Models\Evaluation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik utama
        $stats = [
            'total_documents' => Document::count(),
            'total_queries'   => SearchLog::count(),
            'indexed_docs'    => Document::where('is_indexed', true)->count(),
            'total_terms'     => Term::count(),
            'avg_precision'   => round(Evaluation::avg('precision_score') ?? 0, 3),
            'avg_recall'      => round(Evaluation::avg('recall_score') ?? 0, 3),
        ];

        // Data grafik aktivitas pencarian (7 hari terakhir)
        $searchActivity = SearchLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Lengkapi 7 hari meskipun tidak ada pencarian
        $activityLabels = [];
        $activityData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $activityLabels[] = now()->subDays($i)->format('d M');
            $activityData[]   = $searchActivity[$date]->count ?? 0;
        }

        // 10 query paling sering dicari
        $topQueries = SearchLog::select('query', DB::raw('COUNT(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Pencarian terbaru
        $recentSearches = SearchLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Distribusi kategori dokumen
        $categories = Document::select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        return view('dashboard.index', compact(
            'stats', 'activityLabels', 'activityData',
            'topQueries', 'recentSearches', 'categories'
        ));
    }
}