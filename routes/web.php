<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\IndexingController;
use App\Http\Controllers\PreprocessingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ============================================================
// AUTHENTICATION ROUTES
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/login',          [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',         [AuthController::class, 'login']);
});
// Register
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Redirect root ke search engine (bisa diakses tanpa login)
Route::get('/', fn() => redirect()->route('search.index'));

// ============================================================
// SEARCH ENGINE (PUBLIC - tanpa login)
// ============================================================
Route::get('/search',             [SearchController::class, 'index'])->name('search.index');
Route::get('/search/results',     [SearchController::class, 'search'])->name('search.results');
Route::get('/search/detail/{document}', [SearchController::class, 'detail'])->name('search.detail');

// ============================================================
// PROTECTED ROUTES (perlu login)
// ============================================================
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dataset / Dokumen Management
    Route::resource('documents', DocumentController::class);
    Route::get('/documents/import/form',     [DocumentController::class, 'importForm'])->name('documents.import.form');
    Route::post('/documents/import',         [DocumentController::class, 'import'])->name('documents.import');
    Route::get('/documents/template/download', [DocumentController::class, 'downloadTemplate'])->name('documents.template');

    // Preprocessing
    Route::get('/preprocessing',             [PreprocessingController::class, 'index'])->name('preprocessing.index');
    Route::get('/preprocessing/{document}',  [PreprocessingController::class, 'show'])->name('preprocessing.show');
    Route::post('/preprocessing/{document}/process', [PreprocessingController::class, 'process'])->name('preprocessing.process');

    // Indexing
    Route::get('/indexing',                  [IndexingController::class, 'index'])->name('indexing.index');
    Route::post('/indexing/build',           [IndexingController::class, 'build'])->name('indexing.build');
    Route::get('/indexing/matrix',           [IndexingController::class, 'matrix'])->name('indexing.matrix');
    Route::get('/indexing/document/{document}', [IndexingController::class, 'documentDetail'])->name('indexing.document');

    // Evaluasi
    Route::get('/evaluation',                [EvaluationController::class, 'index'])->name('evaluation.index');
    Route::post('/evaluation/run',           [EvaluationController::class, 'runEvaluation'])->name('evaluation.run');
    Route::get('/evaluation/{evaluation}',   [EvaluationController::class, 'show'])->name('evaluation.show');

    // Riwayat Pencarian
    Route::get('/history',                   [HistoryController::class, 'index'])->name('history.index');
    Route::delete('/history/{searchLog}',    [HistoryController::class, 'destroy'])->name('history.destroy');
    Route::post('/history/clear',            [HistoryController::class, 'clearAll'])->name('history.clear');

    // User Management (admin only)
    Route::middleware('can:admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Profile
    Route::get('/profile',                   [UserController::class, 'profile'])->name('profile');
    Route::put('/profile',                   [UserController::class, 'updateProfile'])->name('profile.update');
});