<?php

namespace App\Filament\Resources\Seasons;

use App\Filament\Resources\Seasons\Pages\CreateSeason;
use App\Filament\Resources\Seasons\Pages\EditSeason;
use App\Filament\Resources\Seasons\Pages\ListSeasons;
use App\Filament\Resources\Seasons\Pages\ViewSeason;
use App\Filament\Resources\Seasons\Schemas\SeasonForm;
use App\Filament\Resources\Seasons\Schemas\SeasonInfolist;
use App\Filament\Resources\Seasons\Tables\SeasonsTable;
use App\Models\Season;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeasonResource extends Resource
{
    protected static ?string $model = Season::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;

    public static function form(Schema $schema): Schema
    {
        return SeasonForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SeasonInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeasonsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeasons::route('/'),
            'create' => CreateSeason::route('/create'),
            'view' => ViewSeason::route('/{record}'),
            'edit' => EditSeason::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.seasons.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.seasons.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.seasons.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.recruitment');
    }
}
