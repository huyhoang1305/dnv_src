<?php

namespace App\Filament\Resources\AttributeValues;

use App\Filament\Resources\AttributeValues\Pages\CreateAttributeValue;
use App\Filament\Resources\AttributeValues\Pages\EditAttributeValue;
use App\Filament\Resources\AttributeValues\Pages\ListAttributeValues;
use App\Filament\Resources\AttributeValues\Schemas\AttributeValueForm;
use App\Filament\Resources\AttributeValues\Tables\AttributeValuesTable;
use App\Models\AttributeValue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttributeValueResource extends Resource
{
    protected static ?string $model = AttributeValue::class;

    protected static ?string $modelLabel = 'Giá trị thuộc tính';
    protected static ?string $pluralModelLabel = 'Giá trị thuộc tính';
    protected static string|\UnitEnum|null $navigationGroup = 'Cửa hàng';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-swatch';
    protected static bool $shouldRegisterNavigation = false;

public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return AttributeValueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttributeValuesTable::configure($table);
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
            'index' => ListAttributeValues::route('/'),
            'create' => CreateAttributeValue::route('/create'),
            'edit' => EditAttributeValue::route('/{record}/edit'),
        ];
    }
}
