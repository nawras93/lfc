<?php

use App\Http\Controllers\Admin\CandidateDocumentDownloadController;
use App\Http\Controllers\PublicRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('public.locale')->group(function (): void {
    Route::get('/', [PublicRegistrationController::class, 'landing'])->name('public.home');
    Route::get('/register/{seasonSlug}/{registrationSlug}', [PublicRegistrationController::class, 'create'])->name('public.register.show');
    Route::post('/register/{seasonSlug}/{registrationSlug}', [PublicRegistrationController::class, 'store'])->name('public.register.store');
});

Route::middleware(['auth', 'signed'])->group(function (): void {
    Route::get('/admin/candidate-documents/{candidateDocument}/download', CandidateDocumentDownloadController::class)
        ->name('admin.candidate-documents.download');
});
