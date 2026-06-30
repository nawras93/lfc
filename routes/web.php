<?php

use App\Http\Controllers\Admin\CandidateDocumentDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'signed'])->group(function (): void {
    Route::get('/admin/candidate-documents/{candidateDocument}/download', CandidateDocumentDownloadController::class)
        ->name('admin.candidate-documents.download');
});
