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

    protected static ?string $title = null;

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('admin.resources.candidates.relations.documents');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('document_type_id')
                    ->label(__('admin.resources.candidates.fields.document_type'))
                    ->options(fn (): array => DocumentType::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                FileUpload::make('file_path')
                    ->label(__('admin.resources.candidates.fields.file'))
                    ->disk('private')
                    ->directory(fn (): string => 'candidate-documents/'.$this->getOwnerRecord()->getKey())
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(10240)
                    ->preventFilePathTampering()
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('status')
                    ->label(__('admin.resources.candidates.fields.status'))
                    ->options(EnumOptions::for(CandidateDocumentStatus::class))
                    ->default(CandidateDocumentStatus::Received->value)
                    ->required()
                    ->native(false),
                Textarea::make('note')
                    ->label(__('admin.resources.candidates.fields.note'))
                    ->rows(3)
                    ->maxLength(1000),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('documentType.name')
                    ->label(__('admin.resources.candidates.fields.document_type'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin.resources.candidates.fields.status'))
                    ->badge(),
                TextColumn::make('uploadedBy.name')
                    ->label(__('admin.resources.candidates.fields.uploaded_by')),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
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
                    ->label(__('admin.resources.candidates.actions.download'))
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
