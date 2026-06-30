<?php

namespace App\Filament\Resources\Candidates\RelationManagers;

use App\Enums\CandidateDocumentStatus;
use App\Models\DocumentType;
use App\Support\EnumOptions;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class CandidateDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('document_type_id')
                    ->label('Document type')
                    ->options(fn (): array => DocumentType::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                FileUpload::make('file_path')
                    ->label('File')
                    ->disk('private')
                    ->directory(fn (): string => 'candidate-documents/'.$this->getOwnerRecord()->getKey())
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(10240)
                    ->preventFilePathTampering()
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('status')
                    ->options(EnumOptions::for(CandidateDocumentStatus::class))
                    ->default(CandidateDocumentStatus::Received->value)
                    ->required()
                    ->native(false),
                Textarea::make('note')
                    ->rows(3)
                    ->maxLength(1000),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('documentType.name')
                    ->label('Document type')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('uploadedBy.name')
                    ->label('Uploaded by'),
                TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'uploaded_by' => auth()->id(),
                    ]),
            ])
            ->recordActions([
                Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record): string => URL::temporarySignedRoute(
                        'admin.candidate-documents.download',
                        now()->addMinutes(10),
                        ['candidateDocument' => $record],
                    )),
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'uploaded_by' => auth()->id(),
                    ]),
                DeleteAction::make(),
            ]);
    }
}
