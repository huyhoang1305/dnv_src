<?php

namespace App\Filament\Resources\AttributeTypes;

use App\Filament\Resources\AttributeTypes\Pages\CreateAttributeType;
use App\Filament\Resources\AttributeTypes\Pages\EditAttributeType;
use App\Filament\Resources\AttributeTypes\Pages\ListAttributeTypes;
use App\Filament\Resources\AttributeTypes\Schemas\AttributeTypeForm;
use App\Filament\Resources\AttributeTypes\Tables\AttributeTypesTable;
use App\Models\AttributeType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttributeTypeResource extends Resource
{
    protected static ?string $model = AttributeType::class;

    protected static ?string $modelLabel = 'Loại thuộc tính';
    protected static ?string $pluralModelLabel = 'Loại thuộc tính';
    protected static string|\UnitEnum|null $navigationGroup = 'Cửa hàng';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-adjustments-horizontal';
    protected static bool $shouldRegisterNavigation = true;

public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return AttributeTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttributeTypesTable::configure($table);
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
            'index' => ListAttributeTypes::route('/'),
            'create' => CreateAttributeType::route('/create'),
            'edit' => EditAttributeType::route('/{record}/edit'),
        ];
    }
}
