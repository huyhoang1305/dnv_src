<?php

namespace App\Filament\Resources\ContentCategories;

use App\Filament\Resources\ContentCategories\Pages\CreateContentCategory;
use App\Filament\Resources\ContentCategories\Pages\EditContentCategory;
use App\Filament\Resources\ContentCategories\Pages\ListContentCategories;
use App\Filament\Resources\ContentCategories\Schemas\ContentCategoryForm;
use App\Filament\Resources\ContentCategories\Tables\ContentCategoriesTable;
use App\Models\ContentCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContentCategoryResource extends Resource
{
    protected static ?string $model = ContentCategory::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-folder-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Nội dung';
    protected static ?string $modelLabel = 'Danh mục';
    protected static ?string $pluralModelLabel = 'Danh mục';

    protected static ?int $navigationSort = 2;

public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return ContentCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentCategories::route('/'),
            'create' => CreateContentCategory::route('/create'),
            'edit' => EditContentCategory::route('/{record}/edit'),
        ];
    }
}
