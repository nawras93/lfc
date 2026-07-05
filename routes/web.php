<?php

use App\Http\Controllers\Admin\CandidateDocumentDownloadController;
use App\Http\Controllers\PublicRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('public.locale')->group(function (): void {
    Route::get('/', [PublicRegistrationController::class, 'landing'])->name('public.home');
    Route::get('/register/{seasonSlug}/{registrationSlug}', [PublicRegistrationController::class, 'create'])->name('public.register.show');
    Route::post('/register/{seasonSlug}/{registrationSlug}', [PublicRegistrationController::class, 'store'])->name('public.register.store');
});

Route::get('/admin/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'ar'], true), 404);

    session(['admin_locale' => $locale]);

    return redirect()->to(url()->previous() ?: url('/admin-app-one'));
})->name('admin.locale.switch');

Route::middleware(['auth', 'signed'])->group(function (): void {
    Route::get('/admin-app-one/candidate-documents/{candidateDocument}/download', CandidateDocumentDownloadController::class)
        ->name('admin.candidate-documents.download');
});

Route::redirect('/admin', '/admin-app-one');
