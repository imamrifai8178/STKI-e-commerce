<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PreprocessingService;
use Illuminate\Http\Request;

/**
 * PreprocessingController
 * Menampilkan hasil preprocessing setiap dokumen step by step.
 */
class PreprocessingController extends Controller
{
    public function __construct(private PreprocessingService $service) {}

    public function index(Request $request)
    {
        $documents = Document::query()
            ->when($request->filled('search'), fn($q) =>
                $q->where('title', 'like', '%' . $request->search . '%'))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('preprocessing.index', compact('documents'));
    }

    /**
     * Tampilkan detail preprocessing satu dokumen.
     */
    public function show(Document $document)
    {
        $text   = $document->title . ' ' . $document->content;
        $result = $this->service->preprocess($text);

        return view('preprocessing.show', compact('document', 'result'));
    }

    /**
     * Jalankan preprocessing untuk satu dokumen (AJAX).
     */
    public function process(Document $document)
    {
        $text   = $document->title . ' ' . $document->content;
        $result = $this->service->preprocess($text);

        // Simpan token ke dokumen
        $document->update(['preprocessed_tokens' => $result['stemmed']]);

        return response()->json([
            'success' => true,
            'result'  => $result,
        ]);
    }
}