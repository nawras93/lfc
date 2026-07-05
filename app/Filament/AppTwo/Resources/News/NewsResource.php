<?php

namespace App\Filament\AppTwo\Resources\News;

use App\Filament\AppTwo\Resources\News\Pages\CreateNews;
use App\Filament\AppTwo\Resources\News\Pages\EditNews;
use App\Filament\AppTwo\Resources\News\Pages\ListNews;
use App\Models\NewsPost;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = NewsPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.news.sections.content'))
                    ->description(__('admin.resources.news.descriptions.content'))
                    ->icon(Heroicon::OutlinedNewspaper)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('admin.resources.news.fields.title'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('title_ar')
                            ->label(__('admin.resources.news.fields.title_ar'))
                            ->maxLength(255)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl']),
                        Textarea::make('excerpt')
                            ->label(__('admin.resources.news.fields.excerpt'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('excerpt_ar')
                            ->label(__('admin.resources.news.fields.excerpt_ar'))
                            ->rows(3)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl'])
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->label(__('admin.resources.news.fields.body'))
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('body_ar')
                            ->label(__('admin.resources.news.fields.body_ar'))
                            ->rows(6)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl'])
                            ->columnSpanFull(),
                    ]),
                Section::make(__('admin.resources.news.sections.publication'))
                    ->description(__('admin.resources.news.descriptions.publication'))
                    ->icon(Heroicon::OutlinedPhoto)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_path')
                            ->label(__('admin.resources.news.fields.image'))
                            ->disk('public')
                            ->directory('news')
                            ->image()
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->label(__('admin.resources.news.fields.is_published')),
                        DateTimePicker::make('published_at')
                            ->label(__('admin.resources.news.fields.published_at'))
                            ->seconds(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.resources.news.fields.title'))
                    ->state(fn (NewsPost $record): ?string => $record->localized('title'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label(__('admin.resources.news.fields.is_published'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label(__('admin.resources.news.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'edit' => EditNews::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.news.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.news.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.news.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.content');
    }
}
