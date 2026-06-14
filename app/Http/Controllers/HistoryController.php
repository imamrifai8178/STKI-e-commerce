<?php

namespace App\Http\Controllers;

use App\Models\SearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $query = SearchLog::with('user')
        ->when($request->filled('q'), fn ($q2) =>
            $q2->where('query', 'like', '%' . $request->q . '%'))
        ->when(!$user->isAdmin(), fn ($q2) =>
            $q2->where('user_id', $user->id))
        ->orderByDesc('created_at')
        ->paginate(20)
        ->withQueryString();

    return view('history.index', ['logs' => $query]);
}

    public function destroy(SearchLog $searchLog)
    {
        $searchLog->delete();
        return redirect()->back()->with('success', 'Log dihapus.');
    }

    public function clearAll()
    {
        SearchLog::query()->delete();
        return redirect()->route('history.index')->with('success', 'Semua riwayat pencarian dihapus.');
    }
}