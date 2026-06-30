<?php

namespace Database\Factories;

use App\Enums\CandidateDocumentStatus;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateDocument>
 */
class CandidateDocumentFactory extends Factory
{
    protected $model = CandidateDocument::class;

    public function definition(): array
    {
        return [
            'candidate_id' => Candidate::factory(),
            'document_type_id' => DocumentType::factory(),
            'file_path' => 'candidate-documents/demo/file.pdf',
            'status' => CandidateDocumentStatus::Received,
            'note' => null,
            'uploaded_by' => User::factory(),
        ];
    }
}
