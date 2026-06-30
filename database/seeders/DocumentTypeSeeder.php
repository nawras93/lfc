<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Passport copy', 'required' => true],
            ['name' => 'QID copy', 'required' => true],
            ['name' => 'Birth certificate', 'required' => true],
            ['name' => 'Player photo', 'required' => true],
            ['name' => 'Previous club release letter', 'required' => false],
            ['name' => 'Medical document', 'required' => false],
            ['name' => 'QFA document', 'required' => false],
            ['name' => 'FIFA document', 'required' => false],
        ] as $documentType) {
            DocumentType::query()->updateOrCreate(
                ['name' => $documentType['name']],
                ['required' => $documentType['required']],
            );
        }
    }
}
