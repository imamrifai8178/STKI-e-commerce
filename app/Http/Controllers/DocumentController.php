<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PreprocessingService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(private PreprocessingService $preprocessingService) {}

    /**
     * Daftar semua dokumen dengan pagination dan filter.
     */
    public function index(Request $request)
    {
        $query = Document::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->where('title', 'like', "%$q%")
                   ->orWhere('content', 'like', "%$q%")
                   ->orWhere('category', 'like', "%$q%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $documents  = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $categories = Document::distinct()->pluck('category');

        return view('documents.index', compact('documents', 'categories'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:500',
            'content'      => 'required|string',
            'category'     => 'nullable|string|max:100',
            'author'       => 'nullable|string|max:200',
            'source'       => 'nullable|string|max:300',
            'published_at' => 'nullable|date',
        ]);

        Document::create($validated);

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function show(Document $document)
    {
        // Tampilkan token preprocessing jika sudah diproses
        $preprocessingResult = null;
        if ($document->preprocessed_tokens) {
            $preprocessingResult = $this->preprocessingService->preprocess(
                $document->title . ' ' . $document->content
            );
        }

        // Ambil term TF-IDF dokumen ini
        $tfidfData = $document->documentTerms()
            ->with('term')
            ->orderByDesc('tfidf')
            ->limit(20)
            ->get();

        return view('documents.show', compact('document', 'preprocessingResult', 'tfidfData'));
    }

    public function edit(Document $document)
    {
        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:500',
            'content'      => 'required|string',
            'category'     => 'nullable|string|max:100',
            'author'       => 'nullable|string|max:200',
            'source'       => 'nullable|string|max:300',
            'published_at' => 'nullable|date',
        ]);

        // Reset indexed flag karena konten berubah
        $validated['is_indexed'] = false;
        $validated['preprocessed_tokens'] = null;

        $document->update($validated);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Dokumen berhasil diperbarui. Silakan rebuild index.');
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    /**
     * Tampilkan form import CSV.
     */
    public function importForm()
    {
        return view('documents.import');
    }

    /**
     * Proses upload dan import file CSV.
     * Format CSV yang diharapkan: title, content, category, author, source, published_at
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file    = $request->file('csv_file');
        $path    = $file->getRealPath();
        $handle  = fopen($path, 'r');

        if (!$handle) {
            return back()->with('error', 'Gagal membaca file CSV.');
        }

        $imported = 0;
        $errors   = 0;
        $isHeader = true;
        $headerMap = [];

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            // Baris pertama adalah header
            if ($isHeader) {
                $headerMap = array_flip(array_map('strtolower', array_map('trim', $row)));
                $isHeader = false;
                continue;
            }

            try {
                $title   = $row[$headerMap['title'] ?? 0] ?? '';
                $content = $row[$headerMap['content'] ?? 1] ?? '';

                if (empty(trim($title)) || empty(trim($content))) {
                    $errors++;
                    continue;
                }

                Document::create([
                    'title'        => trim($title),
                    'content'      => trim($content),
                    'category'     => trim($row[$headerMap['category'] ?? 2] ?? 'Umum'),
                    'author'       => trim($row[$headerMap['author'] ?? 3] ?? ''),
                    'source'       => trim($row[$headerMap['source'] ?? 4] ?? ''),
                    'published_at' => !empty($row[$headerMap['published_at'] ?? 5])
                        ? date('Y-m-d', strtotime($row[$headerMap['published_at'] ?? 5]))
                        : now()->toDateString(),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        $message = "Berhasil mengimport {$imported} dokumen.";
        if ($errors > 0) $message .= " {$errors} baris gagal diimport.";

        return redirect()->route('documents.index')->with('success', $message);
    }

    /**
     * Download template CSV untuk import dataset.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_dataset.csv"',
        ];

        $columns = ['title', 'content', 'category', 'author', 'source', 'published_at'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, [
                'Contoh Judul Berita',
                'Isi berita lengkap di sini...',
                'Politik',
                'Nama Reporter',
                'Kompas.com',
                '2024-01-15',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}