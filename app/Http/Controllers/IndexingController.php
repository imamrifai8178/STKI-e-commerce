<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentTerm;
use App\Models\Term;
use App\Services\TfIdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * IndexingController
 * Mengelola proses pembangunan index TF-IDF dan menampilkan vocabulary.
 */
class IndexingController extends Controller
{
    public function __construct(private TfIdfService $tfIdfService) {}

    /**
     * Tampilkan halaman indexing dengan vocabulary dan statistik.
     */
    public function index(Request $request)
    {
        // Statistik index
        $stats = [
            'total_documents' => Document::count(),
            'indexed_docs'    => Document::where('is_indexed', true)->count(),
            'total_terms'     => Term::count(),
            'total_entries'   => DocumentTerm::count(),
        ];

        // Tampilkan vocabulary dengan TF-IDF tertinggi
        $terms = Term::query()
            ->when($request->filled('search'), fn($q) =>
                $q->where('term', 'like', '%' . $request->search . '%'))
            ->orderByDesc('document_frequency')
            ->paginate(20)
            ->withQueryString();

        return view('indexing.index', compact('stats', 'terms'));
    }

    /**
     * Bangun/rebuild index TF-IDF untuk semua dokumen.
     */
    public function build()
    {
        if (Document::count() === 0) {
            return redirect()->back()->with('error', 'Belum ada dokumen. Upload dataset terlebih dahulu.');
        }

        $result = $this->tfIdfService->buildIndex();

        if ($result['success']) {
            $msg = "Index berhasil dibangun! {$result['documents']} dokumen, "
                 . "{$result['unique_terms']} term unik, "
                 . "waktu: {$result['execution_time']}ms";
            return redirect()->route('indexing.index')->with('success', $msg);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Tampilkan matrix TF-IDF (sample: top 10 dokumen × top 20 term).
     */
    public function matrix()
    {
        // Ambil 20 term dengan IDF tertinggi
        $topTerms = Term::orderByDesc('idf')->limit(20)->get();

        // Ambil 10 dokumen
        $documents = Document::where('is_indexed', true)->limit(10)->get();

        // Bangun matrix
        $matrix = [];
        foreach ($documents as $doc) {
            $row = ['document' => $doc, 'values' => []];
            foreach ($topTerms as $term) {
                $entry = DocumentTerm::where('document_id', $doc->id)
                    ->where('term_id', $term->id)
                    ->first();
                $row['values'][$term->term] = $entry ? round($entry->tfidf, 4) : 0;
            }
            $matrix[] = $row;
        }

        return view('indexing.matrix', compact('topTerms', 'matrix'));
    }

    /**
     * Detail TF-IDF satu dokumen.
     */
    public function documentDetail(Document $document)
    {
        $entries = DocumentTerm::where('document_id', $document->id)
            ->with('term')
            ->orderByDesc('tfidf')
            ->paginate(20);

        return view('indexing.document_detail', compact('document', 'entries'));
    }
}