<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateDocument;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateDocumentDownloadController extends Controller
{
    public function __invoke(CandidateDocument $candidateDocument): StreamedResponse
    {
        return response()->streamDownload(
            fn () => print ($candidateDocument->privateDiskContents()),
            basename($candidateDocument->file_path),
        );
    }
}
