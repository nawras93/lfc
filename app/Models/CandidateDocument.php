<?php

namespace App\Models;

use App\Enums\CandidateDocumentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'candidate_id',
    'document_type_id',
    'file_path',
    'status',
    'note',
    'uploaded_by',
])]
class CandidateDocument extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => CandidateDocumentStatus::class,
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function privateDiskContents(): string
    {
        return Storage::disk('private')->get($this->file_path);
    }
}
